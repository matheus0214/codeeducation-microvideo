<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerUploadTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves, TestResources;

    public function testInvalidationVideo()
    {
        $file = UploadedFile::fake()->create('video.mp4', Video::VIDEO_FILE_MAX_SIZE + 10);
        $response = $this->json('POST', $this->routeStore(), $this->sendData + ['video_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'video file', 'max' => Video::VIDEO_FILE_MAX_SIZE]),]
            );

        $response = $this->json('PUT', $this->routeUpdate(), ['video_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'video file', 'max' => Video::VIDEO_FILE_MAX_SIZE]),]
            );
    }

    public function testInvalidationThumb()
    {
        $file = UploadedFile::fake()->create('video.png', Video::THUMB_FILE_MAX_SIZE + 10);
        $response = $this->json('POST', $this->routeStore(), $this->sendData + ['thumb_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['thumb_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'thumb file', 'max' => Video::THUMB_FILE_MAX_SIZE]),]
            );

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + ['thumb_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['thumb_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'thumb file', 'max' => Video::THUMB_FILE_MAX_SIZE]),]
            );
    }

    public function testInvalidationBanner()
    {
        $file = UploadedFile::fake()->create('video.png', Video::THUMB_FILE_MAX_SIZE + 10);
        $response = $this->json('POST', $this->routeStore(), $this->sendData + ['thumb_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['thumb_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'thumb file', 'max' => Video::THUMB_FILE_MAX_SIZE]),]
            );

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + ['thumb_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['thumb_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'thumb file', 'max' => Video::THUMB_FILE_MAX_SIZE]),]
            );
    }

    public function testInvalidationTrailer()
    {
        $file = UploadedFile::fake()->create('video.mp4', Video::TRAILER_FILE_MAX_SIZE + 10);
        $response = $this->json('POST', $this->routeStore(), $this->sendData + ['trailer_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['trailer_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'trailer file', 'max' => Video::TRAILER_FILE_MAX_SIZE]),]
            );

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + ['trailer_file' => $file]);

        \Storage::assertMissing($this->video->id . '/' . $file->hashName());
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['trailer_file'])
            ->assertJsonFragment(
                [\Lang::get('validation.max.file', ['attribute' => 'trailer file', 'max' => Video::TRAILER_FILE_MAX_SIZE]),]
            );
    }

    public function testCreateWithFiles()
    {
        $video = UploadedFile::fake()->create('video.mp4');
        $trailer = UploadedFile::fake()->create('trailer.mp4', 11);
        $thumb = UploadedFile::fake()->create('thumb.png', 4);
        $banner = UploadedFile::fake()->create('trailer.png', 1);

        $response = $this->json('POST', $this->routeStore(), $this->sendData + [
            'video_file' => $video,
            'trailer_file' => $trailer,
            'thumb_file' => $thumb,
            'banner_file' => $banner,
        ]);
        \Storage::assertExists($response->json('data.id') . '/' . $video->hashName());
        \Storage::assertExists($response->json('data.id') . '/' . $trailer->hashName());
        \Storage::assertExists($response->json('data.id') . '/' . $thumb->hashName());
        \Storage::assertExists($response->json('data.id') . '/' . $banner->hashName());

        $response->assertStatus(201);
    }

    public function testUpdateWithFiles()
    {
        $video = UploadedFile::fake()->create('video.mp4');
        $trailer = UploadedFile::fake()->create('trailer.mp4', 11);
        $thumb = UploadedFile::fake()->create('thumb.png', 4);
        $banner = UploadedFile::fake()->create('trailer.png', 1);

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + [
            'video_file' => $video,
            'trailer_file' => $trailer,
            'thumb_file' => $thumb,
            'banner_file' => $banner,
        ]);
        \Storage::assertExists($response->json('data.id') . '/' . $video->hashName());
        \Storage::assertExists($response->json('data.id') . '/' . $trailer->hashName());
        \Storage::assertExists($response->json('data.id') . '/' . $thumb->hashName());
        \Storage::assertExists($response->json('data.id') . '/' . $banner->hashName());

        $response->assertStatus(200);
    }

    public function testInvalidationVideoStore()
    {
        $fileInvalid = UploadedFile::fake()->create('video_file.txt', 3);
        $response = $this->json(
            'POST',
            route('videos.store'),
            $this->sendData + ['video_file' => $fileInvalid]
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_file'])
            ->assertJsonFragment([
                \Lang::get(
                    'validation.mimetypes',
                    ['attribute' => 'video file', 'values' => 'mp4']
                )
            ]);
    }

    public function testInvalidationVideoUpdate()
    {
        $fileInvalid = UploadedFile::fake()->create('video_file.txt', 3);
        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $this->video->id]),
            $this->sendData + ['video_file' => $fileInvalid]
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_file'])
            ->assertJsonFragment([
                \Lang::get(
                    'validation.mimetypes',
                    ['attribute' => 'video file', 'values' => 'mp4']
                )
            ]);
    }

    public function testStoreWithFiles()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + ['video_file' => $file]
        );

        \Storage::assertExists($response->json('data.id') . '/' . $file->hashName());

        $file = UploadedFile::fake()->create('video.mp4', 'video/mp4');
        $fileUpdate = UploadedFile::fake()->create('video_update.mp4');
        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + ['video_file' => $file]
        );

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $response->json('data.id')]),
            $this->sendData + ['video_file' => $fileUpdate]
        );

        $response->assertStatus(200);

        \Storage::assertExists($response->json('data.id') . '/' . $fileUpdate->hashName());
        \Storage::assertMissing($response->json('data.id') . '/' . $file->hashName());

        $id = $response->json('data.id');
        $resource = new VideoResource(Video::find($id));

        $this->assertResource($response, $resource);
    }

    protected  function model()
    {
        return Video::class;
    }

    protected  function routeStore()
    {
        return route('videos.store');
    }

    protected  function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }
}
