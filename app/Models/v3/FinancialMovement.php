<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialMovement extends Model
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

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = (new Carbon($value))->format('Y-m-d h:i:s');
    }

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
        'user_id',
        'customer_id',
        'statment',
        'owed_money',
        'paid_money',
        'total_money',



    ];
    protected $appends = ['customer_name', 'estate_name', 'owner_name'];

    protected $hidden = ['Estate', 'user','Customer'];

    public function getTypeNameAttribute()
    {


        return __('views.' . @$this->type);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }
    public function Customer()
    {
        return $this->belongsTo(TentPayUser::class, 'customer_id');
    }


    public function getEstateNameAttribute()
    {
        return @$this->Estate->estate_name;
    }

    public function getEstateGroupNameAttribute()
    {
        return @$this->Estate->estate_group->group_name;
    }

    public function getOwnerNameAttribute()
    {
        return @$this->user->onwer_name;
    }
    public function getCustomerNameAttribute()
    {
        return @$this->Customer->name;
    }
}
