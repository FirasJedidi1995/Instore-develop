<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SizeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin|superadmin']);
    }

    public function index()
    {
        $sizes = Size::all();
        return response()->json($sizes, 200); 
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $size = Size::create($request->only(['name']));
        return response()->json($size, 201); 
    }

    public function show($id)
    {
        $size = Size::find($id);

        if ($size) {
            return response()->json($size);
        } else {
            return response()->json(['message' => 'Taille non trouvée'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['error' => 'Taille non trouvée'], 404);
        }

        $rules = [
            'name' => 'required|string|max:255'
           
        ];

        $validatedData = $request->validate($rules);

        $size->update($validatedData);

        return response()->json($size);
    }

    public function destroy($id)
    {
        $size = Size::find($id);

        if (!$size) {
            return response()->json(['error' => 'Taille non trouvée'], 404);
        }

        $size->delete();

        return response()->json(['message' => 'Taille supprimée avec succès']);
    }
}