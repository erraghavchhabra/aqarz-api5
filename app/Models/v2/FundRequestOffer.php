<?php

namespace App\Models\v2;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'status',
        'estate_id',
        'provider_id',
        'reason',
        'send_offer_type',
        'first_show_date',
        'show_count',
        'request_preview_date',
        'app_name',
        'request_id'

    ];

  //  protected $hidden = ['provider_id'];
    protected $appends = ['in_fav', 'status_name'];


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
        elseif ($this->status == 'new') {
            return __('views.new');
        }

        else {
            return __('views.expired');
        }
    }
}
