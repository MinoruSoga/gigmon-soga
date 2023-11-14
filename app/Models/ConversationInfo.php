<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_system_id',
        'conversation_token',
        'title',
        'is_visible',
        'function_id',
        'model'
    ];
}
