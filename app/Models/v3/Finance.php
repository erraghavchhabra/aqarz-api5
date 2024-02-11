<?php

namespace App\Models\v3;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Finance extends Model
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
        'operation_type_id',
        'estate_type_id',
        'job_type',
        'finance_interval',
        'job_start_date',
        'estate_price',
        'engagements',
        'city_id',
        'name',
        'identity_number',
        'identity_file',
        'mobile',

        'total_salary',


        'available_amount',
        'national_address',
        'national_address_file',
        'building_number',
        'street_name',
        'neighborhood_name',
        'building_city_name',
        'postal_code',
        'status',
        'additional_number',
        'unit_number',
        'solidarity_partner',
        'solidarity_salary',
        'user_id',
        'offer_numbers',
        'bank_id',
        'birthday',
        'is_subsidized_property',
        'is_first_home',
        'estate_id'

    ];

    protected $appends=['operation_type_name','estate_type_name','city_name','bank_name'];

    public function operation_type()
    {
        return $this->belongsTo(OprationType::class);
    }

    public function estate_type()
    {
        return $this->belongsTo(EstateType::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class,'city_id','serial_city');
    }


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function offer()
    {
        return $this->hasMany(FinanceOffer::class,'finance_id');
    }



    public function getOperationTypeNameAttribute()
    {


        return @$this->operation_type->name;
    }
    public function getEstateTypeNameAttribute()
    {


        return @$this->estate_type->name;
    }

    public function getCityNameAttribute()
    {


        return @$this->city->name;
    }


    public function getBankNameAttribute()
    {


        return @$this->bank->name;
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class,'bank_id');
    }





    public function getNationalAddressFileAttribute()
    {
        return url( (@$this->attributes['national_address_file']));
    }
    public function getIdentityFileAttribute()
    {
        return url( (@$this->attributes['identity_file']));
    }


}
