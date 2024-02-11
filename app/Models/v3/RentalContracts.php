<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalContracts extends Model
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
        'tent_id',
        'user_id',
        'estate_id',
        'payment_value',
        'count_month',
        'estate_group_id',
        'rent_total_amount',
        'contract_number',
        'date_of_writing_the_contract',
        'additional_contract_terms',
        'payment_type',
        'annual_increase',
        'refundable_insurance',
        'rental_commission',
        'maintenance_and_services',
        'electricity',
        'waters',
        'cleanliness',
        'property_management',
        'services',
        'start_date',
        'end_date',
        'contract_interval',
        'status',
        'ejar_id_info',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tent()
    {
        return $this->belongsTo(TentPayUser::class, 'tent_id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }
    public function estate_expenses()
    {
        return $this->hasMany(EstateExpense::class, 'rent_contract_id');
    }
    public function rent_contract_notes()
    {
        return $this->hasMany(RentContractNote::class, 'rental_contracts_id');
    }
    public function rent_contract_financial_movements()
    {
        return $this->hasMany(RentContractFinancialMovement::class, 'rental_contracts_id');
    }
    public function rent_invoices()
    {
        return $this->hasMany(RentalContractInvoice::class, 'rental_contracts_id');
    }
}
