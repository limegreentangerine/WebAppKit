<?php

namespace Concrete\Package\WebApp;

use Core;
use Route;
use DateTime;
use WebApp\Events\Push;
use WebApp\Events\Setup;
use Concrete\Core\Entity\Package;
use WebApp\Log\PushNotificationLog;
use ClassKit\Package\Traits\PageTrait;
use Symfony\Component\Process\Process;
use ClassKit\Package\PackageController;
use Concrete\Core\Support\Facade\Events;
use Doctrine\ORM\EntityManagerInterface;
use WebApp\Entity\ScheduledNotification;
use Concrete\Core\Package\PackageService;

class Controller extends PackageController
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
    protected $pkgVersion = '0.0.0';

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
    protected $packageDependencies = [
        'class_kit' => true
    ];

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
    protected $tasks = [
        'send_scheduled_notifications' => \WebApp\Command\Task\Controller\SendScheduledNotifications::class,
    ];

    protected function installServiceWorker(): void
    {
        $command = DIR_BASE . '/vendor/bin/install-service-worker';

        if (!is_file($command)) {
            throw new \RuntimeException('The WebAppKit service-worker Composer command was not found: ' . $command);
        }

        $process = new Process([$command], DIR_BASE);
        $process->run();

        if (!$process->isSuccessful()) {
            $output = trim($process->getErrorOutput() . PHP_EOL . $process->getOutput());
            throw new \RuntimeException('Unable to install the WebAppKit service worker.' . ($output !== '' ? ' ' . $output : ''));
        }
    }

    /**
     * Install or Upgrade
     *
     * @var Package $pkg
     */
    public function installOrUpgrade(Package $pkg)
    {
        // Add Single Pages
        $this->addSinglePage('/dashboard/web_app', $pkg, t('Web App'));
        $this->addSinglePage('/dashboard/push_notifications', $pkg, t('Push Notifications'));
        $this->addSinglePage('/dashboard/push_notifications/custom', $pkg, t('Custom Notifications'));
        $this->addSinglePage('/dashboard/push_notifications/scheduled', $pkg, t('Scheduled Notifications'));
        $this->addSinglePage('/dashboard/push_notifications/settings', $pkg, t('Settings'));

        // install tasks
        $this->installContentFile('tasks.xml');

        $this->installServiceWorker();
    }

    public function registerRoutes(): void
    {
        Route::register('/push/subscribe', '\WebApp\Events\Subscription::subscribe');
        Route::register('/push/unsubscribe', '\WebApp\Events\Subscription::unsubscribe');
        Route::register('/push/broadcast', '\WebApp\Events\Push::broadcast');
    }

    public function registerEvents(): void
    {
        Events::addListener('on_before_render', function () {
            Setup::setupWebApp();
            Setup::registerPushAssets();
        });

        Events::addListener('on_page_type_publish', function ($event) {
            $pageType = $event->getPageTypeObject();
            $logger = Core::make(PushNotificationLog::class)->getLogger();

            if ($pageType->getPageTypeHandle() === 'news') {
                $page = $event->getPageObject();
                $now = new DateTime();
                $newsDate = $page->getCollectionDatePublicObject();

                $em = Core::make(EntityManagerInterface::class);
                $scheduledNotification = ScheduledNotification::getByColumnAndValue('referenceId', $page->getCollectionID());

                if ($scheduledNotification) {
                    if ($newsDate->format('U') > $now->format('U')) {
                        $logger->addDebug(t('Updated scheduled notification for %s', $page->getCollectionName()));
                        $scheduledNotification->setSendDate($newsDate);
                        $em->persist($scheduledNotification);
                        $em->flush();
                    } else {
                        $logger->addDebug(t('Sent scheduled notification for %s', $page->getCollectionName()));
                        Push::sendNewsNotification($page);
                    }

                } elseif ($newsDate->format('U') > $now->format('U')) {
                    $logger->addDebug(t('Scheduled notification for %s', $page->getCollectionName()));
                    $scheduledNotification = new ScheduledNotification();
                    $scheduledNotification->setType('News');
                    $scheduledNotification->setReferenceId($page->getCollectionID());
                    $scheduledNotification->setSendDate($newsDate);
                    $em->persist($scheduledNotification);
                    $em->flush();
                } else {
                    $logger->addDebug(t('Sent notification for %s', $page->getCollectionName()));
                    Push::sendNewsNotification($page);
                }
            }
        });

        Events::addListener('send_scheduled_news', function ($event) {
            $page = $event->getArgument('page');
            Push::sendNewsNotification($page);
        });

        Events::addListener('send_custom_notification', function ($event) {
            $notification = $event->getArgument('notification');
            Push::sendCustomNotification($notification);
        });
    }

    public function getPackageName()
    {
        return t('WebAppKit');
    }

    public function getPackageDescription()
    {
        return t('Add standalone web app functionality, and push notifications to a website');
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
