<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware(['role:admin|superadmin']);
    }
    
    public function index()
    {
        $query = User::query();
        $query->orderBy('name', 'asc');

        $users = $query->get();

        return response()->json($users, 200);
    }

    

    public function show($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }
        return response()->json(User::find($id), 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(['message' => 'utilisateur introuvable'], 404);
        }

        $user->delete();

        return response()->json(['message'=>'delete successful'],200);//204
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'poste' => ['nullable', 'in:administrator,operator'],
            'image'=>'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => ['nullable', 'in:ACTIVE,INACTIVE,PENDING'],
            'acountLink'=> 'nullable|string',
            'street'=> 'nullable|string',
            'city'=> 'nullable|string',
            'post_code'=> ['nullable', 'regex:/^[0-9]{4}$/'],
            'CIN'=> ['nullable', 'regex:/^[0-9]{8}$/'],
            'TAXNumber'=> 'nullable|regex:/^[0-9]{8}$/',
            'companyName'=> 'nullable|string',
            'companyUnderConstruction'=> 'nullable|boolean',
            
        ]);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json(
                [
                    'message' => 'utilisateur introuvable',
                    "status" => "404"
                ]
            );
        }
        $user->update($request->only('name', 'email', 'phone', 'status','image','poste','acountLink','street','city','post_code','CIN','TAXNumber','companyName', 'companyUnderConstruction',));
        return response()->json([
            "message" => "Updated Successefully",
            "status" => 200,
        ]);
    }

 
    public function getUsersByRole($Role)
    {
        $userRole = User::role($Role)->get();
        return response()->json( $userRole);
    }

    public function filterUser(Request $request)
    {
        $query = User::query();

    // Filtrage par nom
    if ($request->has('name')) {
        $query->where('name', 'like', '%' . $request->input('name') . '%');
    }

    // Filtrage par e-mail
    if ($request->has('email')) {
        $query->where('email', $request->input('email'));
    }

    $users = $query->get();

    return response()->json($users, 200);
    }

    public function updateUserStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACTIVE,INACTIVE,PENDING',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->status = $request->input('status');
        $user->save();

        return response()->json(['message' => 'User status updated successfully'], 200);
    }

}