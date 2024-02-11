<?php

namespace App\Models\v3;

use App\User;
use Illuminate\Database\Eloquent\Model;

class DeferredInstallment extends Model
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
    protected $fillable = [
        'operation_type_id',
        'estate_type_id',
        'contract_interval',
        'financing_body',
        'previous_financial_failures',
        'stumble_amount',
        'engagements',
        'personal_financing_engagements',
        'lease_finance_engagements',
        'credit_card_engagements',
        'display_in_app',


        'rent_price',
        'tenant_name',
        'tenant_mobile',
        'tenant_identity_number',
        'tenant_birthday',


        'tenant_city_id',
        'tenant_job_type',
        'tenant_total_salary',
        'tenant_salary_bank_id',


        'status',
        'user_id',


        'is_salary_adapter_on',


    ];

    protected $appends = ['operation_type_name', 'estate_type_name', 'tenant_city_name', 'status_name'];

    protected $hidden = [
        'operation_type',
        'estate_type',
        'user',
        'tenant_city',
        'tenant_bank',
        'deleted_at',
    ];

    public function operation_type()
    {
        return $this->belongsTo(OprationType::class);
    }

    public function estate_type()
    {
        return $this->belongsTo(EstateType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tenant_city()
    {
        return $this->belongsTo(City::class, 'tenant_city_id', 'serial_city');
    }


    public function tenant_bank()
    {
        return $this->belongsTo(Bank::class, 'tenant_salary_bank_id');
    }


    public function getOperationTypeNameAttribute()
    {


        return @$this->operation_type->name;
    }

    public function getEstateTypeNameAttribute()
    {


        return @$this->estate_type->name;
    }

    public function getTenantCityNameAttribute()
    {


        return @$this->tenant_city->name;
    }

    public function getStatusNameAttribute()
    {


        if ($this->status == '0') {
            return 'قيد الانتظار';
        }
        if ($this->status == '1') {
            return 'تمت الموافقة';
        }
        if ($this->status == '2') {
            return 'مرفوض';
        }
    }


    public function comment()
    {
        return $this->hasMany(DeferredInstallmentComment::class, 'deferred_installment_id');
    }


}
