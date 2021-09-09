<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CastMemberController extends Controller
{
    protected $rules;

    public function __construct()
    {
        $this->rules = [
            'name' => 'required|max:255',
            'type' => 'required|in:' . implode(',', [CastMember::TYPE_ACTOR, CastMember::TYPE_DIRECTOR]),
        ];
    }

    public function index()
    {
        return CastMember::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rules);

        $castMember = CastMember::create($validatedData);
        $castMember->refresh();

        return $castMember;
    }

    public function show(CastMember $castMember)
    {
        return $castMember;
    }

    public function update(Request $request, CastMember $castMember)
    {
        $validatedData = $this->validate($request, $this->rules);

        $castMember->update($validatedData);

        return $castMember;
    }

    public function destroy(CastMember $castMember)
    {
        $castMember->delete();

        return response()->noContent();
    }
}