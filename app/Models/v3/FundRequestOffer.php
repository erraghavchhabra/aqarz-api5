<?php

namespace App\Models\v3;

use App\Http\Resources\v4\EstateResource;
use App\Models\dashboard\Admin;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//class FundRequestOffer extends Model
class FundRequestOffer extends Model
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
        'uuid',
        'request_id',
        'emp_id',
        'instument_number',
        'guarantees',
        'beneficiary_name',
        'beneficiary_mobile',
        'status',
        'estate_id',
        'provider_id',
        'assigned_id',
        'reason',
        'send_offer_type',
        'first_show_date',
        'show_count',
        'request_preview_date',
        'app_name',
        'start_at',
        'review_at',
        'accept_review_at',
        'accepted_at',
        'cancel_at',



    ];

    protected $hidden = ['priority','assigned_commit',
        'assigned_id','commints_count','created_by_id',
        'closed_at',
        'last_commit',
        'time_estimate',
        'start_date',
        'contract_status',
        'negotiation_price',
        'is_paid',
        'paid_status',
        'stage_status',
        'price_per_ft',
        'preview_status',
        'funding_status',
    ];
    protected $appends = ['in_fav', 'status_name','estate_type_name'];


    public function fund_request()
    {
        return $this->belongsTo(RequestFund::class, 'uuid', 'uuid');
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function getInFavAttribute()
    {


        $fav = Favorite::where('type_id', $this->id)
            ->where('type', 'fund')
            ->where('status', '1')
            ->first();
        if ($fav) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getStatusNameAttribute()
    {

        if ($this->status == null) {
            return __('views.no_status');
        } elseif ($this->status == 'rejected_customer') {
            return __('views.rejected_customer');
        } elseif ($this->status == 'active') {
            return __('active');
        } elseif ($this->status == 'accepted_customer') {
            return __('views.accepted_customer');
        }
        elseif ($this->status == 'sending_code') {
            return __('views.sending_code');
        }
        elseif ($this->status == 'new') {
            return __('views.new');
        } elseif ($this->status == 'waiting_code') {
            return __('views.waiting_code');
        } else {
            return __('views.expired');
        }
    }

    public function getEstateTypeNameAttribute()
    {


        return @$this->estate_type->name;
    }
    public function assigned()
    {
        return $this->belongsTo(Admin::class, 'assigned_id');
    }
}
