<?php

namespace App\Models\v4;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_id', 'action_id', 'user_id','price','response_summary',
        'response_code','approved','currency','status','plan_subscription_id','source_scheme'
    ];
    protected $table = 'payment_log';
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

}
