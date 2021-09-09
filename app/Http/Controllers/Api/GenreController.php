<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|string|max:255',
        'is_active' => 'boolean',
        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL'
    ];

    public function index()
    {
        return Genre::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rules);

        $self = $this;
        $genre = \DB::transaction(function () use ($self, $request,  $validatedData) {
            $genre = Genre::create($validatedData);
            $self->handleRelations($genre, $request);

            return $genre;
        });

        $genre->refresh();

        return $genre;
    }

    public function model()
    {
        return Genre::class;
    }

    public function update(Request $request, $id)
    {
        $genre = $this->findOrFail($id);
        $validateData = $this->validate($request, $this->rulesUpdate());

        $self = $this;

        $genre = \DB::transaction(function () use ($self, $request, $genre, $validateData) {
            $genre->update($validateData);
            $self->handleRelations($genre, $request);

            return $genre;
        });

        $genre->refresh();

        return $genre;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function handleRelations(Genre $genre, Request $request)
    {
        $genre->categories()->sync($request->get('categories_id'));
    }
}
