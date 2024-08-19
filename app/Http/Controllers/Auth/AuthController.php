<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use JWTAuth;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Rule;
use App\Mail\forgetPasswordCode;
use App\Models\verification_code;
use Tymon\JWTAuth\Exceptions\TokenInvalidException as JWTTokenInvalidException;
class AuthController extends Controller
{

 

    
    public  function register(Request $request){
        $rules = [
            'name'=>'required',
            'phone'=>['required', 'regex:/^[0-9]{8}$/'],
            'email'=>'required|email|unique:users,email|',
            'password'=>'required|min:6|max:24|',
            'role'=>'required |in:client,provider-extern,provider-intern', // hedhi temchi salla7tha
            'image'=>'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'acountLink'=> 'nullable|string',
            'street'=> 'nullable|string',
            'city'=> 'nullable|string',
            'post_code'=> ['nullable', 'regex:/^[0-9]{4}$/'],
            'CIN'=> ['nullable', 'regex:/^[0-9]{8}$/'],
            'TAXNumber'=> 'nullable|string',
            'companyName'=> 'nullable|string',
            'companyUnderConstruction'=> 'nullable|boolean',
        ];
        if (User::where('email', $request->input('email'))->exists()) {
            return response()->json(['error' => 'Email already exists'], Response::HTTP_CONFLICT);
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('users'), $imageName);
        }

        $userData = $validator->validated();
        $userData['image'] = asset('users') . '/' . $imageName; // hedhi temchi salla7tha
        $user = User::create(array_merge(
            $userData,
            ['password' => bcrypt($request->password)]
        ));

   
        $user->assignRole($request->role);
        
        return response()->json('User Created');
    }
   
   

    public function login(Request $request)
    {
        $creds = $request->only(['email','password']);
        if (!$token=auth()->attempt($creds)){
            return response()->json([
                'success'=>false,
                'message'=>'information incorrecte'
            ],Response::HTTP_UNAUTHORIZED);
        }
        $user = User::where('email', $request->email)->first();
       

        if ($user->status != 'ACTIVE') {
                return response()->json([
                    "message" => 'Your account is not active',
                    "status" => 401
                ]);
            }

            $accessToken = $token;
            $refreshToken = FacadesJWTAuth::claims(['exp' => now()->addWeeks(2)->timestamp])->fromUser(auth()->user());
            $expiresIn = config('jwt.ttl') * 60;
            return response()->json([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'user'=>Auth::user(),
                'expires_in' => $expiresIn
            ]);
    }

    public function logout(Request $request){
        try {
            FacadesJWTAuth::invalidate(FacadesJWTAuth::parseToken($request->token));
            return response()->json([
                "success"=>true,
                "message"=>"logout success"
            ]);
        }catch (Exception $exception){
            return response()->json([
                "success"=>false,
                "message"=>"".$exception
            ]);
        }
    }
    public function AuthenticatedUser()
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }
   

    function randomcode($length)
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $alphabetLength = strlen($alphabet);
    $pass = '';

    for ($i = 0; $i < $length; $i++) {
        $pass .= $alphabet[random_int(0, $alphabetLength - 1)];
    }

    return $pass;
}

