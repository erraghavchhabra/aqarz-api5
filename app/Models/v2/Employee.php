<?php

namespace App\Models\v2;

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


    protected $appends = ['user'];

    protected $hidden = ['created_at', 'deleted_at', 'updated_at'];

    public function getUserAttribute()
    {

        $user = User::where('mobile', $this->emp_mobile)
            ->first();
        if ($user) {
            return $user;
        }

        return null;

    }

}
