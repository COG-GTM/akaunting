<?php

namespace App\Utilities;

use App\Models\Common\Media;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class Image
{
    /**
     * Resize a stored media file or a local file path and return the encoded binary, cached for 5 minutes.
     */
    public static function resize(Media|string $source, $width, $height): string
    {
        $width = (int) $width ?: null;
        $height = (int) $height ?: null;

        $version = $source instanceof Media
            ? 'media.' . $source->id . '.' . $source->updated_at?->getTimestamp()
            : 'path.' . $source . '.' . filemtime($source);

        $key = 'image.resize.' . md5($version . '|' . $width . 'x' . $height);

        return Cache::remember($key, 300, function () use ($source, $width, $height) {
            $contents = $source instanceof Media ? $source->contents() : $source;

            $image = (new ImageManager(new Driver()))->read($contents);

            $image->resize($width, $height);

            return (string) $image->encode();
        });
    }
}
