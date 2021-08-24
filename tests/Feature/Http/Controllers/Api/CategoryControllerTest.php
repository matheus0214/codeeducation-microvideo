<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testInvalidationData()
    {
        $response = $this->json(
            'POST',
            route('categories.store'),
            [],
            ['Accept' => 'application/json']
        );

        $this->assertInvalidationRequired($response);

        $response = $this->json(
            'POST',
            route('categories.store'),
            [
                'name' => str_repeat('a', 266),
                'is_active' => 'invalid'
            ],
            ['Accept' => 'application/json']
        );

        $this->assertInvalidationRequiredAndMax($response);

        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT',
            route('categories.update', [
                'category' => $category->id
            ]),
            [
                'name' => str_repeat('a', 266),
                'is_active' => 'invalid'
            ],
        );
        $this->assertInvalidationActive($response);
        $this->assertInvalidationRequiredAndMax($response);
    }

    protected function assertInvalidationRequired(TestResponse $response)
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

    protected function assertInvalidationRequiredAndMax(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    protected function assertInvalidationActive(TestResponse $response)
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

    public function testStore()
    {
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);

        $category = Category::find($response->json('id'));

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'is_active' => false,
            'description' => 'This is my description'
        ]);

        $category = Category::find($response->json('id'));

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $response->assertJsonFragment([
            'is_active' => false,
            'description' => 'This is my description'
        ]);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'is_active' => false,
            'description' => 'description'
        ]);

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'is_active' => true,
                'description' => 'Lorem ipsum'
            ]
        );

        $category = Category::find($response->json('id'));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'description' => 'Lorem ipsum'
            ]);

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'is_active' => true,
                'description' => ''
            ]
        );

        $response->assertJsonFragment([
            'description' => null
        ]);
    }

    public function testDelete()
    {
        $category = factory(Category::class)->create();

        $response = $this->json('DELETE', route('categories.destroy', [
            'category' => $category->id
        ]));

        $response->assertStatus(204);

        $findCategory = Category::find($category->id);
        $this->assertNull($findCategory);
    }
}
