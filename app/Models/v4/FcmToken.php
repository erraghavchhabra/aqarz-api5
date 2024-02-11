<?php

namespace App\Models\v4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FcmToken extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'user_id', 'token', 'type'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];


}
