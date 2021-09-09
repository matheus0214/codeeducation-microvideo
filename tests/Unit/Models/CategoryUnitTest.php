<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Traits\Uuid;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryUnitTest extends TestCase
{

    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = new Category();
    }

    public function testFillable()
    {
        $fillable = ['name', 'description', 'is_active'];

        $this->assertEquals(
            $fillable,
            $this->category->getFillable()
        );
    }

    public function testIfUseTraits()
    {
        $traits = [
            SoftDeletes::class,
            Uuid::class,
        ];
        $categoryTraits = array_keys(class_uses(Category::class));

        $this->assertEquals($traits, $categoryTraits);
    }

    public function testHasCasts()
    {
        $casts = [
            'is_active' => 'boolean'
        ];

        $this->assertEquals($casts, $this->category->getCasts());
    }

    public function testIncrementIsFalse()
    {

        $this->assertFalse($this->category->getIncrementing());
    }

    public function testHasSoftDelete()
    {
        $dates = 'deleted_at';

        // dd($category->getDates());

        $this->assertContains($dates, $this->category->getDates());
    }
}
