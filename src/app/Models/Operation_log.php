<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Operation_log extends Model
{
    //use HasFactory;

    protected $fillable = [
        'action',
        'table',
        'changes',
        'operator',
        'created_at'
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
