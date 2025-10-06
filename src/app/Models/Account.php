<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Transaction;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'version',
        'created_at'
    ];

    //
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class);
    }
}
