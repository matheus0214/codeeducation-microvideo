<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class)->create();

        $castMembers = CastMember::all();

        $this->assertCount(1, $castMembers);

        $castMembersKeys = array_keys($castMembers->first()->toArray());

        $this->assertEqualsCanonicalizing(
            ['id', 'name', 'type', 'created_at', 'updated_at', 'deleted_at'],
            $castMembersKeys
        );
    }

    public function testStore()
    {
        $castMembers = CastMember::create([
            'name' => 'Aldair',
            'type' => 1
        ]);
        $castMembers->refresh();

        $this->assertEquals('Aldair', $castMembers->name);
        $this->assertEquals(1, $castMembers->type);
        $this->assertArrayHasKey('deleted_at', $castMembers->getAttributes());

        $castMembers = CastMember::create([
            'name' => 'Aldair',
            'type' => 2
        ]);
        $castMembers->refresh();
        $this->assertEquals(2, $castMembers->type);
    }

    public function testUpdate()
    {
        $castMembers = CastMember::create([
            'name' => 'Aldair',
            'type' => 1
        ]);
        $castMembers->refresh();

        $castMembers->update(['name' => 'Sidne']);

        $this->assertEquals('Sidne', $castMembers->name);

        $castMembers = CastMember::create([
            'name' => 'Aldair',
            'type' => 1
        ]);

        $castMembers->update(['type' => 2]);

        $this->assertEquals(2, $castMembers->type);
    }

    public function testDestroy()
    {
        $castMembers = CastMember::create([
            'name' => 'Lincon',
            'type' => 1
        ]);

        $castMembers->delete();

        $member = CastMember::find($castMembers->id);

        $this->assertNull($member);
    }
}
