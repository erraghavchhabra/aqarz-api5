<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentContractNote extends Model
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
        'rental_contracts_id',
        'user_id',
        'notes',



    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rent_contract()
    {
        return $this->belongsTo(RentalContracts::class, 'rental_contracts_id');
    }
}
