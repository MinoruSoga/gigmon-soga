<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_token',
        'conversation_system_id',
        'user_id',
        'role',
        'message',
        'prompt',
        'response',
        'function_id',
        'model',
        'source',
    ];
}
