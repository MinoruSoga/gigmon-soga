<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'display_from', 'display_to', 'priority_flag'];

    protected $dates = ['display_from', 'display_to'];

}
