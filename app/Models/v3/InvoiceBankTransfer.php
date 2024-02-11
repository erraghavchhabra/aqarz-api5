<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceBankTransfer extends Model
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
    protected $table = 'invoice_bank_transfers';

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
        'expenses_id',
        'invoice_id',
        'rent_contracts_id',
        'user_id',

        'bank_converter_from_id',
        'bank_converter_to_id',
        'bank_transfer_number',
        'bank_transfer_photo',



    ];

    public function rent_contract()
    {
        return $this->belongsTo(RentalContracts::class, 'rent_contract_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(RentalContractInvoice::class, 'invoice_id', 'id');
    }

    public function experiences()
    {
        return $this->belongsTo(EstateExpense::class, 'expenses_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(TentPayUser::class, 'tent_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function bank_from()
    {
        return $this->belongsTo(Bank::class, 'bank_converter_from_id', 'id');
    }
    public function bank_to()
    {
        return $this->belongsTo(Bank::class, 'bank_converter_to_id', 'id');
    }

    public function getBankTransferPhotoAttribute($value)
    {

        if ($value != null) {
            return url((@$this->attributes['bank_transfer_photo']));
        }
    }
}