public function forgetPassWord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'status' => 400
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'errors' => ['email' => 'The selected email is invalid.'],
                'status' => 400
            ]);
        }

        $code = $this->randomCode(4);

        
        verification_code::where(['email' => $request->email, 'status' => 'pending'])
            ->update(['status' => 'expired']);

        try {
            $data = [
                'email' => $request->email,
                'name' => $user->name,
                'code' => $code,
                'subject' => 'Forget Password',
            ];

            Mail::to($data['email'])->send(new ForgetPasswordCode($data));

            $verificationCode = new verification_code();
            $verificationCode->email = $request->email;
            $verificationCode->code = $code;
            $verificationCode->status = 'pending';
            $verificationCode->save();

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'new_code' => $verificationCode,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send the email. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


//forget password section >>>
    // generate random code 
    // function randomcode($_length)
    // {
    //     $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    //     $pass = array();
    //     $alphaLength = strlen($alphabet) - 1;
    //     for ($i = 0; $i < $_length; $i++) {
    //         $n = rand(0, $alphaLength);
    //         $pass[] = $alphabet[$n];
    //     }
    //     return implode($pass); //turn the array into a string
    // }

    // send random code to virif mail
    // public function forgetPassWord(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'email' => [
    //             'required',
    //             'email',
    //             function ($attribute, $value, $fail) {
    //                 if (!User::where('email', $value)->exists() ) {
    //                     $fail('The selected email is invalid.');
    //                 }
    //             },
    //         ],
    //     ]);


    //     if ($validator->fails()) {
    //         return response()->json([
    //             $validator->errors(),
    //             "status" => 400
    //         ]);
    //     }

    //     $user = User::where('email', $request->email)->first();
    //     $code = self::randomcode(4);
    //     // make old codes expired
    //     verification_code::where(["email" => $request->email, "status" => "pending"])->update(["status" => "expired"]);
    //     if ($user) {
    //         $data = [
    //             "email" => $request->email,
    //             "name" => $user->name,
    //             "code" => $code,
    //             "subject" => "forget password",
    //         ];
    //         Mail::to($data["email"])->send(new forgetPasswordCode($data));
    //         $verifTable = new verification_code();
    //         $verifTable->email = $request->email;
    //         $verifTable->code = $code;
    //         $verifTable->status = "pending";
    //         $verifTable->save();

    //         return response()->json([
    //             'status' => 'success',
    //             'data' => $data,
    //             'new code' => $verifTable,
    //         ]);
    //     }
    // } 
    public function changePassword(Request $request) // en marche
    {
        $user = User::find(auth()->user()->id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'newPassword'=>'required|string|min:6|confirmed',
        ]);
        
        //if($user->password!=$request->password)

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'status' => 400
            ]);
        }

        
        // Chiffrer le nouveau mot de passe
        $password_hashed = Hash::make($request->newPassword);

        // Mettre à jour le mot de passe dans la base de données
        $user->password = $password_hashed;
        $user->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    public function verifCode(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => [
            'required',
            'email',
            function ($attribute, $value, $fail) {
                if (!User::where('email', $value)->exists()) {
                    $fail('The selected email is invalid.');
                }
            },
        ],
        'code' => [
            'required',
            'string',
            'min:4',
            'max:4',
            'exists:verification_codes,code'
        ],
        'password' => 'required|string|min:6'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
            'status' => 400
        ]);
    }

    $code = $request->code;
    $dataBaseCode = verification_code::where([
        'email' => $request->email,
        'status' => 'pending',
        'code' => $code
    ])->orderBy('created_at', 'desc')->first();

    if ($dataBaseCode) {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            
            $password_hashed = Hash::make($request->password);

            
            $user->password = $password_hashed;
            $user->save();

           
            $dataBaseCode->status = 'used';
            $dataBaseCode->save();

            return response()->json([
                'message' => 'Verification success and password updated',
                'status' => 200,
                'code' => $dataBaseCode
            ]);
        } else {
            return response()->json([
                'message' => 'User not found',
                'status' => 404
            ]);
        }
    } else {
        return response()->json([
            'message' => 'Invalid verification code',
            'status' => 406 // Not acceptable == 406
        ]);
    }
}

    // public function verifCode(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => [
    //             'required',
    //             'email',
    //             function ($attribute, $value, $fail) {
    //                 if (!User::where('email', $value)->exists() ) {
    //                     $fail('The selected email is invalid.');
    //                 }
    //             },
    //         ],
    //         'code' => [
    //             "required",
    //             "string",
    //             "min:4",
    //             "max:4",
    //             "exists:verification_codes,code"
    //         ],
    //        
    //     ]);


    //     if ($validator->fails()) {
    //         return response()->json([
    //             $validator->errors(),
    //             "status" => 400
    //         ]);
    //     }
    //     $code = $request->code;
    //     $dataBaseCode = verification_code::where(["email" => $request->email, "status" => "pending", "code" => $code])->orderBy('created_at', 'desc')->first();
   
    //     if ($dataBaseCode) {
            
    
    //         $dataBaseCode->status = "used";
    //         $dataBaseCode->save();
    
            
    //         return response()->json([
    //             'message' => "verification success",
    //             "status" => 200,
    //             "code" => $dataBaseCode
    //         ]);
    //     } else {
    //         return response()->json([
    //             "message" => "invalide verification code",
    //             "status" =>  406 // not acceptable == 406
    //         ]);
    //     }
    // }
        /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function refresh()
    // {
    //     return $this->respondWithToken(auth()->refresh());
    // }
    
    public function updateUserPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found', 'status' => 404], 404);
        }

        $user->password = Hash::make($request->input('password'));
        $user->update();

        return response()->json(
            [
                'message' => 'User password updated successfully',
                'status' => 200
            ],
            200
        );
    }

    // public function update(Request $request, $id)
    // {
    //     //valdiate
    //     // $rules = [];
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string',
    //         'email' =>  [
    //             'required',
    //             'string',
    //             Rule::unique('users')->ignore($id),
    //             'email'
    //         ],
    //         'phone' => ['required', 'regex:/^[0-9]{8}$/'],
    //         'birthday' => ['required', 'date'],
    //         'sexe' => ['required', 'in:male,female'],
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             $validator->errors(),
    //             "status" => 400
    //         ]);
    //     }
    //     $user = User::find($id);
    //     if (is_null($user)) {
    //         return response()->json(
    //             [
    //                 'message' => 'utilisateur introuvable',
    //                 "status" => "404"
    //             ]
    //         );
    //     }
    //     $user->birthday = Carbon::createFromFormat('m/d/Y', $request->birthday)->format('Y-m-d');
        
    //     $user->update($request->only('name', 'email', 'phone', 'birthday', 'sexe', 'status'));
    //     return response()->json([
    //         "message" => "Updated Successefully",
    //         "status" => 200,
    //     ]);
    // }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */


     public function updateSelfData(Request $request)
{
    
    $user = Auth::user();
    $user = User::find(Auth::id());
    
    $rules = [
        'image' => 'nullable|image|max:2048',
        'name' => 'nullable|string|max:255',
        'email' => [
            'nullable',
            'string',
            'email',
            Rule::unique('users')->ignore($user->id),
        ],
        'phone' => ['nullable', 'regex:/^[0-9]{8}$/'],
        'acountLink' => 'nullable|string',
        'street' => 'nullable|string',
        'city' => 'nullable|string',
        'post_code' => ['nullable', 'regex:/^[0-9]{4}$/'],
        'CIN' => ['nullable', 'regex:/^[0-9]{8}$/'],
        'TAXNumber' => 'nullable|string',
        'companyName' => 'nullable|string',
        'companyUnderConstruction' => 'nullable|boolean',
    ];

    
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
            'status' => 400
        ]);
    }

   
    $userData = $validator->validated();
    
    
    if ($request->hasFile('image')) {
        
        if ($user->image) {
            $oldImagePath = public_path('users/') . basename($user->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

       
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('users'), $imageName);
        $userData['image'] = asset('users/' . $imageName);
    }

   
    $user->update($userData);

    
    return response()->json([
        'message' => 'User data updated successfully',
        'status' => 200
    ]);
}

   
     
     public function refresh()
     {
         $token = FacadesJWTAuth::getToken();
         if (!$token) {
             return response()->json(['error' => 'Token not provided'], 401);
         }
     
         try {
             $refreshedToken = FacadesJWTAuth::refresh($token);
         } catch (JWTTokenInvalidException $e) {
             return response()->json(['error' => 'Invalid token'], 401);
         }
     
         return response()->json(['access_token' => $refreshedToken]);
     }
     

}