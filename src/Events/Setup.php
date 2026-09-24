<?php

namespace WebApp\Events;

use Core;
use File;
use Page;
use View;
use WebApp\Entity\PushKey;
use Concrete\Core\Entity\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Config\Repository\Liaison;
use Concrete\Core\Error\UserMessageException;

class Setup
{
    protected string $pkgHandle = 'web_app';
    protected Package $pkg;
    protected Liaison $config;
    protected Page $page;
    protected View $view;
    protected bool $active;

    public function __construct()
    {
        $this->pkg = Core::make(PackageService::class)->getByHandle($this->pkgHandle);
        if (!$this->pkg) {
            throw new UserMessageException(t('Package %s not found', $this->pkgHandle));
        }

        $this->config = $this->pkg->getFileConfig();
        if (!$this->config) {
            throw new UserMessageException(t('Package %s Config not found', $this->pkgHandle));
        }

        $this->page = Page::getCurrentPage();
        if (!$this->page || $this->page->isError()) {
            throw new UserMessageException(t('Current page not found'));
        }

        $this->view = $this->page->getPageController()->getViewObject();
        if (!$this->view) {
            throw new UserMessageException(t('Page View not found'));
        }

        $this->active = $this->config->get('web_app.activate');
    }

    public static function setupWebApp(): void
    {
        if (!self::$active) {
            return;
        }

        self::$view->addHeaderItem('<meta name="screen-orientation" content="portrait" />');
        self::$view->addHeaderItem('<link rel="manifest" href="/site.webmanifest" />');
        self::$view->addHeaderItem('<meta name="mobile-web-app-capable" content="yes" />'); // android
        self::$view->addHeaderItem('<meta name="apple-mobile-web-app-capable" content="yes" />'); // ios
        // self::$view->addHeaderItem('<meta name="apple-mobile-web-app-status-bar-style" content="default" />'); //TODO: a bit buggy in modern ios

        foreach (self::$config->get('web_app.launchscreens') as $size => $fID) {
            if ($fID > 0) {
                $file = File::getByID($fID);
                if ($file) {
                    $sizeArray = explode('x', $size);
                    $width = $sizeArray[0];

                    switch ($width) {
                        case '640':
                            $mediaString = '(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)';
                            // no break
                        case '750':
                            $mediaString = '(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)';
                            // no break
                        case '1242':
                            $mediaString = '(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)';
                            // no break
                        case '1125':
                            $mediaString = '(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)';
                            // no break
                        case '1536':
                            $mediaString = '(min-device-width: 768px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)';
                            // no break
                        case '1668':
                            $mediaString = '(min-device-width: 834px) and (max-device-width: 834px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)';
                            // no break
                        case '2048':
                            $mediaString = '(min-device-width: 1024px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)';
                    }

                    if (isset($mediaString)) {
                        self::$view->addHeaderItem('<link rel="apple-touch-startup-image" href="' . $file->getRelativePath() . '" media="' . $mediaString . '" />');
                    }
                }
            }
        }
    }

    public static function registerPushAssets(): void
    {
        $currentKeys = PushKey::getByID(self::$config->get('push_notifications.key_id'));

        if (
            is_object($currentKeys)
            && !self::$page->isAdminArea()
            && (self::$page->getCollectionHandle() !== 'login' && self::$page->getCollectionHandle() !== 'register')
        ) {
            ob_start();
            self::$view->element('subscribe_notification', [], 'web_app');
            $subscribeNotification = ob_get_contents();
            ob_end_clean();

            $html = Core::make('helper/html');
            $swPath = '/web-app-service-worker.js';
            $controller = self::$page->getController();
            $controller->addHeaderItem($html->css('push.css', 'web_app'));
            $controller->addFooterItem('<script type="text/x-template" id="push-subscribe-notification" data-sw="' . $swPath . '" data-key="' . $currentKeys->getPublicKey() . '">' . $subscribeNotification . '</script>');
            $controller->addFooterItem('<script src="' . Core::make('autocache')->autocache('', 'sw.js') . '"></script>');
            $controller->addFooterItem($html->javascript('push.js', 'web_app'));
        }
    }
}
