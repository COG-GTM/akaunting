<?php

namespace Tests\Unit\Utilities;

use App\Utilities\Image;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class ImageTest extends TestCase
{
    public function testItResizesAnImageFromAFilePath()
    {
        $resized = Image::resize(base_path('public/img/company.png'), 128, 64);

        $image = (new ImageManager(new Driver()))->read($resized);

        $this->assertSame(128, $image->width());
        $this->assertSame(64, $image->height());
    }

    public function testItResizesAnImageFromBinaryContents()
    {
        $contents = file_get_contents(base_path('public/img/company.png'));

        $resized = Image::resize($contents, '40', '20');

        $image = (new ImageManager(new Driver()))->read($resized);

        $this->assertSame(40, $image->width());
        $this->assertSame(20, $image->height());
    }
}
