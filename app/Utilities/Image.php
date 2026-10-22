<?php

declare(strict_types=1);

namespace App\Utilities;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class Image
{
    public static function manager(): ImageManager
    {
        $driver = config('image.driver') === 'imagick' ? new ImagickDriver() : new GdDriver();

        return new ImageManager($driver);
    }

    /**
     * Resize the given image source (path or binary contents) and return the encoded bytes.
     */
    public static function resize(string $source, $width, $height): string
    {
        return static::manager()
            ->read($source)
            ->resize(static::dimension($width), static::dimension($height))
            ->encode()
            ->toString();
    }

    protected static function dimension($value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }
}
