<?php

namespace App\Http\Controllers\API;

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
            $media = $request->file('media');
            $media_name = $user_id . '-' . Carbon::now()->timestamp . '-' . $media->getClientOriginalName();
            $media->move(public_path('/media'), $media_name);

            $media_path = "/media/" . $media_name;

            $message = [
                "content" => $media_path,
                "type" => $request->type
            ];
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
        //if the conv is a group
        if ($request->group_id) {
            $members = GroupMember::where('group_id', $request->group_id)->where('user_id', '!=', $user_id)->get();

            foreach ($members as $member) {
                Recipient::create([
                    'message_id' => $messageCreated->id,
                    'recipient_id' => $member->user_id,
                    'recipient_group_id' => $member->group_id
                ]);
            }
        } else {
            Recipient::create([
                'message_id' => $messageCreated->id,
                'recipient_id' => $request->friend_id
            ]);
        }

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
                ->select('messages.*', 'users.name AS user_name')
                ->where('recipients.recipient_group_id', $recipient_id)
                ->groupBy('messages.id')
                ->latest()->paginate(10);

        } else {
            return Message::join('message_recipients AS recipients', 'messages.id', '=', 'recipients.message_id')
                ->join('users', 'users.id', '=', 'messages.sender_id')
                ->select('messages.*', 'users.name AS user_name', 'recipients.recipient_id AS recipient_id')
                ->Where('recipients.recipient_group_id', null)
                ->where(
                    function ($query) use ($recipient_id){
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
                ->latest()->paginate(10);
        }
    }

    public function wave(int $id, Request $request)
    {
        $user = User::where('id', $id)->first();
        broadcast(new WaveSent($user, $request->message))->toOthers();
    }
}
