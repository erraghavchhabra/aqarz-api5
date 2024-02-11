<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TentPayUser extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $table = 'tent_pay_users';
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
        'name',
        'customer_character',
        'mobile',
        'other_mobile',
        'identification',
        'identification_photo',
        'tax_number',
        'date_of_birth',
        'collector_name',
        'nationality',
        'movement_type',
        'building_number',
        'postal_code',
        'additional_code',
        'email',
        'bank_id',
        'bank_name',
        'account_number',
        'phone',
        'fax_number',
        'address',
        'mail_box',
        'guarantor',
        'amount_paid',
        'user_id',


    ];
    public function tent_pay_notes()
    {
        return $this->hasMany(TentPayUserNote::class, 'tent_pay_user_id');
    }
    public function tent_contract()
    {
        return $this->hasMany(RentalContracts::class, 'tent_id');
    }
    public function pay_contract()
    {
        return $this->hasMany(PayContracts::class, 'payer_id');
    }
}
