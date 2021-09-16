<?php

namespace Tests\Unit\Models\Traits;

use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use Tests\Stubs\Models\UploadFilesStub;

class UploadFilesUnitTest extends TestCase
{
    /** @var UploadFilesStub */
    private $obj;

    public function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
    }

    public function testUploadFile()
    {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);

        \Storage::assertExists("1/" . $file->hashName());
    }

    public function testUploadFiles()
    {
        \Storage::fake();

        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFiles([$file1, $file2]);

        \Storage::assertExists("1/" . $file1->hashName());
        \Storage::assertExists("1/" . $file2->hashName());
    }

    public function testDeleteFile()
    {
        \Storage::fake();

        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);

        $this->obj->deleteFile($file->hashName());

        \Storage::assertMissing("1/" . $file->hashName());

        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);

        $this->obj->deleteFile($file);

        \Storage::assertMissing("1/" . $file->hashName());
    }

    public function testDeleteFiles()
    {
        \Storage::fake();

        $file1 = UploadedFile::fake()->create('video.mp4');
        $file2 = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file1);
        $this->obj->uploadFile($file2);

        $this->obj->deleteFiles([$file1->hashName(), $file2]);

        \Storage::assertMissing("1/" . $file1->hashName());
        \Storage::assertMissing("1/" . $file2->hashName());

        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);

        $this->obj->deleteFiles([$file]);

        \Storage::assertMissing("1/" . $file->hashName());
    }

    public function testExtractFiles()
    {
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);

        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);

        $this->assertCount(1, $attributes);
        $this->assertCount(0, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = ['file' => $file1, 'other' => 'test'];

        $files = UploadFilesStub::extractFiles($attributes);

        $this->assertCount(2, $attributes);
        $this->assertEquals(['file' => $file1->hashName(), 'other' => 'test'], $attributes);
        $this->assertEquals([$file1], $files);
    }
}
