<?php

namespace App\Http\Controllers\API;

use App\Events\UserSessionChange;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if(Hash::check($request->password, $user->password)){
            $user->update([
                'is_logged_in' => true,
            ]);
            event(new Login('', $user, ''));
            return response()->json([
                'token' => $user->createToken(time())->plainTextToken,
            ], 200);
        }
    }

    public function user()
    {
        return Auth::user();
    }

    public function logout(Request $request) {
        $request->user()->update([
            'is_logged_in' => false,
        ]);
        event(new Logout('', $request->user()));
        $request->user()->currentAccessToken()->delete();
    }
}
