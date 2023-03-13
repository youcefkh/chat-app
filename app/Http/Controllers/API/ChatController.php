<?php

namespace App\Http\Controllers\API;

use App\Events\MessageRecieved;
use Carbon\Carbon;
use App\Models\Chat;
use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Events\WaveSent;
use App\Models\Recipient;
use App\Models\Recipients;
use App\Events\MessageSent;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('XssSanitization', ['only' => ['storeMessage']]);
    }

    public function storeMessage(Request $request)
    {
        $user_id = Auth::user()->id;
        $message = []; //message details to broadcast

        //if the message is a media file
        if ($request->hasFile('media')) {
            $message = $this->storeMediaFile($request);
        } else {
            $message = [
                "content" => $request->message,
                "type" => $request->type
            ];
        }

        $messageCreated = Message::create([
            "message" => $message["content"],
            "type" => $message["type"],
            "sender_id" => $user_id,
        ]);

        /* link the message to every recipient */
        $this->sendToRecipients($request, $messageCreated->id);

        //add message creating date
        $message['created_at'] = $messageCreated->created_at;

        $conversation = [
            "type" => $request->friend_id ? "private" : "group",
            "recipient_id" => $request->friend_id ? $request->friend_id : $request->group_id
        ];

        broadcast(new MessageSent($request->user(), $message, $conversation))->toOthers();

        return $messageCreated;
    }

    public function getMessages(string $type, int $recipient_id)
    {
        if ($type == "group") {
            return Message::join('message_recipients AS recipients', 'messages.id', '=', 'recipients.message_id')
                ->join('users', 'users.id', '=', 'messages.sender_id')
                ->select('messages.*', 'users.name AS user_name', 'users.picture as user_pic')
                ->where('recipients.recipient_group_id', $recipient_id)
                ->groupBy('messages.id')
                ->latest()->paginate(15);
        } else {
            return Message::join('message_recipients AS recipients', 'messages.id', '=', 'recipients.message_id')
                ->join('users', 'users.id', '=', 'messages.sender_id')
                ->select('messages.*', 'users.name AS user_name', 'users.picture as user_pic', 'recipients.recipient_id AS recipient_id')
                ->Where('recipients.recipient_group_id', null)
                ->where(
                    function ($query) use ($recipient_id) {
                        return $query
                            ->where([
                                ['recipients.recipient_id', $recipient_id],
                                ['messages.sender_id', Auth::user()->id],
                            ])
                            ->OrWhere([
                                ['recipients.recipient_id', Auth::user()->id],
                                ['messages.sender_id', $recipient_id],
                            ]);
                    }
                )
                ->latest()->paginate(15);
        }
    }

    public function wave(int $id, Request $request)
    {
        $user = User::where('id', $id)->first();
        broadcast(new WaveSent($user, $request->message))->toOthers();
    }

    public function messagesHistory()
    {
        $user_id = Auth::user()->id;
        //get latest messages id from each group of rooms
        $message_ids_collection = DB::table('messages')
            ->join('message_recipients AS recipients', 'messages.id', '=', 'recipients.message_id')
            ->join('users', 'users.id', '=', 'messages.sender_id')
            ->join('rooms', 'rooms.id', '=', 'recipients.room_id')
            ->leftJoin('group_members AS members', 'members.group_id', 'rooms.group_id')
            ->selectRaw('Max(messages.id) as message_id, rooms.id AS room_id')
            ->where('rooms.user1_id', $user_id)
            ->orWhere('rooms.user2_id', $user_id)
            ->orWhere('members.user_id', $user_id)
            ->groupBy('recipients.room_id')
            ->get();

        $message_ids_array = $message_ids_collection->pluck('message_id'); //from collection to array of ids

        //get records from private convs
        $private_messages = Message::join('message_recipients AS recipients', 'messages.id', '=', 'recipients.message_id')
            ->join('rooms', 'rooms.id', '=', 'recipients.room_id')
            ->join('users AS u1', 'u1.id', '=', 'rooms.user1_id')
            ->join('users AS u2', 'u2.id', '=', 'rooms.user2_id')
            ->select('messages.*', 'u1.name AS u1_name', 'u1.id AS u1_id', 'u1.picture AS u1_pic', 'u2.name AS u2_name', 'u2.id AS u2_id', 'u2.picture AS u2_pic', 'rooms.id AS room_id')
            ->whereIn('messages.id', $message_ids_array)
            ->get();

        //get records from groups
        $group_messages = Message::join('message_recipients AS recipients', 'messages.id', '=', 'recipients.message_id')
            ->join('users', 'users.id', '=', 'messages.sender_id')
            ->join('rooms', 'rooms.id', '=', 'recipients.room_id')
            ->join('groups', 'groups.id', '=', 'rooms.group_id')
            ->select('messages.*', 'groups.name AS group_name', 'groups.id AS group_id', 'groups.picture AS group_pic', 'users.name AS sender_name', 'rooms.id AS room_id')
            ->whereIn('messages.id', $message_ids_array)
            ->distinct('messages.id')
            ->get();

        $conversations = $group_messages->merge($private_messages)->sortByDesc('created_at')->values()->all();

        //add count of unseen messages to each record
        foreach ($conversations as $conv) {
            $unseen_messages = Message::join('message_recipients AS recipients', 'messages.id', '=', 'recipients.message_id')
                ->select(DB::raw('count(*) as count'), 'recipients.room_id')
                ->where('is_seen', 0)
                ->where('recipients.recipient_id', $user_id)
                ->where('recipients.room_id', $conv->room_id)
                ->first();

            $conv->unseen_messages = $unseen_messages ? $unseen_messages->count : 0;
        }

        return $conversations;
    }

    private function storeMediaFile(Request $request)
    {
        $user_id = Auth::user()->id;
        $media = $request->file('media');
        $media_name = $user_id . '-' . Carbon::now()->timestamp . '-' . $media->getClientOriginalName();
        $media->move(public_path('/media'), $media_name);

        $media_path = "/media/" . $media_name;

        return [
            "content" => $media_path,
            "type" => $request->type
        ];
    }

    private function sendToRecipients(Request $request, $message_id)
    {
        $user_id = Auth::user()->id;
        //if the conv is a group
        if ($request->group_id) {
            $members = GroupMember::where('group_id', $request->group_id)->where('user_id', '!=', $user_id)->get();
            $room = Room::where('group_id', $request->group_id)->first();

            foreach ($members as $member) {
                Recipient::create([
                    'message_id' => $message_id,
                    'recipient_id' => $member->user_id,
                    'recipient_group_id' => $member->group_id,
                    'room_id' => $room->id
                ]);

                broadcast(new MessageRecieved($member->user_id))->toOthers();
            }
        } else {
            /* check if room exists */
            $room = Room::where(
                function ($query) use ($user_id, $request) {
                    return $query
                        ->where([
                            ['user1_id', $user_id],
                            ['user2_id', $request->friend_id]
                        ])
                        ->OrWhere([
                            ['user1_id', $request->friend_id],
                            ['user2_id', $user_id]
                        ]);
                }
            )->first();
            if ($room === null) {
                $room = Room::create([
                    'user1_id' => $user_id,
                    'user2_id' => $request->friend_id
                ]);
            }

            Recipient::create([
                'message_id' => $message_id,
                'recipient_id' => $request->friend_id,
                'room_id' => $room->id
            ]);

            broadcast(new MessageRecieved($request->friend_id))->toOthers();
        }
    }

    public function seeMessages(Request $request)
    {
        $room = null;
        $user_id = Auth::user()->id;

        if ($request->type == 'group') {
            $room = Room::where('group_id', $request->id)->first();
        } else {
            $room = Room::where(
                function ($query) use ($user_id, $request) {
                    return $query
                        ->where([
                            ['user1_id', $user_id],
                            ['user2_id', $request->id]
                        ])
                        ->OrWhere([
                            ['user1_id', $request->id],
                            ['user2_id', $user_id]
                        ]);
                }
            )->first();
        }

        return Recipient::where('room_id', $room->id)->where('recipient_id', Auth::user()->id)
                ->update([
                    "is_seen" => true
                ]);
    }
}
