<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HasFactory;

class RoleRequest extends Model
{
    protected $fillable = [
        'user_id',
        'requested_role',
        'status',
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
