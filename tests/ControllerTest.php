<?php

namespace WebApp\Tests;

use PHPUnit\Framework\TestCase;
use Concrete\Package\WebApp\Controller;

defined('C5_EXECUTE') || define('C5_EXECUTE', true);
defined('DIR_PACKAGES_CORE') || define('DIR_PACKAGES_CORE', '');
defined('DIR_PACKAGES') || define('DIR_PACKAGES', '');
defined('REL_DIR_PACKAGES_CORE') || define('REL_DIR_PACKAGES_CORE', '');
defined('REL_DIR_PACKAGES') || define('REL_DIR_PACKAGES', '');

require_once dirname(__DIR__) . '/controller.php';

class ControllerTest extends TestCase
{
    private function getControllerProperty(Controller $controller, string $name): mixed
    {
        return (new \ReflectionClass(Controller::class))->getProperty($name)->getValue($controller);
    }

    private function invokeInstallServiceWorker(): void
    {
        $controller = (new \ReflectionClass(Controller::class))->newInstanceWithoutConstructor();
        (new \ReflectionMethod(Controller::class, 'installServiceWorker'))->invoke($controller);
    }

    private function createInstaller(string $directory, string $contents): void
    {
        $installer = $directory . '/vendor/bin/install-service-worker';
        mkdir(dirname($installer), 0700, true);
        file_put_contents($installer, $contents);
        chmod($installer, 0700);
    }

    private function runInTemporaryDirectory(callable $callback): void
    {
        $originalDirectory = getcwd();
        $directory = sys_get_temp_dir() . '/webappkit-controller-' . bin2hex(random_bytes(8));
        mkdir($directory, 0700);

        try {
            chdir($directory);
            $callback($directory);
        } finally {
            chdir($originalDirectory);

            $installer = $directory . '/vendor/bin/install-service-worker';
            if (is_file($installer)) {
                unlink($installer);
            }
            if (is_dir(dirname($installer))) {
                rmdir(dirname($installer));
            }
            if (is_dir(dirname(dirname($installer)))) {
                rmdir(dirname(dirname($installer)));
            }
            rmdir($directory);
        }
    }
    public function testPackageMetadata(): void
    {
        $controller = (new \ReflectionClass(Controller::class))->newInstanceWithoutConstructor();

        $this->assertSame('web_app', $this->getControllerProperty($controller, 'pkgHandle'));
        $this->assertSame('1.0.0', $this->getControllerProperty($controller, 'pkgVersion'));
        $this->assertSame('9.5.0', $this->getControllerProperty($controller, 'appVersionRequired'));
        $this->assertSame('8.4', $this->getControllerProperty($controller, 'phpVersionRequired'));
        $this->assertSame(['class_kit' => true], $this->getControllerProperty($controller, 'packageDependencies'));
        $this->assertSame(
            ['send_scheduled_notifications' => \WebApp\Command\Task\Controller\SendScheduledNotifications::class],
            $this->getControllerProperty($controller, 'tasks'),
        );
    }

    public function testInstallServiceWorkerSucceedsWhenCommandSucceeds(): void
    {
        $this->runInTemporaryDirectory(function (string $directory): void {
            $this->createInstaller($directory, "#!/bin/sh\nexit 0\n");

            $this->invokeInstallServiceWorker();

            $this->addToAssertionCount(1);
        });
    }

    public function testInstallServiceWorkerReportsCommandOutputOnFailure(): void
    {
        $this->runInTemporaryDirectory(function (string $directory): void {
            $this->createInstaller($directory, "#!/bin/sh\necho installer failed\nexit 7\n");

            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Unable to install the WebAppKit service worker. installer failed');
            $this->invokeInstallServiceWorker();
        });
    }

    public function testInstallServiceWorkerReportsWhenCommandIsMissing(): void
    {
        $this->runInTemporaryDirectory(function (): void {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('The WebAppKit service-worker Composer command was not found');
            $this->invokeInstallServiceWorker();
        });
    }
}
