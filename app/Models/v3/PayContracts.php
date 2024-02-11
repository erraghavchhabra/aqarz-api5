<?php

namespace App\Models\v3;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayContracts extends Model
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
        'user_id',
        'estate_id',
        'estate_group_id',
        'payer_id',
        'status',
        'date_of_writing_the_contract',
        'total_price',
        'additional_contract_terms',
        'create_by',
        'contract_number',


    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payer()
    {
        return $this->belongsTo(TentPayUser::class, 'payer_id');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

}
