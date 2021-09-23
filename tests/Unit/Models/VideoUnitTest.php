<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use PHPUnit\Framework\TestCase;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\UploadFiles;

class VideoUnitTest extends TestCase
{
    private $video;

    public function setUp(): void
    {
        parent::setUp();
        $this->video = new Video();
    }

    public function testFillable()
    {
        $fillable = [
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
            'video_file',
            'thumb_file'
        ];

        $this->assertEquals(
            $fillable,
            $this->video->getFillable()
        );
    }

    public function testIfUseTraits()
    {
        $traits = [
            Uuid::class,
            SoftDeletes::class,
            UploadFiles::class
        ];

        $videoTraits = array_keys(class_uses(Video::class));

        $this->assertEqualsCanonicalizing(
            $traits,
            $videoTraits
        );
    }

    public function testHasCasts()
    {
        $casts = [
            'id' => 'string',
            'opened' => 'boolean',
            'year_launched' => 'integer',
            'duration' => 'integer'
        ];

        $this->assertEqualsCanonicalizing(
            $casts,
            $this->video->getCasts()
        );
    }

    public function assertIncrementingIsFalse()
    {
        $this->assertFalse($this->video->incrementing);
    }

    public function assertDataSoftDelete()
    {
        $this->assertContains(['deleted_at'], $this->video->dates);
    }
}
