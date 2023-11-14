<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GPTFunction extends Model
{
    use HasFactory;

    protected $table = 'functions';

    protected $casts = ['parameters' => 'json'];
}
