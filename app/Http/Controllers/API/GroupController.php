<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('XssSanitization', ['only' => ['store', 'update', 'search']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Auth::user()->id;

        return Group::join('group_members AS members', 'groups.id', '=', 'members.group_id')
            ->where('members.user_id', '=', $user_id)
            ->get(['groups.*']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $values = ['name' => $request->name];
        
        if ($request->hasFile('picture')) {
            $media = $request->file('picture');
            $media_name = $request->name . '-group-' . Carbon::now()->timestamp . '-' . $media->getClientOriginalName();
            $media->move(public_path('/media'), $media_name);

            $group_picture = "/media/" . $media_name;
            $values['picture'] = $group_picture;
        }

        $data = Group::create($values);

        $group = Group::find($data->id); //to get the default picture in case it wasn't assigned any

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $request->user()->id
        ]);

        Room::create([
            'group_id' => $group->id
        ]);

        return response()->json([
            "group" => $group,
            "message" => $group->name . " group was created by " . $request->user()->name
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Group::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /* Group members */

    public function showMembers(int $group_id)
    {
        return Group::find($group_id)->users()->get(['users.id', 'users.name', 'users.email']);
    }

    public function deleteMember(int $user_id)
    {
        return GroupMember::where('user_id', $user_id)->delete();
    }

    public function isMember(int $group_id, int $user_id)
    {
        $result = GroupMember::where('group_id', $group_id)->where('user_id', $user_id)->exists();

        return response()->json([
            'isMember' => $result,
        ], 200);
    }

    /**
     * search members by name to add to a group
     */
    public function search(Request $request, $group_id)
    {
        //fetch users that don't belong to $group_id
        $result1 = User::where('users.name', 'LIKE', "%$request->search%")->whereHas('groups', function ($query) use ($group_id) {
            $query->where('groups.id', '!=', $group_id);
        })->get();

        //fetch users that don't belong to any group
        $result2 = User::where('users.name', 'LIKE', "%$request->search%")->whereDoesntHave('groups')->get();

        return $result1->merge($result2);
    }

    /**
     * add new members to a group
     */
    public function addMembers(Request $request, $group_id)
    {
        $request->validate([
            'users' => ['required']
        ]);

        foreach ($request->users as $ids) {
            GroupMember::create([
                'user_id' => $ids,
                'group_id' => $group_id
            ]);
        }

        return response()->json([
            'message' => count($request->users) . ' members have been added',
        ], 201);
    }
}
