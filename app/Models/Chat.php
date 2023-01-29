<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['user_one', 'user_two'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public static function chat(int $sender_id, int $receiver_id)
    {
        $query = DB::select('select * from chats where (user_one = ? AND user_two = ?) OR (user_one = ? AND user_two = ?)', [$sender_id, $receiver_id, $receiver_id, $sender_id]);
        
        return $query ? $query[0] : null;
    }
}
