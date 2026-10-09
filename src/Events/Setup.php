<?php

namespace WebApp\Events;

use Core;
use WebApp\Entity\PushKey;
use Concrete\Core\Page\Page;
use Concrete\Core\View\View;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Config\Repository\Liaison;
use Concrete\Core\Error\UserMessageException;

class Setup
{
    private static string $pkgHandle = 'web_app';
    private static ?Package $pkg = null;
    private static ?Liaison $config = null;
    private static ?Page $page = null;
    private static ?View $view = null;
    private static ?bool $active = null;

    /**
     * Populates the static package/page/view state used by the event handlers below.
     * Safe to call multiple times; only initializes once per request.
     */
    private static function init(): void
    {
        if (self::$active !== null) {
            return;
        }

        self::$pkg = Core::make(PackageService::class)->getClass(self::$pkgHandle);
        if (!self::$pkg) {
            throw new UserMessageException(t('Package %s not found', self::$pkgHandle));
        }

        // Package::getFileConfig() always returns a Liaison, so no null-check is needed here.
        self::$config = self::$pkg->getFileConfig();

        self::$page = Page::getCurrentPage();
        if (!self::$page || self::$page->isError()) {
            throw new UserMessageException(t('Current page not found'));
        }

        self::$view = self::$page->getPageController()->getViewObject();
        if (!self::$view) {
            throw new UserMessageException(t('Page View not found'));
        }

        self::$active = (bool) self::$config->get('web_app.activate');
    }

    public static function setupWebApp(): void
    {
        self::init();

        if (!self::$active) {
            return;
        }

        self::$view->addHeaderItem('<meta name="screen-orientation" content="portrait" />');
        self::$view->addHeaderItem('<link rel="manifest" href="/site.webmanifest" />');
        self::$view->addHeaderItem('<meta name="mobile-web-app-capable" content="yes" />'); // android
        self::$view->addHeaderItem('<meta name="apple-mobile-web-app-capable" content="yes" />'); // ios
        // self::$view->addHeaderItem('<meta name="apple-mobile-web-app-status-bar-style" content="default" />'); //TODO: a bit buggy in modern ios

        foreach (self::$config->get('web_app.launchscreens') as $width => $path) {
            switch ($width) {
                case '640':
                    $mediaString = '(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)';
                    break;
                case '750':
                    $mediaString = '(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2) and (orientation: portrait)';
                    break;
                case '1242':
                    $mediaString = '(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)';
                    break;
                case '1125':
                    $mediaString = '(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3) and (orientation: portrait)';
                    break;
                case '1536':
                    $mediaString = '(min-device-width: 768px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)';
                    break;
                case '1668':
                    $mediaString = '(min-device-width: 834px) and (max-device-width: 834px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)';
                    break;
                case '2048':
                    $mediaString = '(min-device-width: 1024px) and (max-device-width: 1024px) and (-webkit-min-device-pixel-ratio: 2) and (orientation: portrait)';
                    break;
            }

            if (isset($mediaString)) {
                self::$view->addHeaderItem('<link rel="apple-touch-startup-image" href="' . $path . '" media="' . $mediaString . '" />');
            }
        }
    }

    public static function registerPushAssets(): void
    {
        self::init();

        $currentKeys = PushKey::getByID(self::$config->get('push_notifications.key_id') ?? 1);

        if (
            is_object($currentKeys)
            && self::$config->get('push_notifications.activate')
            && !self::$page->isAdminArea()
            && (self::$page->getCollectionHandle() !== 'login' && self::$page->getCollectionHandle() !== 'register')
        ) {
            ob_start();
            self::$view->element('subscribe_notification', [], 'web_app');
            $subscribeNotification = ob_get_contents();
            ob_end_clean();

            $html = Core::make('helper/html');
            $swPath = '/web-app-service-worker.js';
            $controller = self::$page->getPageController();
            $controller->addHeaderItem($html->css('push.css', 'web_app'));
            $controller->addFooterItem('<script type="text/x-template" id="push-subscribe-notification" data-sw="' . $swPath . '" data-key="' . $currentKeys->getPublicKey() . '">' . $subscribeNotification . '</script>');
            $controller->addFooterItem('<script src="' . $swPath . '"></script>');
            $controller->addFooterItem($html->javascript('push.js', 'web_app'));
        }
    }
}
