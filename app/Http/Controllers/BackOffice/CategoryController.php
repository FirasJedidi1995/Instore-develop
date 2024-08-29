<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $user = User::find(Auth::id());
            $method = $request->route()->getActionMethod();
            $roles = $user->getRoleNames()->toArray(); // Get the user's roles as an array

            // Define roles and method access logic
            $allowedRolesForIndexAndShow = ['provider-intern', 'provider-extern'];
            $allowedRolesForAllMethods = ['admin', 'superadmin'];

            if ($method === 'index' || $method === 'show') {
                if (array_intersect($roles, $allowedRolesForIndexAndShow) || array_intersect($roles, $allowedRolesForAllMethods)) {
                    return $next($request);
                }
            } else {
                if (array_intersect($roles, $allowedRolesForAllMethods)) {
                    return $next($request);
                }
            }

            // If access is denied
            abort(403, 'Unauthorized');
        });
    }
    public function index()
{
    $categories = Category::all();
    return response()->json(CategoryResource::collection($categories), 200);
}


    
    public function store(Request $request)
{
    $rules = ['name' => 'string|required'];
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $category = Category::create($request->all());

    return response()->json($category, 201);
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