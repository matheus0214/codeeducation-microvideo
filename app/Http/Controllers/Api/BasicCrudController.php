<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();
    protected abstract function rulesStore();
    protected abstract function rulesUpdate();

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());

        /** @var Category $obj */
        $obj = $this->model()::create($validatedData);
        $obj->refresh();

        return $obj;
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();

        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function show($id)
    {
        $obj = $this->findOrFail($id);

        return $obj;
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $category = $this->findOrFail($id);
        $category->update($validatedData);

        return $category;
    }

    public function destroy($id)
    {
        $obj = $this->findOrFail($id);
        $obj->delete();

        return response()->noContent();
    }
}