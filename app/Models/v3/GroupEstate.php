<?php

namespace App\Models\v3;

//use App\search\geo_search;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Laravel\Scout\Searchable;

class GroupEstate extends Model
{

    use SoftDeletes;

    //  use \Awobaz\Compoships\Compoships;
    /* use Searchable;
     use geo_search;*/
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';
    protected $table = 'estate_groups';
    protected $guarded = [

    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_name',
        'owner_management_commission',
        'owner_management_commission_type',
        'building_number',
        'user_id',
        'city_name',
        'state_name',
        'neighborhood_name',
        'postal_code',
        'additional_code',
        'owner_estate_name',
        'lat',
        'lan',
        'owner_estate_mobile',
        'owner_birth_day',
        'unit_counter',
        'unit_number',
        'status',
        'full_address',
        'instrument_number',
        'instrument_file',
        'instrument_status',
        'bank_id',
        'bank_name',
        'guard_name',
        'guard_mobile',
        'guard_identity',
        'image',
        'unit_counter',
        'interface'

    ];


    protected $casts = [
        'lat' => 'float',
        'lan' => 'float',
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];

    protected $appends = ['count_estate'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group_estate_notes()
    {
        return $this->hasMany(EstateGroupOwnerNote::class, 'group_estate_id');
    }

    public function estate()
    {
        return $this->hasMany(Estate::class, 'group_estate_id');
    }

    public function rent_contract()
    {
        return $this->hasMany(RentalContracts::class, 'estate_group_id');
    }
    public function getCountEstateAttribute()
    {


        return count(@$this->estate);
    }


}
