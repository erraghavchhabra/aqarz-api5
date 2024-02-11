<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstateExpense extends Model
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
        'payment_type',
        'user_id',
        'tent_id',
        'type',
        'due_date',
        'statement_note',
        'price',
        'rent_contract_id',
        'cost_type',


    ];
    protected $appends = ['type_name', 'estate_name', 'owner_name', 'estate_group_name'];

    protected $hidden = ['Estate', 'user'];

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

    public function rent_contract()
    {
        return $this->belongsTo(RentalContracts::class, 'rent_contract_id');
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
}
