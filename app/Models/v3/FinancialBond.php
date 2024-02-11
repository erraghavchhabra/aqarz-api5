<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialBond extends Model
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
    protected $table = 'financial_bonds';

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
        'type',
        'client_id',
        'experiences_id',
        'invoice_id',
        'dues_type',
        'user_id',
        'estate_id',
        'client_name',
        'client_mobile',
        'client_identity',
        'publication_date',
        'contract_id',
        'collector_emp_id',
        'interval_from_date',
        'interval_to_date',
        'amount',
        'statement',
        'status',
        'for_owner',
        'payment_type',



    ];

    public function contract()
    {
        return $this->belongsTo(RentalContracts::class, 'contract_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(RentalContractInvoice::class, 'invoice_id', 'id');
    }

    public function experiences()
    {
        return $this->belongsTo(EstateExpense::class, 'experiences_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(TentPayUser::class, 'client_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function collector_emp()
    {
        return $this->belongsTo(Employee::class, 'collector_emp_id', 'id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

    public function client_movment()
    {
        return $this->belongsTo(RentContractFinancialMovement::class, 'id', 'financialbond_id');
    }

    public function owner_movment()
    {
        return $this->belongsTo(EstateOwnerFinanceMovment::class, 'id', 'financialbond_id');
    }

    public function EstateDeposit()
    {
        return $this->belongsTo(EstateDeposit::class, 'id', 'financialbond_id');
    }


    public function delete()
    {
        $this->client_movment()->delete();
        $this->owner_movment()->delete();
        $this->EstateDeposit()->delete();
        parent::delete();
    }

    protected $appends = ['customer_character'];

    public function getCustomerCharacterAttribute()
    {


        return @$this->client->customer_character;
    }
}
