<?php

namespace WebApp\Tests;

use WebApp\Images\Icons;
use PHPUnit\Framework\TestCase;
use WebApp\Images\LaunchScreens;

class ImageControllerTest extends TestCase
{
    public function testIconSizesAndValidationSize(): void
    {
        $this->assertSame(
            ['48x48', '72x72', '96x96', '144x144', '168x168', '192x192', '256x256', '512x512'],
            Icons::getSizes(),
        );
        $this->assertSame(['512', '512'], Icons::getValidationSize());
    }

    public function testLaunchScreenSizesAndValidationSize(): void
    {
        $this->assertSame(
            [
                '640x1136',
                '750x1334',
                '828x1792',
                '1125x2436',
                '1242x2208',
                '1170x2532',
                '1179x2556',
                '1536x2048',
                '1242x2688',
                '1290x2796',
                '1668x2224',
                '1640x2360',
                '1668x2388',
                '2048x2732',
            ],
            LaunchScreens::getSizes(),
        );
        $this->assertSame(['2048', '2732'], LaunchScreens::getValidationSize());
    }
}
