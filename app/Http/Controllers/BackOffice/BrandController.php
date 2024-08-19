<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller


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
        //dd(auth()->user()->roles);

        $brands = Brand::with('categories')->get();
        return response()->json($brands);
    }

    public function store(Request $request){
        $rules = [
            'name' => 'required|string',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'image' => 'required|image|max:2048' // Validation de l'image
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('brands'), $imageName);
            
        }

        $brand = Brand::create([
            'name' => $request->name,
            'image' => asset('brands') .'/'. $imageName
        ]);

        if ($request->has('category_ids')) {
            $categoryIds = is_array($request->category_ids) ? $request->category_ids : [$request->category_ids];
            $brand->categories()->attach($categoryIds);
        }

        return response()->json($brand, 201);
    }
  
    
    public function show($id){
        $brand=Brand::with('categories')->find($id);
        if(!$brand){
            return response()->json(['error'=>'Brand not found'],404);
        }
        return response()->json($brand);
    }

    public function update(Request $request, $id){
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['error' => 'Brand not found'], 404);
        }
    
        $rules = [
            'name' => 'sometimes|required|string',
            'category_ids' => 'sometimes|array|min:1',
            'category_ids.*' => 'integer|exists:categories,id',
            'image' => 'nullable|image|max:2048'
        ];
    
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
    
        
        if ($request->filled('name')) {
            $brand->name = $request->name;
        }
    
       
        if ($request->has('category_ids')) {
            $categoryIds = is_array($request->category_ids) ? $request->category_ids : [$request->category_ids];
            $brand->categories()->sync($categoryIds);
        }
    
        
        if ($request->hasFile('image')) {
            
            if ($brand->image) {
                $oldImagePath = public_path(parse_url($brand->image, PHP_URL_PATH));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
    
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('brands'), $imageName);
    
            
            $brand->image = asset('brands') . '/' . $imageName;
        }
    
        $brand->save();
    
        return response()->json($brand);
    }
    
    

   

    public function destroy($id){
        $brand=Brand::find($id);
        if(!$brand){
            return response()->json(['error'=>'Brand not found'],404);
        }
        $brand->categories()->detach();
        $brand->delete();
        return response()->json(['message' => 'Brand deleted successfully']);
    }
}