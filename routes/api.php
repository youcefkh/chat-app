<?php

use Pusher\Pusher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\GroupController;
use App\Http\Controllers\API\UserController;
use App\Models\Chat;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::post('login', [AuthController::class, 'login']);

Route::get('auth-user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('user/search', [UserController::class, 'search']);
Route::apiResource('user', UserController::class);

Route::get('track-activity', function (){return true;})->middleware('auth:sanctum');
Route::get('logged-in-users', [UserController::class, 'loggedInUsers'])->middleware('auth:sanctum');

Route::post('chat/message', [ChatController::class, 'storeMessage']);
Route::get('chat/messages/{type}/{id}', [ChatController::class, 'getMessages']);

Route::post('chat/wave/{id}', [ChatController::class, 'wave']);

Route::apiResource('group', GroupController::class);
Route::get('group-members/{group_id}', [GroupController::class, 'showMembers']);
Route::delete('group-members/{user_id}', [GroupController::class, 'deleteMember']);
Route::get('group-members/search/{group_id}', [GroupController::class, 'search'])->name('search');
Route::get('group-members/{group_id}/{user_id}', [GroupController::class, 'isMember'])->name('isMember');
Route::post('group-members/{group_id}', [GroupController::class, 'addMembers'])->name('addMembers');
