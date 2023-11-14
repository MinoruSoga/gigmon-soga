<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'postal_code',
        'prefecture',
        'city',
        'address',
        'building',
        'phone_number',
        'plan_id',
        'docsbot_team_id',
        'docsbot_bot_id',
        'agency_code',
        'staff_code',
        'docsbot_api_key',
        'unsubscribed_at',
        'deleted_at',
        'paused_at',
        'accounting_email',
        'parent_company_id',
    ];

    protected $casts = [
        'docsbot_team_id' => 'encrypted',
        'docsbot_bot_id' => 'encrypted',
        'docsbot_api_key' => 'encrypted',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function prompts()
    {
        return $this->hasMany(Prompt::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function histories()
    {
        return $this->hasMany(CompanyHistory::class);
    }

    public function prohibited_words()
    {
        return $this->hasMany(ProhibitedWord::class);
    }

    public function getWordsCount()
    {
        $count = 0;
        foreach($this->employees as $employee) {
            $count +=
                $employee->user->conversations()->sum(DB::raw('CHAR_LENGTH(prompt)')) +
                $employee->user->conversations()->sum(DB::raw('CHAR_LENGTH(message)'));
        }
        return $count;
    }

    public function getThisMonthsCharacterCount($model){
        $count = 0;
        $month = date('m');
        foreach($this->employees as $employee) {
    
            $promptSum = $employee->user->conversations()->where('model', 'like', '%' . $model . '%')->whereMonth('created_at', $month)->sum(DB::raw('CHAR_LENGTH(prompt)'));
            $messageSum = $employee->user->conversations()->where('model', 'like', '%' . $model . '%')->whereMonth('created_at', $month)->sum(DB::raw('CHAR_LENGTH(message)'));
    
            $count += $promptSum + $messageSum;
        }

        return ceil($count / 1000);
    }

    public function ipAddresses()
    {
        return $this->hasMany(CompanyIpAddress::class);
    }
}
