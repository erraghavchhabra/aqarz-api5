<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentContractFinancialMovement extends Model
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
    protected $table = 'rent_contract_financial_movements';
    protected $fillable = [
        'rental_contracts_id',
        'financialbond_id',
        'client_id',
        'estate_id',
        'dues_type',
        'owed_amount',
        'paid_amount',
        'remaining_amount',
        'rental_contract_invoice_id',
        'estate_expenses_id',
        'statement',
        'status',

        'user_id',
        'remaining_amount',



    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }
    public function rental_contract()
    {
        return $this->belongsTo(RentalContracts::class, 'rental_contracts_id');
    }

    public function rental_contract_invoice()
    {
        return $this->belongsTo(RentalContractInvoice::class, 'rental_contract_invoice_id');
    }
    public function estate_expenses()
    {
        return $this->belongsTo(EstateExpense::class, 'estate_expenses_id');
    }
    public function client()
    {
        return $this->belongsTo(TentPayUser::class,'client_id','id');
    }


}
