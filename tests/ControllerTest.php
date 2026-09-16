<?php

declare(strict_types=1);

if (!defined('C5_EXECUTE')) {
    define('C5_EXECUTE', true);
}

if (!defined('DIR_PACKAGES_CORE')) {
    define('DIR_PACKAGES_CORE', __DIR__ . '/../vendor');
}

if (!defined('DIR_PACKAGES')) {
    define('DIR_PACKAGES', __DIR__ . '/../packages');
}

if (!defined('REL_DIR_PACKAGES_CORE')) {
    define('REL_DIR_PACKAGES_CORE', '/packages');
}

if (!defined('REL_DIR_PACKAGES')) {
    define('REL_DIR_PACKAGES', '/packages');
}

if (!defined('DIR_BASE')) {
    define('DIR_BASE', dirname(__DIR__));
}

if (!function_exists('t')) {
    function t(string $text): string
    {
        return $text;
    }
}

require_once dirname(__DIR__) . '/controller.php';

use PHPUnit\Framework\TestCase;
use Concrete\Package\WebApp\Controller;

class TestableWebAppController extends Controller
{
    public array $singlePageCalls = [];

    public bool $eventsRegistered = false;

    public function __construct() {}

    protected function addSinglePage(string $path, $pkg, string $name = '', string $description = '')
    {
        $this->singlePageCalls[] = [$path, $pkg, $name, $description];
    }

    protected function registerEvents()
    {
        $this->eventsRegistered = true;
    }

    public function invokeInstallOrUpgrade(object $pkg): void
    {
        $this->installOrUpgrade($pkg);
    }

    public function invokeOnStart(): void
    {
        $this->on_start();
    }
}

final class ControllerTest extends TestCase
{
    public function testItRegistersTheDashboardSinglePageOnInstallOrUpgrade(): void
    {
        $pkg = new \Concrete\Core\Entity\Package();
        $controller = new TestableWebAppController();

        $controller->invokeInstallOrUpgrade($pkg);

        self::assertSame([
            '/dashboard/web_app',
            $pkg,
            'Web App',
            '',
        ], $controller->singlePageCalls[0]);
    }

    public function testItProvidesThePackageMetadata(): void
    {
        $controller = new TestableWebAppController();

        self::assertSame('Web App', $controller->getPackageName());
        self::assertSame('Add standalone web app functionality to a website', $controller->getPackageDescription());
    }

    public function testOnStartRegistersPackageEvents(): void
    {
        $controller = new TestableWebAppController();

        $controller->invokeOnStart();

        self::assertTrue($controller->eventsRegistered);
    }
}
