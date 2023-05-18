<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Login;
use App\Http\Controllers\Controller;
use App\Models\GroupMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['store']]);
        $this->middleware('XssSanitization', ['only' => ['store', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->validator($request) === null) {
            return $this->create($request);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if ($request->hasFile('picture')) {
            $user_id = $user->id;
            $media = $request->file('picture');
            $media_name = $user_id . '-' . Carbon::now()->timestamp . '-' . $media->getClientOriginalName();
            $media->move(public_path('/media'), $media_name);

            $media_path = "/media/" . $media_name;
            $user->picture = $media_path;
            return $user->save();
        }

        $request->validate([
            'name' => ['required', 'regex:/^[\pL\s]+$/u', 'max:255'],
            'password' => ['string', 'min:8', 'confirmed'],
        ]);

        return $user->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        return $user->delete();
    }

    /**
     * track user's last activity
     */
    public function loggedInUsers()
    {
        return User::whereBetween('last_active_at', [now()->subMinutes(10), now()])->where('is_logged_in', true)->get();
    }

    /**
     * search users by name
     */
    public function search(Request $request)
    {
        return User::where('name', 'LIKE', "%$request->search%")->where('id', '!=', Auth::user()->id)->take(10)->get(['id', 'name', 'picture']);
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(Request $request)
    {
        $request->validate([
            'name' => ['required', 'regex:/^[\pL\s]+$/u', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(Request $request)
    {
        //create user
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'is_logged_in' => true
        ]);

        //add user to the general group
        GroupMember::create([
            'group_id' => 1,
            'user_id' => $user->id
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken(time())->plainTextToken
        ], 200);
    }
}
