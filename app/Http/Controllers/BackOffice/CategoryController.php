<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware(['role:admin|superadmin']);
    }
    public function index(){
        $categories=Category::all();
        return response($categories,200);
    }

    
    public function store(Request $request)
{
    $rules = ['name' => 'string|required'];
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $category = Category::create($request->all());

    return response($category, 201);
}

public function show($id){
    $category=Category::find($id);
    if(!$category){
        return response()->json(['error'=>'Category not found'],404);
    }
    return response()->json($category);
}

public function update(Request $request,$id){
    $category=Category::find($id);
    if(!$category){
        return response()->json(['error'=>'Category not found'],404);
    }
    $category->update($request->all());
    return response()->json('Category updated');
}

public function destroy($id){
    $category=Category::find($id);
    if(!$category){
        return response()->json(['error'=>'Category not found'],404);
    }
    $category->delete();
    return response()->json(['message' => 'Subcategory deleted successfully']);
    
}

    
}