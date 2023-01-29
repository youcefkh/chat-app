<?php

use App\Models\Chat;
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
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notifications', function ($user) {
    return $user !== null;
});

Broadcast::channel('users', function ($user) {
    return $user !== null;
});

Broadcast::channel('chat.{type}.{id}', function ($user, $type, $id) {
    return $user->id !== null;
});

Broadcast::channel('chat.wave.{id}', function ($user, $id) {
    return $user->id == $id;
});