<?php

namespace App\Models\v3;

use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundRequestOfferClickUp extends FundRequestOffer
{

    use SoftDeletes;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->fillable = array_merge($this->fillable, array(
            'priority',
            'assigned_commit',
            'assigned_id',
            'commints_count',
            'created_by_id',
            'closed_at',
            'last_commit',
            'time_estimate',
            'start_date',
            'contract_status',
            'negotiation_price',
            'is_paid',
            'state_id',
            'paid_status',
            'stage_status',
            'price_per_ft',
            'preview_status',
            'funding_status',
            'contact_status',
        ));
        $this->makeVisible(['priority', 'assigned_commit', 'assigned_id', 'commints_count',
            'closed_at',
            'last_commit',
            'created_by_id',
            'time_estimate',
            'start_date',
            'contract_status',
            'negotiation_price',
            'is_paid',
            'state_id',
            'paid_status',
            'stage_status',
            'price_per_ft',
            'preview_status',
            'funding_status',
            'contact_status',
        ]);

        //$this->casts=$this->casts;
        //  $this->appends=$this->appends;
        // $this->
    }

    public function toArray()
    {

        $url = 'https://aqarz.sa/';
        return [

            'id' => $this->id,
            'uuid' => $this->uuid,
            'request_id' => $this->request_id,
          //  'beneficiary_name' => @$$this->fund_request->beneficiary_name,
         //   'beneficiary_mobile' => @$this->fund_request->beneficiary_mobile,
            'beneficiary_name' => $this->fund_request->beneficiary_name,
            'beneficiary_mobile' => $this->fund_request->beneficiary_mobile,
            'city_name' => @$this->fund_request->city_name,
            'price' => @$this->fund_request->estate_price_range,
            'neighborhood_name' => @$this->fund_request->neighborhood_name,
            'lat' => $this->estate->lat,
            'lan' => $this->estate->lan,
            'status' => $this->status,
            'estate_id' => $this->estate_id,
            'state_id' => @$this->fund_request->state_id,
            'negotiation_price' => $this->negotiation_price,
            'price_per_ft' => $this->price_per_ft,
            'send_offer_type' => $this->send_offer_type,
            'is_paid' => $this->is_paid,
            'paid_status' => $this->paid_status,
            'priority' => $this->priority,
            'contract_status' => $this->contract_status,
            'stage_status' => $this->stage_status,
            'funding_status' => $this->funding_status,
            'preview_status' => $this->preview_status,
            'reason' => $this->reason,
            'assigned_commit' => $this->assigned_commit,
            'last_commit' => $this->last_commit,
            'assigned_id' => $this->assigned_id,
            'created_by_id' => $this->created_by_id,
            'commints_count' => $this->commints_count,
            'app_name' => $this->app_name,
            'is_close' => $this->is_close,
            'provider_id' => $this->provider_id,
            'show_count' => $this->show_count,
            'time_estimate' => $this->time_estimate,
            'first_show_date' => $this->first_show_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
            'start_date' => $this->start_date,
            'request_preview_date' => $this->request_preview_date,
            'status_name' => $this->status_name,
            'estate_type_name' => $this->estate->estate_type_name,
            'estate_type_id' => $this->estate->estate_type_id,
            'estate_full_address' => $this->estate->full_address,
            'estate_total_price' => $this->estate->total_price,
            'estate_total_area' => $this->estate->total_area,

            'estate_link' => @$this->estate->link,
            'request_link' => @$this->fund_request->link,
            'comments' => @$this->notes,


        ];
    }

    protected $table = 'fund_request_offers';
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];
    /* protected $casts = [
         'created_at' => 'datetime:Y-m-d h:i:s',
         'updated_at' => 'datetime:Y-m-d h:i:s',
         'deleted_at' => 'datetime:Y-m-d h:i:s'
     ];*/

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    /* protected $fillable = [
        'priority',


     ];*/

    //  protected $hidden = ['provider_id'];

    /*
        public function getContractStatusAttribute($value)
        {
    //'contract_processing','send_contract','signing_contract'

            if ($value)
                return __('views.' . $value);


        }

        public function getPaidStatusAttribute($value)
        {
    //''pending','under conversion','was received'

            if ($value)
                return __('views.' . $value);


        }
        public function getStageStatusAttribute($value)
        {
    //'email_capture','marketing_qualified','sales_qualified','demo','proposal','negotiations','hand_off_to_success','launched','follow_up'

            if ($value)
                return __('views.' . $value);


        }

        public function getPreviewStatusAttribute($value)
        {
    //'email_capture','marketing_qualified','sales_qualified','demo','proposal','negotiations','hand_off_to_success','launched','follow_up'

            if ($value)
                return __('views.' . $value);


        }
        public function getFundingStatusAttribute($value)
        {
    //'email_capture','marketing_qualified','sales_qualified','demo','proposal','negotiations','hand_off_to_success','launched','follow_up'

            if ($value)
                return __('views.' . $value);


        }

        public function getContactStatusAttribute($value)
        {
    //'email_capture','marketing_qualified','sales_qualified','demo','proposal','negotiations','hand_off_to_success','launched','follow_up'
           // 'answered','no_response','recall','whats_up'
            if ($value)
                return __('views.' . $value);


        }

    */
    public function notes()
    {
        return $this->hasMany(FundOfferComment::class, 'fund_offer_id', 'id');
    }

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

    public function getEstatePriceRangeAttribute()
    {
        $estate_price_id = EstatePrice::find($this->estate_price_id);
        if ($estate_price_id) {
            return $estate_price_id->estate_price_range;
        }

        return null;

        //  return $this->estate_price->estate_price_range;
    }
}
