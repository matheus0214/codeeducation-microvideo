<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreate()
    {
        $genre = Genre::create([
            'name' => 'Drama',
            'is_active' => true
        ]);
        $genre->refresh();

        $keys = array_keys($genre->getAttributes());

        $this->assertTrue($genre->is_active);
        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            $keys
        );
        $this->assertTrue(Uuid::isValid($genre->id));

        $genre = Genre::create([
            'name' => 'Suspense',
            'is_active' => false
        ]);
        $this->assertFalse($genre->is_active);
    }

    public function testUpdate()
    {
        $genre = Genre::create([
            'name' => 'Drama',
            'is_active' => true
        ]);

        $genre->update([
            'name' => 'Suspense'
        ]);
        $this->assertEquals('Suspense', $genre->name);

        $genre->update([
            'is_active' => false
        ]);
        $this->assertFalse($genre->is_active);
    }

    public function testDelete()
    {
        $genre = Genre::create([
            'name' => 'Drama',
            'is_active' => true
        ]);

        $genre->delete();

        $this->assertNull(Genre::find($genre->id));
    }
}
