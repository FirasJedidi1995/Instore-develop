<?php

namespace App\Http\Controllers\BackOffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendMessageAdminJob;
use App\Jobs\SendMessageProviderJob;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::all();
        return response()->json([
            'message' => 'List messages !',
            "status" => Response::HTTP_OK,
            "data" =>  MessageResource::collection($messages)
        ]);
       ; 
    }   
    public function getMessagesByProvider(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $messages = Message::where('receiver_id', $request->user_id)->get();
        return response()->json([
            'message' => 'List messages !',
            "status" => Response::HTTP_OK,
            "data" =>  MessageResource::collection($messages)
        ]);
       ; 
    }  
    public function getMessagesByAdmin()
    {
        $user = Auth::user();
        $messages = Message::where('receiver_id', $user->id)->get();
        return response()->json([
            'message' => 'List messages !',
            "status" => Response::HTTP_OK,
            "data" =>  MessageResource::collection($messages)
        ]);
       ; 
    }  
    public function getContacts()
    {
        $providers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['provider-intern','provider-extern']);
        })->get();        
        return response()->json([
            'message' => 'List Contacts !',
            "status" => Response::HTTP_OK,
            "data" =>  UserResource::collection($providers)
        ]);
       ; 
    }   
    
    public function sendAdminMessage(Request $request)
    {
        $rules = [
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                $validator->errors(),
                "status" => 400
            ]);
        }
        $messages = new Message();
        $messages->message  = $request->message;
        $messages->sender_id  = Auth::user()->id;
        $messages->receiver_id = $request->user_id;
        $messages->sender_type = 'ADMIN';
       
        $messages->save();
        $provider = User::find($request->user_id);
        dispatch(new SendMessageAdminJob($messages, $provider));
        return response()->json([
            'message' => 'Message created!',
            "status" => Response::HTTP_CREATED,
            "data" => new MessageResource($messages)
        ]);
    }
    public function show($id)
    {
        $messages = Message::find($id);
        return response()->json($messages);
    }
    public function update(Request $request, $id)
    {
       $messages = Message::find($id);
       $messages->update($request->all());
       return response()->json('Message updated');
    }
    public function destroy($id)
    {
        $messages = Message::find($id);
        $messages->delete();
        return response()->json('Message deleted!');
    }
}
