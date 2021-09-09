<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;
use \App\Models\Traits\Uuid;

class CastMemberUnitTest extends TestCase
{
    /** @var CastMember $castMember*/
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = new CastMember();
    }

    public function testFillable()
    {
        $fillable = ['name', 'type'];

        $this->assertEquals($fillable, $this->castMember->getFillable());
    }

    public function testCasts()
    {
        $casts = ['type' => 'integer'];

        $this->assertEquals($casts, $this->castMember->getCasts());
    }

    public function testHasDeletedAt()
    {
        $this->assertContains('deleted_at', $this->castMember->getDates());
    }

    public function testHasTraits()
    {
        $traits = [
            Uuid::class,
            SoftDeletes::class,
        ];

        $classTraits = array_keys(class_uses(CastMember::class));

        $this->assertEqualsCanonicalizing(
            $traits,
            $classTraits
        );
    }
}
