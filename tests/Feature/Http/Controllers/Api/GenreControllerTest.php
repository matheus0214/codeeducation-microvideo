<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;
    private $category;
    private $sendData;

    public function setUp(): void
    {
        parent::setUp();

        $this->genre = factory(Genre::class)->create();
        $this->category = factory(Category::class)->create();

        $this->sendData = [
            'name' => 'Drama',
            'categories_id' => [$this->category->id]
        ];
    }

    public function testList()
    {
        $response = $this->json('GET', route('genres.index'));

        $response->assertStatus(200);
        $response->assertJson([$this->genre->toArray()]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'Drama',
            'categories_id' => [$this->category->id]
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Drama'
            ]);
        $this->assertHasCategory($response->json('id'), $this->category->id);

        $response = $this->json('POST', route('genres.store'), [
            'categories_id' => [$this->category->id]
        ]);
        $this->assertNameRequired($response);

        $response = $this->json(
            'POST',
            route('genres.store'),
            ['name' => str_repeat('asd', 256)]
        );
        $this->assertNameMaxLength($response);

        $response = $this->json(
            'POST',
            route('genres.store'),
            ['name' => 'Genre', 'is_active' => true, 'categories_id' => [$this->category->id]]
        );
        $response->assertJsonFragment([
            'is_active' => true
        ]);

        $response = $this->json(
            'POST',
            route('genres.store'),
            [
                'name' => 'Genre', 'is_active' => false,
                'categories_id' => [$this->category->id]
            ]
        );
        $response->assertJsonFragment([
            'is_active' => false
        ]);

        $response = $this->json(
            'POST',
            route('genres.store'),
            [
                'name' => 'Genre', 'is_active' => 'invalid',
                'categories_id' => [$this->category->id]
            ]
        );
        $this->assertIsActiveIsBoolean($response);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json('GET', route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());

        $response = $this->json('GET', route('genres.show', ['genre' => 'Not register']));
        $response
            ->assertStatus(404);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            [
                'name' => 'Drama',
                'categories_id' => [$this->category->id]
            ]
        );

        $updatedGenre = Genre::find($genre->id);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Drama',
                'is_active' => true
            ])
            ->assertJson($updatedGenre->toArray());
        $this->assertHasCategory($response->json('id'), $this->category->id);

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            ['name' => str_repeat('a', 256)]
        );
        $this->assertNameMaxLength($response);

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            ['is_active' => 'invalid']
        );
        $this->assertIsActiveIsBoolean($response);

        $response = $this->json(
            'GET',
            route('genres.update', ['genre' => 'not register']),
            ['name' => 'Drama']
        );
        $response
            ->assertStatus(404);
    }

    public function testDelete()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $genre->id]),
        );

        $deletedGenre = Genre::find($genre->id);

        $response
            ->assertStatus(204);
        $this->assertNull($deletedGenre);

        $response = $this->json(
            'GET',
            route('genres.update', ['genre' => 'not register']),
            ['name' => 'Drama']
        );
        $response
            ->assertStatus(404);
    }

    public function testInvalidationData()
    {
        $dataRequired = [
            'name' => '',
            'categories_id' => ''
        ];
        $this->assertInvalidationInStoreAction(
            $dataRequired,
            'required',
        );
        $this->assertInvalidationInUpdateAction(
            $dataRequired,
            'required',
        );

        $dataMaxName = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction(
            $dataMaxName,
            'max.string',
            ['max' => 255]
        );
        $this->assertInvalidationInUpdateAction(
            $dataMaxName,
            'max.string',
            ['max' => 255]
        );

        $dataMaxName = ['is_active' => 'a'];
        $this->assertInvalidationInStoreAction(
            $dataMaxName,
            'boolean',
        );
        $this->assertInvalidationInUpdateAction(
            $dataMaxName,
            'boolean',
        );

        $dataMaxName = ['categories_id' => [100]];
        $this->assertInvalidationInStoreAction(
            $dataMaxName,
            'in',
        );
        $this->assertInvalidationInUpdateAction(
            $dataMaxName,
            'in',
        );

        $dataMaxName = ['categories_id' => 100];
        $this->assertInvalidationInStoreAction(
            $dataMaxName,
            'array',
        );
        $this->assertInvalidationInUpdateAction(
            $dataMaxName,
            'array',
        );

        $categoryDelete = factory(Category::class)->create();
        $categoryDelete->delete();

        $dataMaxName = ['categories_id' => [$categoryDelete->id]];
        $this->assertInvalidationInStoreAction(
            $dataMaxName,
            'exists',
        );
        $this->assertInvalidationInUpdateAction(
            $dataMaxName,
            'exists',
        );
    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test',
            ]);

        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        /** @var \Illuminate\Http\Request $request */
        $request = \Mockery::mock(\Illuminate\Http\Request::class);

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test',
            ]);

        $controller
            ->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        /** @var \Illuminate\Http\Request $request */
        $request = \Mockery::mock(\Illuminate\Http\Request::class);

        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $exception) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function assertNameRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    public function assertNameMaxLength(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => '255'])
            ]);
    }

    public function assertIsActiveIsBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'is_active'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesId[0]]
        ];

        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas(
            'category_genre',
            [
                'category_id' => $categoriesId[0],
                'genre_id' => $response->json('id')
            ]
        );

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ];

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' =>
            $response->json('id')]),
            $sendData
        );
        $this->assertDatabaseMissing(
            'category_genre',
            [
                'category_id' => $categoriesId[0],
                'genre_id' => $response->json('id')
            ]
        );

        $this->assertDatabaseHas(
            'category_genre',
            [
                'category_id' => $categoriesId[1],
                'genre_id' => $response->json('id')
            ]
        );

        $this->assertDatabaseHas(
            'category_genre',
            [
                'category_id' => $categoriesId[2],
                'genre_id' => $response->json('id')
            ]
        );
    }

    protected function assertHasCategory(
        $genreId,
        $categoryId
    ) {
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $genreId,
            'category_id' => $categoryId
        ]);
    }

    protected  function model()
    {
        return Genre::class;
    }

    protected  function routeStore()
    {
        return route('genres.store');
    }

    protected  function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }
}
