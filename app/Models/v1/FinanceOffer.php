<?php

namespace App\Models\v1;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceOffer extends Model
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


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'instument_number',
        'guarantees',
        'beneficiary_name',
        'beneficiary_mobile',
        'code',
        'status',
        'user_id',
        'estate_id',
        'finance_id',

    ];





    public function finance_request()
    {
        return $this->belongsTo(Finance::class,'finance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }


}
