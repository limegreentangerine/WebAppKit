<?php

namespace WebApp\Images;

use WebApp\Images\WebAppImageController;

class Icons extends WebAppImageController
{
    public static function getSizes(): array
    {
        return [
            '48x48',
            '72x72',
            '96x96',
            '144x144',
            '168x168',
            '192x192',
            '256x256',
            '512x512'
        ];
    }
}
