<?php

namespace App\Models\v2;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
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
        'fort_id',
        'plan_id',
        'user_id',
        'user_plan_id',
        'payment_method_id',
        'total',
        'is_pay',
        'status',
        'pdf_file'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function user_plan()
    {
        return $this->belongsTo(UserPlan::class);
    }

}
