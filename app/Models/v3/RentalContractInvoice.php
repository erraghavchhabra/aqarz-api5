<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalContractInvoice extends Model
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
    protected $table = 'rent_invoices';
    protected $fillable = [
        'bond_type',
        'collection_date',
        'period_from',
        'period_to',
        'collector_name',
        'payment_type',
        'statement',
        'rental_contracts_id',
        'status',
        'user_id',
        'client_id',
        'paid_amount',
        'owed_amount',


    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rental_contract()
    {
        return $this->belongsTo(RentalContracts::class, 'rental_contracts_id');
    }

}
