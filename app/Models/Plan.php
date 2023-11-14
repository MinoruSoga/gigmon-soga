<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'max_users', 'max_prompts', 'knowledge_base_enabled',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
