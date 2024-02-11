<?php

namespace App\Models\v3;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ReportEstate extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'estate_id',

    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

}
