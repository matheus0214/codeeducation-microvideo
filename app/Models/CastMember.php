<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes, \App\Models\Traits\Uuid;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 1;

    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];
    protected $keyType = 'string';
    protected $casts = [
        'type' => 'integer'
    ];

    public $incrementing = false;
}
