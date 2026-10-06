<?php

namespace WebApp\Images;

use WebApp\Images\WebAppImageController;

class LaunchScreens extends WebAppImageController
{
    public static function getSizes(): array
    {
        return [
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
            '2048x2732'
        ];
    }
}
