<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $category;
    private $serializedFields = [
        'id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'per_page' => 15
                ]
            ])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'meta' => [],
                'links' => []
            ]);

        $resource =  CategoryResource::collection(collect([$this->category]));

        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJson(['data' => $category->toArray()]);
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 266),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => '255']);

        $data = [
            'is_active' => 'invalid'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');

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

        $data = [
            'name' => str_repeat('a', 266),
        ];
        $this->assertInvalidationInUpdateAction(
            $data,
            'max.string',
            ['max' => '255']
        );

        $data = [
            'is_active' => 'invalid',
        ];
        $this->assertInvalidationInUpdateAction(
            $data,
            'boolean',
        );
    }

    public function testStore()
    {
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore($data, $data + [
            'is_active' => true,
            'deleted_at' => null
        ]);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

        $data = [
            'name' => 'test',
            'description' => 'This is a description',
            'is_active' => false
        ];
        $this->assertStore($data, $data + [
            'description' => 'This is a description',
            'is_active' => false,
            'deleted_at' => null
        ]);

        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));

        $this->assertResource($response, $resource);
    }

    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'is_active' => false,
            'description' => 'description'
        ]);

        $data = [
            'name' => 'test',
            'is_active' => true,
        ];

        $response = $this->assertUpdate(
            $data,
            $data + ['deleted_at' => null],
        );

        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);
        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));

        $this->assertResource($response, $resource);

        $data = [
            'name' => 'test',
            'is_active' => true,
            'description' => ''
        ];

        $this->assertUpdate(
            $data,
            ['description' => null],
        );

        $data = [
            'name' => 'test',
            'is_active' => true,
            'description' => 'test'
        ];

        $this->assertUpdate(
            $data,
            array_merge($data, ['description' => 'test']),
        );
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('categories.destroy', [
            'category' => $this->category->id
        ]));

        $response->assertStatus(204);

        $findCategory = Category::find($this->category->id);
        $this->assertNull($findCategory);
    }

    protected function assertInvalidationRequired(TestResponse $response)
    {
        $this->assertInvalidationFields(
            $response,
            ['name'],
            'required'
        );

        $response
            ->assertStatus(422)
            ->assertJsonMissingValidationErrors([
                'is_active'
            ]);
    }

    protected function assertInvalidationRequiredAndMax(TestResponse $response)
    {
        $this->assertInvalidationFields(
            $response,
            ['name'],
            'max.string',
            ['max' => 255]
        );
    }

    protected function assertInvalidationActive(TestResponse $response)
    {
        $this->assertInvalidationFields(
            $response,
            ['is_active'],
            'boolean'
        );
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}
