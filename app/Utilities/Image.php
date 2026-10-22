<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Cache;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class Image
{
    /**
     * Resize an image (binary contents or file path) and return the encoded binary, cached for 5 minutes.
     */
    public static function resize(string $source, $width, $height): string
    {
        $width = (int) $width ?: null;
        $height = (int) $height ?: null;

        $key = 'image.resize.' . md5((is_file($source) ? $source . filemtime($source) : $source) . '|' . $width . 'x' . $height);

        return Cache::remember($key, 300, function () use ($source, $width, $height) {
            $image = (new ImageManager(new Driver()))->read($source);

            $image->resize($width, $height);

            return (string) $image->encode();
        });
    }
}
