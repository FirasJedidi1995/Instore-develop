<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return  $user->id === $id;
});
Broadcast::channel('.admin.{id}', function ($id) {
    if(Auth::check()){
        return $id;
    }
});
Broadcast::channel('.provider.{id}', function ($id) {
    if(Auth::check()){
        return $id;
    }
});

