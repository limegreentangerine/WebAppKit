<?php

namespace WebApp\Images;

abstract class WebAppImageController
{
    abstract protected static function getSizes(): array;

    public static function getValidationSize(): array
    {
        $sizes = static::getSizes();
        return explode('x', end($sizes));
    }
}
