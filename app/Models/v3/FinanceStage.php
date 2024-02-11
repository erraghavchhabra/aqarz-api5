<?php

namespace App\Models\v3;

use App\Models\dashboard\Admin;
use Illuminate\Database\Eloquent\Model;

class FinanceStage extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];

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
    protected $with = ['emp','assigned'];
    protected $table = 'clickup_request_finance_stages';
    protected $fillable = [
        'request_id',
        'request_uuid',
        'emp_id',
        'assigned_id',
        'funding_status',
        'contract_status',
        'attachments',
        'cancel_cause',
        'notes',
    ];

    public function emp()
    {
        return $this->belongsTo(Admin::class, 'emp_id');
    }
    public function assigned()
    {
        return $this->belongsTo(Admin::class, 'assigned_id');
    }
}
