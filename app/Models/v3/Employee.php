<?php

namespace App\Models\v3;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
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
        'user_id',
        'emp_name',
        'emp_mobile',
        'country_code',


    ];



    protected $appends = ['user'  , 'status' , 'market_demands_count'];

    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];

    public function getMarketDemandsCountAttribute()
    {
        return $this->hasMany(EstateRequest::class, 'user_id', 'id')->count();
    }
    public function getUserAttribute()
    {

        $user = User::where('mobile', $this->emp_mobile)
            ->first();
        if ($user) {
            return $user;
        }

        return null;

    }
    public function user_information()
    {
        return $this->belongsTo(User::class, 'emp_mobile', 'mobile');
    }

    public function getStatusAttribute()
    {
        $user = User::where('mobile', $this->emp_mobile)
            ->first();
        if ($user) {
           if ($user->is_employee == 1){
               return 'pending';
           }elseif ($user->is_employee == 2) {
               return 'active';
           }else{
               return 'rejected';
           }
        }

        return 'pending';

    }



}
