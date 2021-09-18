<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestValidations, TestSaves;


    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationCategoriesIdField()
    {
        $data = ['categories_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['categories_id' => ['a']];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationGenresIdField()
    {
        $data = ['genres_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['genres_id' => ['a']];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            'genres_id' => [$genre->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationMax()
    {
        $data = [
            'title' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = [
            'duration' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationLaunchedYear()
    {
        $data = [
            'year_launched' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpenedField()
    {
        $data = [
            'opened' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField()
    {
        $data = [
            'rating' => 0
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testSave()
    {
        $testData = array_diff_key($this->sendData, [
            'categories_id' => $this->sendData['categories_id'],
            'genres_id' => $this->sendData['genres_id']
        ]);

        $data = [
            [
                'send_data' => $this->sendData + ['opened' => false],
                'test_data' => $testData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $testData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
            $this->assertHasCategory($response->json('id'), $this->sendData['categories_id'][0]);
            $this->assertHasGenre($response->json('id'), $this->sendData['genres_id'][0]);

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
        }
    }

    public function testShow()
    {
        $response = $this->json('GET', route('videos.show', ['video' => $this->video->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
    }


    protected function assertHasCategory(
        $videoId,
        $categoryId
    ) {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoId,
            'category_id' => $categoryId
        ]);
    }

    protected function assertHasGenre(
        $videoId,
        $genreId
    ) {
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genreId,
            'video_id' => $videoId
        ]);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        /** @var Collection $genres */
        $genres = factory(Genre::class, 3)->create();

        $genres->each(function ($g) use ($categoriesId) {
            $g->categories()->attach($categoriesId);
        });

        $response = $this->json('POST', $this->routeStore(), $this->sendData);
        $this->assertDatabaseHas(
            'category_video',
            [
                'category_id' => $this->sendData['categories_id'][0],
                'video_id' => $response->json('id'),
            ]
        );

        $sendData = [
            'title' => 'This is my title',
            'description' => 'Lorem ipsum',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$categoriesId[1], $categoriesId[2]],
            'genres_id' => [$genres[0]->id]
        ];

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' =>
            $response->json('id')]),
            $sendData
        );

        $this->assertDatabaseMissing(
            'category_video',
            [
                'category_id' => $this->sendData['categories_id'][0],
                'video_id' => $response->json('id')
            ]
        );

        $this->assertDatabaseHas(
            'category_video',
            [
                'category_id' => $categoriesId[1],
                'video_id' => $response->json('id')
            ]
        );

        $this->assertDatabaseHas(
            'category_video',
            [
                'category_id' => $categoriesId[2],
                'video_id' => $response->json('id')
            ]
        );
    }

    public function testInvalidationVideoStore()
    {
        $fileInvalid = UploadedFile::fake()->create('video_file.txt');
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

        $fileInvalid = UploadedFile::fake()->create('video_file.txt', 15);
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
                    'validation.max.file',
                    ['attribute' => 'video file', 'max' => 12]
                )
            ]);
    }

    public function testInvalidationVideoUpdate()
    {
        $fileInvalid = UploadedFile::fake()->create('video_file.txt');
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

        $fileInvalid = UploadedFile::fake()->create('video_file.txt', 15);
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
                    'validation.max.file',
                    ['attribute' => 'video file', 'max' => 12]
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

        \Storage::assertExists($response->json('id') . '/' . $file->hashName());

        $file = UploadedFile::fake()->create('video.mp4', 'video/mp4');
        $fileUpdate = UploadedFile::fake()->create('video_update.mp4');
        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + ['video_file' => $file]
        );

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + ['video_file' => $fileUpdate]
        );

        $response->assertStatus(200);

        \Storage::assertExists($response->json('id') . '/' . $fileUpdate->hashName());
        \Storage::assertMissing($response->json('id') . '/' . $file->hashName());
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
