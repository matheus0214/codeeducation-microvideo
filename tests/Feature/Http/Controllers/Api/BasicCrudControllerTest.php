<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;
use App\Models\Category;

class BasicCrudControllerTest extends TestCase
{
    /** @var CategoryControllerStub $controller */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        /** @var CategoryStub $category */
        $category = CategoryStub::create([
            'name' => 'This is a name',
            'description' => 'Description'
        ]);

        $result =  $this->controller->index()->toArray();

        $this->assertEquals([$category->toArray()], $result);
    }

    public function testInvalidateDateInStore()
    {
        $this->expectException(ValidationException::class);

        /** @var \Illuminate\Http\Request */
        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);

        $this->controller->store($request);
    }

    public function testStore()
    {
        /** @var \Illuminate\Http\Request */
        $request = \Mockery::mock(Request::class);

        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name', 'description' => 'This is a description']);

        $obj = $this->controller->store($request);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $obj->toArray()
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        /** @var CategoryStub $category */
        $category = CategoryStub::create([
            'name' => 'This is a name',
            'description' => 'Description'
        ]);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');

        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [$category->id]);

        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testIfFindOrFailFetchThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');

        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, ['0']);
    }

    public function testShow()
    {
        /** @var Category $category */
        $category = CategoryStub::create([
            'name' => 'Drama',
            'description' => 'Drama movies'
        ]);

        $result = $this->controller->show($category->id);

        $this->assertEquals($category->toArray(), $result->toArray());
    }

    public function testDelete()
    {
        /** @var Category $category */
        $category = CategoryStub::create([
            'name' => 'Drama',
            'description' => 'Drama movies'
        ]);

        $result = $this->controller->destroy($category->id);

        $this->createTestResponse($result)->assertStatus(204);
    }

    public function testUpdate()
    {
        /** @var \Illuminate\Http\Request */
        $request = \Mockery::mock(Request::class);

        $category = CategoryStub::create([
            'name' => 'Suspense'
        ]);

        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn([
                'name' => 'Drama'
            ]);

        $result = $this->controller->update($request, $category->id);

        $this->assertEquals($category->id, $result->id);

        $categoryFind = CategoryStub::find($category->id);

        $this->assertEquals('Drama', $categoryFind->name);
    }
}
