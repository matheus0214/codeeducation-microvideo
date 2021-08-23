<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use DatabaseMigrations;

    public function testBasicTest()
    {
        $response = $this->post('/api/categories', [
            'name' => 'Drama',
            'description' => 'Lorem ipsum silor',
            'is_active' => true
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(201);
        $this->arrayHasKey('id', $response->decodeResponseJson());
        $this->assertEquals('Drama', $response->decodeResponseJson('name'));
    }
}
