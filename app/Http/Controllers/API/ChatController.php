<?php

namespace App\Http\Controllers\API;

use App\Models\Chat;
use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Events\WaveSent;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
        $chat = null;
        $user_id = Auth::user()->id;
        $message = [];

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
        }else {
            $message = [
                "content" => $request->message,
                "type" => "text"
            ];
        }

        //if the conv is private(chat)
        if ($request->friend_id) {
            $chat = Chat::chat(Auth::user()->id, $request->friend_id);

            // and it's the 1st message between the two users
            if (!$chat) {
                $chat = Chat::Create([
                    'user_one'    => $user_id,
                    'user_two'    => $request->friend_id
                ]);
            }
        }

        $messageCreated = Message::create([
            "message" => $message["content"],
            "type" => $message["type"],
            "user_id" => $user_id,
            "chat_id" => $chat ? $chat->id : null,
            "group_id" => !$chat ? $request->group_id : null,
        ]);

        //add message creating date
        $message['created_at'] = $messageCreated->created_at;

        $conversation = [
            "type" => $request->friend_id ? "private" : "group",
            "receiver_id" => $request->friend_id ? $request->friend_id : $request->group_id
        ];

        broadcast(new MessageSent($request->user(), $message, $conversation))->toOthers();

        return $messageCreated;
    }

    public function getMessages(string $type, int $receiver_id)
    {
        if ($type == "group") {
            return Message::where('group_id', $receiver_id)->with('user:id,name')->latest()->paginate(10);
        } else {
            $chat = Chat::chat(Auth::user()->id, $receiver_id);

            $result = $chat ? Message::where('chat_id', $chat->id)->with('user:id,name,email')->latest()->paginate(10) : null;

            return $result;
        }
    }

    public function wave(int $id, Request $request)
    {
        $user = User::where('id', $id)->first();
        broadcast(new WaveSent($user, $request->message))->toOthers();
    }
}
