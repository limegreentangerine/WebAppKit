<?php

namespace Concrete\Package\WebApp;

use Core;
use File;
use Page;
use View;
use Events;
use WebApp\Package\PageTrait;
use Concrete\Core\Package\Package;
use Concrete\Core\Package\PackageService;

class Controller extends Package
{
    use PageTrait;
    /**
     * The packages handle.
     * Note that this must be unique in the
     * entire concrete5 package ecosystem.
     *
     * @var string
     */
    protected $pkgHandle = 'web_app';

    /**
     * The packages version.
     *
     * @var string
     */
    protected $pkgVersion = '1.0.0';

    /**
     * The minimum Concrete version compatible with the package.
     * Override this value according to the minimum required version for your package.
     *
     * @var string
     */
    protected $appVersionRequired = '9.5.0';

    /**
     * The minimum PHP version compatible with the package.
     * Override this value according to the minimum required version for your package.
     *
     * @var string
     * @var string
     */
    protected $phpVersionRequired = '8.4';

    /**
     * Package service providers to register.
     *
     * eg. 'Concrete\Package\PackageHandle\Src\Providers\PackageServiceProvider'
     *
     * @var array
     */
    protected $providers = [];

    /**
     * An array describing the package dependencies.
     * Keys are package handles.
     * Values may be:
     * - false: this package can't be installed if the other package is already installed.
     * - true: this package can't be installed of the other package is not installed
     * - a string: this package can't be installed of the other package is not installed or it's installed with an older version
     * - an array with two strings, representing the minimum and the maximum version of the other package to be installed.
     *
     * @var array
     *
     * @example [
     *     // This package can't be installed if a package with handle other_package_1 is already installed.
     *     'other_package_1' => false,
     *     // This package can't be installed if a package with handle other_package_2 is not installed.
     *     'other_package_2' => true,
     *     // This package can't be installed if a package with handle other_package_3 is not installed, or it has a version prior to 1.0
     *     'other_package_3' => '1.0',
     *     // This package can't be installed if a package with handle other_package_4 is not installed, or it has a version prior to 2.0, or it has a version after 2.9
     *     'other_package_4' => ['2.0', '2.9'],
     * ]
     */
    protected $packageDependencies = [];

    /**
     * Package class autoloader registrations
     * The package install helper class, included with this boilerplate,
     * is activated by default.
     *
     * @see https://goo.gl/4wyRtH
     * @var array
     */
    protected $pkgAutoloaderRegistries = [
        'src' => '\WebApp',
    ];

    /**
     * Package tasks to register.
     *
     * eg. 'task_handle' => \PackageHandle\Command\Task\Controller\TaskHandleController::class,
     *
     * @var array
     */
    protected $tasks = [];

    /**
     * Install or Upgrade
     *
     * @var $pkg Package
     */
    protected function installOrUpgrade(\Concrete\Core\Entity\Package $pkg): void
    {
        // Add Single Pages
        $this->addSinglePage('/dashboard/web_app', $pkg, t('Web App'));
    }

    protected function registerEvents()
    {
        Events::addListener('on_before_render', function () {
            $pkg = Core::make(PackageService::class)->getByHandle($this->pkgHandle);
            $page = Page::getCurrentPage();
            $config = $pkg->getFileConfig();

            if ($page && $config->get('web_app.activate') === true) {
                $v = View::getInstance();

                // android
                $v->addHeaderItem('<meta name="mobile-web-app-capable" content="yes" />');

                // ios
                $v->addHeaderItem('<meta name="apple-mobile-web-app-capable" content="yes" />');
                // $v->addHeaderItem('<meta name="apple-mobile-web-app-status-bar-style" content="default" />'); // a bit buggy in modern ios

                // general
                $v->addHeaderItem('<meta name="screen-orientation" content="portrait" />');
                $v->addHeaderItem('<link rel="manifest" href="/site.webmanifest" />');

                foreach ($config->get('web_app.launchscreens') as $size => $fID) {
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
                                $v->addHeaderItem('<link rel="apple-touch-startup-image" href="' . $file->getRelativePath() . '" media="' . $mediaString . '" />');
                            }
                        }
                    }
                }
            }
        });
    }

    public function getPackageName()
    {
        return t('Web App');
    }

    public function getPackageDescription()
    {
        return t('Add standalone web app functionality to a website');
    }

    public function on_start()
    {
        $this->registerEvents();
    }

    /**
     * The packages install routine.
     */
    public function install()
    {
        $pkg = parent::install();
        $this->installDatabase();
        $this->installOrUpgrade($pkg);
    }

    /**
     * The packages upgrade routine.
     */
    public function upgrade()
    {
        $pkg = Core::make(PackageService::class)->getByHandle($this->pkgHandle);
        parent::upgrade();
        $this->installOrUpgrade($pkg);
    }

    /**
     * Package uninstall routine
     */
    public function uninstall()
    {
        $manifest = DIR_BASE . '/site.webmanifest';
        if (file_exists($manifest)) {
            unlink($manifest);
        }

        return parent::uninstall();
    }
}
