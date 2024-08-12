<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ColorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin|superadmin']);
    }

    public function index()
    {
        $colors = Color::all();
        return response()->json($colors, 200); 
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:7'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $color = Color::create($request->only(['name', 'code']));
        return response()->json($color, 201); 
    }

    public function show($id)
    {
        $color = Color::find($id);

        if ($color) {
            return response()->json($color);
        } else {
            return response()->json(['message' => 'Couleur non trouvée'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $color = Color::find($id);

        if (!$color) {
            return response()->json(['error' => 'Couleur non trouvée'], 404);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:7'
        ];

        $validatedData = $request->validate($rules);

        $color->update($validatedData);

        return response()->json($color);
    }

    public function destroy($id)
    {
        $color = Color::find($id);

        if (!$color) {
            return response()->json(['error' => 'Couleur non trouvée'], 404);
        }

        $color->delete();

        return response()->json(['message' => 'Couleur supprimée avec succès']);
    }
}