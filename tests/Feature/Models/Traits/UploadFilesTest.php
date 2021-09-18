<?php

namespace Tests\Feature\Models\Traits;

use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();

        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
    }

    public function testMakeOlFilesOnSaving()
    {
        $this->obj->fill([
            'name' => 'test',
            'file' => 'test1.mp4',
            'banner' => 'test2.mp4'
        ]);
        $this->obj->save();

        $this->assertCount(0, $this->obj->oldFiles);

        $this->obj->update([
            'name' => 'test_name',
            'banner' => 'test3.mp4'
        ]);

        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->obj->oldFiles);

        UploadFilesStub::create([
            'name' => 'test'
        ]);
    }

    public function testMakeOldFilesNullOnSave()
    {
        $this->obj->fill([
            'name' => 'test',
        ]);
        $this->obj->save();

        $this->obj->update([
            'name' => 'test_name',
            'banner' => 'test3.mp4'
        ]);
        $this->assertEqualsCanonicalizing([], $this->obj->oldFiles);
    }
}
