<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipient extends Model
{
    use HasFactory;
    protected $table = 'message_recipients';

    protected $fillable = ['message_id', 'recipient_id', 'recipient_group_id', 'room_id', 'is_seen'];
}
