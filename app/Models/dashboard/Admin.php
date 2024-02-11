<?php

namespace App\Models\dashboard;

use App\Models\v1\City;
use App\Models\v1\MemberType;
use App\Models\v1\Neighborhood;
use App\Models\v1\ServiceType;
use App\Models\v3\AdminPermission;
use App\Models\v3\Permission;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class Admin extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'country_code',
        'active_payment_link',
        'role',
        'status',
        'password',
        'api_token',
        'remember_token',
        'confirmation_password_code'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    protected $with = ['permission'];
    protected $appends = ['permission_array','permission_name_array'];

    public function sendOtp($phone, $code)
    {

        $unifonicMessage = new UnifonicMessage();
        $unifonicClient = new UnifonicClient();
        $unifonicMessage->content = "Your Verification Code Is: ";
        $to = $phone;
        $co = $code;
        $data = $unifonicClient->sendVerificationCode($to, $co, $unifonicMessage);
        Log::channel('single')->info($data);
        Log::channel('slack')->info($data);
        return $data;
    }


    public function getLogoAttribute($value)
    {

        if ($value != null) {
            return url((@$this->attributes['logo']));
        }
    }

    public function permission()
    {
        return $this->hasOne(AdminPermission::class, 'admin_id');
    }

    public function getPermissionArrayAttribute()
    {

        $per = AdminPermission::where('admin_id', $this->id)->first();
        if ($per) {
            $array = explode(',', $per->permissions);

            return $array;
        }
        return null;
    }

    public function getPermissionNameArrayAttribute()
    {

        $per = AdminPermission::where('admin_id', $this->id)->first();
        if ($per) {
            $array = explode(',', $per->permissions);
            $perm = Permission::whereIn('id', $array)->pluck('display_name');
            return $perm->toArray();
        }
        return null;
    }
}
