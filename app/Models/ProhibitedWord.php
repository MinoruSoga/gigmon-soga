<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProhibitedWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'word'
    ];

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
}
