<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        $genre = factory(Genre::class)->create();

        $response = $this->json('GET', route('genres.index'));

        $response->assertStatus(200);
        $response->assertJson([$genre->toArray()]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'Drama'
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Drama'
            ]);

        $response = $this->json('POST', route('genres.store'));
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
            ['name' => 'Genre', 'is_active' => true]
        );
        $response->assertJsonFragment([
            'is_active' => true
        ]);

        $response = $this->json(
            'POST',
            route('genres.store'),
            ['name' => 'Genre', 'is_active' => false]
        );
        $response->assertJsonFragment([
            'is_active' => false
        ]);

        $response = $this->json(
            'POST',
            route('genres.store'),
            ['name' => 'Genre', 'is_active' => 'invalid']
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
            ['name' => 'Drama']
        );

        $updatedGenre = Genre::find($genre->id);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Drama',
                'is_active' => true
            ])
            ->assertJson($updatedGenre->toArray());

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
}
