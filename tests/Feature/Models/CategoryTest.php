<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class, 1)->create();

        $categories = Category::all();
        $this->assertCount(1, $categories);

        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoryKeys);
    }

    public function testCreate()
    {
        $category = Category::create([
            'name' => 'Category one'
        ]);
        $category->refresh();

        $this->assertEquals('Category one', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        $this->assertTrue(Uuid::isValid($category->id));

        $category = Category::create([
            'name' => 'Category one',
            'description' => null
        ]);
        $this->assertNull($category->description);

        $category = Category::create([
            'name' => 'Category one',
            'description' => 'Test description'
        ]);
        $this->assertEquals('Test description', $category->description);

        $category = Category::create([
            'name' => 'Category one',
            'is_active' => false
        ]);
        $this->assertFalse($category->is_active);

        $category = Category::create([
            'name' => 'Category one',
            'is_active' => true
        ]);
        $this->assertTrue($category->is_active);
    }

    public function testUpdate()
    {
        $category = factory(Category::class, 1)->create([
            'description' => 'test_description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'name updated',
            'description' => 'test_description_updated',
            'is_active' => true
        ];

        $category->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testDestroy()
    {
        $category = factory(Category::class, 1)->create()->first();

        $category->delete();

        $this->assertNull(Category::find($category->id));
    }
}
