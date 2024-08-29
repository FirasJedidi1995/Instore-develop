<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubcategoryController extends Controller
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

            if ($method === 'index' || $method === 'show' || $method= 'filterSubcategory') {
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
        $subcategories = SubCategory::all();
        return response()->json($subcategories); 
    }   
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'category_id' => 'required|integer|exists:categories,id',
           
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
      
        $subcategory=SubCategory::create($request->all());
       
        
        return response()->json($subcategory,201);
    }
    public function show($id)
    {
        $subcategory = SubCategory::find($id);
        if(!$subcategory){
            return response()->json(['error'=>'subcategory not found'],404);
        }
        
        return response()->json($subcategory);
    }
    public function update(Request $request, $id)
    {
       $subcategory = SubCategory::find($id);
       if(!$subcategory){
        return response()->json(['error'=>'subcategory not found'],404);
       }
       
       
       $rules=['name'=>'string|required','category_id'=>'integer|required|exists:categories,id'];
       $validator=Validator::make($request->all(),$rules);
       if($validator->fails()){
        return response()->json(['errors'=>$validator->errors()],400);
       }
       $subcategory->update($request->all());
       return response()->json($subcategory);
    }
    public function destroy($id)
    {
        $subcategory = SubCategory::find($id);
        if(!$subcategory){
            return response()->json(['error'=>'Subcategory not found'],404);
        }
        $subcategory->delete();
        return response()->json(['message'=>'Subcategory deleted successfully']);
    }
    

    // public function filterSubcategory(Request $request)
    // {
    //     // Récupération du paramètre de catégorie
    //     $category = $request->input('category');
        
        
    //     if (!$category) {
    //         return response()->json(['error' => 'Le paramètre de catégorie est obligatoire.'], 400);
    //     }
    
    //     // Recherche des produits en fonction de la catégorie
    //     //$subcategories = SubCategory::where('type', $category)->get();
    //     $subcategories=SubCategory::where($category->subCategories());
    //     return response()->json($subcategories);
    // }


//     public function filterSubcategory(Request $request)
// {
//     // Récupération du paramètre de catégorie
//     $categoryName = $request->input('category');
    
//     // Vérifier si le paramètre de catégorie est fourni
//     if (!$categoryName) {
//         return response()->json(['error' => 'Le paramètre de catégorie est obligatoire.'], 400);
//     }

//     // Filtrer les sous-catégories par nom de catégorie
//     $subcategories = SubCategory::whereHas('category', function ($query) use ($categoryName) {
//         $query->where('name', 'like', '%' . $categoryName . '%');
//     })->get();

//     return response()->json($subcategories);
// }


public function filterSubcategory(Request $request)
{
    // Récupération du paramètre de catégorie
    $category = $request->input('category');
    
    if (!$category) {
        return response()->json(['error' => 'Le paramètre de catégorie est obligatoire.'], 400);
    }

    // Recherche des sous-catégories en fonction de la catégorie
    $subcategories = SubCategory::whereHas('category', function ($query) use ($category) {
        $query->where('name', 'like', '%' . $category . '%');
    })->get();

    return response()->json($subcategories);
}

   
    
}