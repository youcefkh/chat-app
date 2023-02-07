<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use PhpParser\Node\Expr\Cast\String_;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $message;
    public $conversation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, array $message, array $conversation)
    {
        $this->user = $user;
        $this->message = $message;
        $this->conversation = $conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channel_name = $this->conversation['type'] == 'private' ? max($this->user->id, $this->conversation['recipient_id']) . '-' . min($this->user->id, $this->conversation['recipient_id']) : $this->conversation['recipient_id']; //get something like 17-36 if the conv is private
        return new PrivateChannel('chat.'. $this->conversation['type'] .'.'. $channel_name);
    }
}
