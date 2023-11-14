<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInput extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'input_length',
        'response_length',
        'conversation_system_id',
    ];
}
