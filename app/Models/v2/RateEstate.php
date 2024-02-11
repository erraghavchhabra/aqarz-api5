<?php

namespace App\Models\v2;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RateEstate extends Model
{

    use SoftDeletes;

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
        'estate_id',
        'user_id',
        'provider_id',
        'rate',
        'note'


    ];
    protected $appends = ['user_name'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }




    public function getUserNameAttribute()
    {


        return @$this->user->name;
    }

    public function geProviderNameAttribute()
    {


        return @$this->provider->name;
    }


}
