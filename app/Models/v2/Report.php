<?php

namespace App\Models\v2;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'report_type',
        'body',
        'user_id',
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
