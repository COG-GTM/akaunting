<?php

namespace Tests\Unit\Utilities;

use App\Models\Common\Media;
use App\Utilities\Image;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Plank\Mediable\Facades\MediaUploader;
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

    public function testItResizesAStoredMediaFile()
    {
        $media = MediaUploader::beforeSave(fn ($media) => $media->company_id = company_id())
            ->fromSource(base_path('public/img/company.png'))
            ->toDirectory('tests/image-resize')
            ->onDuplicateReplace()
            ->upload();

        $resized = Image::resize(Media::findOrFail($media->id), '40', '20');

        $media->delete();

        $image = (new ImageManager(new Driver()))->read($resized);

        $this->assertSame(40, $image->width());
        $this->assertSame(20, $image->height());
    }
}
