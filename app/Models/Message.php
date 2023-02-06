<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'sender_id', 'parent_message_id', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'message_recipients');
    }

    public function parentMessage()
    {
        return $this->hasOne(Message::class, 'parent_message_id');
    }
}
