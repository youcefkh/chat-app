<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth:sanctum');
       $this->middleware('XssSanitization', ['only' => ['store', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
}
