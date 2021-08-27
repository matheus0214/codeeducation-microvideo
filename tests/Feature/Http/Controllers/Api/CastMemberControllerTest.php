<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $members = factory(CastMember::class)->create();
        $response = $this->json('GET', route('cast_members.index'));
        // dd($members->toArray(), $response->content()->toArray());

        $response
            ->assertStatus(200)
            ->assertJson([$members->toArray()]);
    }

    public function testShow()
    {
        $member = factory(CastMember::class)->create();
        $member->refresh();
        $response = $this->get(route('cast_members.show', ['cast_member' => $member->id]));

        $response
            ->assertStatus(200)
            ->assertJson($member->toArray());
    }

    public function testStore()
    {
        $response = $this->json(
            'POST',
            route('cast_members.store'),
            [
                'name' => 'Juan',
                'type' => 1
            ]
        );

        $response->assertStatus(201)->assertJson([
            'name' => 'Juan',
            'type' => 1
        ]);

        $response = $this->json(
            'POST',
            route('cast_members.store'),
            [
                'name' => 'Juan',
                'type' => 3
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'type'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.not_in', ['attribute' => 'type'])
            ]);

        $response = $this->json(
            'POST',
            route('cast_members.store'),
            [
                'type' => 2
            ]
        );
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);

        $response = $this->json(
            'POST',
            route('cast_members.store'),
            [
                'name' => str_repeat('a', 256),
                'type' => 2
            ]
        );
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    public function testUpdate()
    {
        $member = CastMember::create([
            'name' => 'Alfredo',
            'type' => 2
        ]);

        $response = $this->json(
            'PUT',
            route('cast_members.update', ['cast_member' => $member->id]),
            [
                'name' => 'Juan',
                'type' => 1
            ]
        );

        $response->assertStatus(200)->assertJson([
            'name' => 'Juan',
            'type' => 1
        ]);

        $response = $this->json(
            'PUT',
            route('cast_members.update', ['cast_member' => $member->id]),
            [
                'name' => 'Juan',
                'type' => 3
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'type'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.not_in', ['attribute' => 'type'])
            ]);

        $response = $this->json(
            'PUT',
            route('cast_members.update', ['cast_member' => $member->id]),
            [
                'name' => str_repeat('a', 256),
            ]
        );
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name'
            ])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    public function testDestroy()
    {
        /** @var CastMember $member */
        $member = factory(CastMember::class)->create();
        $member->refresh();
        $response = $this->delete(route('cast_members.destroy', ['cast_member' => $member->id]));

        $response
            ->assertStatus(204);

        $find = CastMember::find($member->id);
        $this->assertNull($find);

        $findTrash = CastMember::withTrashed()->find($member->id);

        $this->assertEquals($member->id, $findTrash->id);
        $this->assertNotNull($findTrash->deleted_at);
    }
}
