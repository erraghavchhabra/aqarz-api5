<?php

namespace App;

use App\Models\v1\City;
use App\Models\v1\MemberType;
use App\Models\v1\Neighborhood;
use App\Models\v1\ServiceType;
use App\Models\v1\UserPlan;
use App\Models\v2\CourseType;
use App\Models\v2\Employee;
use App\Models\v2\ExperienceType;
use App\Models\v2\MsgDet;
use App\Models\v3\Estate;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\GroupEstate;
use App\Models\v3\IamProvider;
use App\Models\v3\PayContracts;
use App\Models\v3\RentalContracts;
use App\Models\v3\TentPayUser;
use App\Models\v3\Ticket;
use App\Models\v4\PlatformSubscriptions;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Self_;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable, SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'city_id',
        'identity',
        'is_iam_complete',
        'fund_request_fav',
        'fund_request_offer',
        'restored_at',
        'is_pay',
        'name',
        'email',
        'password',
        'type',
        'restore_code',
        'device_token',
        'device_type',
        'mobile',
        'api_token',
        'country_code',
        'confirmation_code',
        'logo',
        'company_logo',
        'services_id',
        'members_id',
        'lat',
        'lan',
        'address',
        'confirmation_password_code',
        'is_certified',
        'is_fund_certified',
        'user_name',
        'is_edit_username',
        'count_visit',
        'count_fund_pending_offer',
        'count_request',
        'count_offer',
        'count_agent',
        'count_emp',
        'saved_filter_city',
        'saved_filter_fund_city',
        'saved_filter_fund_type',
        'saved_filter_type',
        'onwer_name',
        'office_staff',
        'experience',
        'rate',
        'count_call',
        'status',
        'mobile_verified_at',
        'email_verified_at',
        'related_company',
        'count_fund_offer',
        'count_estate',
        'count_accept_offer',
        'count_accept_fund_offer',
        'count_request',
        'is_employee',
        'employer_id',
        'bio',
        'experiences_id',
        'courses_id',
        'count_preview_fund_offer',
        'count_fund_request',
        'hide_estate_id',
        'account_type',
        'license_number',
        'advertiser_number',
        'last_active',
        'show_rate_request',
        'mobile',
        'crn_number',
        'vat_name',
        'vat_number',
        'zatca_city',
        'zatca_city_subdivision',
        'zatca_street',
        'zatca_postal_zone',
        'fal_license_number',
        'fal_license_expiry',
    ];

    protected $appends = [
        'trial_period',
        'link',
        'member_name',
        'service_name',
        'experience_name',
        'course_name',
        'member_name_web',
        'service_name_web',
        'deep_link',
        'is_pay_name',
        'user_plan',
        'emp',
        'profile_percentage',
        'is_password',
        'message_not_read',
        'have_platform_subscription',
        'check_zatca'
        //   'emp_name'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //   'password',
        'remember_token',
        'confirmation_code',
        'confirmation_password_code',
        'password',
        'restore_code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /*  protected $casts = [
          'email_verified_at' => 'datetime',
      ];
  */

    public function getCheckZatcaAttribute()
    {
        if ($this->crn_number && $this->vat_name && $this->vat_number) {
            return true;
        } else {
            return false;
        }
    }

    public function getOnwerNameAttribute($value)
    {
        if ($this->name) {
            return $this->name;
        } else {
            if (@$this->is_iam_complete == 1) {
                return @$this->Iam_information->first_last_name;
            } else {
                return $value;
            }
        }
    }

    public function getMessageNotReadAttribute()
    {
        return MsgDet::where('receiver_id', @auth()->user()->id)->where('sender_id', '!=', null)->where('seen', '!=', 1)->count();
    }

    public function sendOtp($phone, $code)
    {

        $unifonicMessage = new UnifonicMessage();
        $unifonicClient = new UnifonicClient();
        $unifonicMessage->content = "Your Verification Code Is: ";
        $to = '966' . $phone;
        $co = $code;
        $data = $unifonicClient->sendVerificationCode($to, $co, $unifonicMessage);
        Log::channel('single')->info($data);
        Log::channel('slack')->info($data);
        return $data;
    }

    public function restoreOtp($phone, $code)
    {

        $unifonicMessage = new UnifonicMessage();
        $unifonicClient = new UnifonicClient();
        $unifonicMessage->content = "Your Verification Code Is: ";
        $to = $phone;
        $co = $code;
        $data = $unifonicClient->sendVerificationCode($to, $co, $unifonicMessage);
        Log::channel('single')->info($data);
        Log::channel('slack')->info($data);
        return $data;
    }

    public function getLogoAttribute($value)
    {

        if ($value != null) {
            return url((@$this->attributes['logo']));
        }
    }

    public function getlastActiveAttribute($value)
    {

        if ($value != null) {
            return Carbon::parse($value)->format('Y-m-d H:i:s');;
        } else {
            return Carbon::parse($this->created_ar)->format('Y-m-d H:i:s');
        }
    }

    public function getCompanyLogoAttribute($value)
    {

        if ($value != null) {
            return url((@$this->attributes['company_logo']));
        }
    }


    public function getCountEstateAttribute()
    {
        return Estate::where('user_id', $this->id)->count() ?? 0;
    }


    public function getCityNameAttribute()
    {

        $city = City::where('serial_city', $this->city_id)->first();
        if ($city) {
            return $city->name;
        }

        return null;

    }


    public function getUserPlanAttribute()
    {


        if ($this->type = 'provider') {
//            $userPlan = UserPlan::where('user_id', $this->id)
//                ->where('date_end', '>', date('Y-m-d'))
//                ->first();

            $userPlan = $this->platform_plan->where('status', 'active')->first();

            if ($userPlan) {
                return $userPlan;
            }
        }

        return null;

    }

    public function getIsPasswordAttribute()
    {
        return $this->password != null ? true : false;
    }


    public function getIsPayNameAttribute()
    {


        if ($this->is_pay == 1) {
            return __('views.active');
        }

        return __('views.not_active');

    }


    public function getServiceNameAttribute()
    {


        if ($this->type == 'provider') {
            $array = explode(',', $this->services_id);
            $service = ServiceType::whereIn('id', $array)->get();
            $serviceName = '';
            foreach ($service as $serviceItem) {
                $serviceName .= ',' . $serviceItem->name;
            }

            return $service;
        } else {
            return null;
        }


        return null;

    }


    public function getMemberNameAttribute()
    {


        if ($this->type == 'provider') {
            $array = explode(',', $this->members_id);
            $service = MemberType::whereIn('id', $array)->get();
            $serviceName = '';
            foreach ($service as $serviceItem) {
                $serviceName .= ',' . $serviceItem->name;
            }

            return $service;
        } else {
            return null;
        }


        return null;

    }

    public function getCourseNameAttribute()
    {


        if ($this->type == 'provider') {
            $array = explode(',', $this->courses_id);
            $CourseType = CourseType::whereIn('id', $array)->get();
            $CourseTypeName = '';
            foreach ($CourseType as $CourseTypeItem) {
                $CourseTypeName .= ',' . $CourseTypeItem->name;
            }

            return $CourseType;
        } else {
            return null;
        }


        return null;

    }

    public function getExperienceNameAttribute()
    {
        if ($this->type == 'provider') {
            $array = explode(',', $this->experiences_id);
            $ExperienceType = ExperienceType::whereIn('id', $array)->get();
            $ExperienceTypeName = '';
            foreach ($ExperienceType as $ExperienceTypeItem) {
                $ExperienceTypeName .= ',' . $ExperienceTypeItem->name;
            }

            return $ExperienceType;
        } else {
            return null;
        }


        return null;

    }

    public function getServiceNameWebAttribute()
    {


        if ($this->type == 'provider') {
            $array = explode(',', $this->services_id);
            $service = ServiceType::whereIn('id', $array)->get();
            $serviceName = '';
            foreach ($service as $serviceItem) {
                $serviceName .= ',' . $serviceItem->name_ar;
            }

            return $serviceName;
        } else {
            return null;
        }


        return null;

    }


    public function getMemberNameWebAttribute()
    {


        if ($this->type == 'provider') {
            $array = explode(',', $this->members_id);
            $service = MemberType::whereIn('id', $array)->get();
            $serviceName = '';
            foreach ($service as $serviceItem) {
                $serviceName .= ',' . $serviceItem->name_ar;
            }


            return $serviceName;
        } else {
            return null;
        }


        return null;

    }

    public function getNeighborhoodNameAttribute()
    {

        $neighborhood = Neighborhood::where('neighborhood_serial', $this->neighborhood_id)
            ->first();
        if ($neighborhood) {
            return $neighborhood->name;
        }

        return null;

    }


    public function getLinkAttribute()
    {

        $url = 'https://aqarz.sa/profile/';
        return $url . $this->user_name;

    }

    public function getDeepLinkAttribute()
    {

        $url = 'https://aqarz.sa/';
        return $url . $this->id . '/show';

    }

    public function getEmpNameAttribute()
    {


        $emp = Employee::where('emp_mobile', $this->mobile)->first();
        if ($emp) {
            return $emp->emp_name;
        } else {
            return null;
        }

    }


    /*public function getRouteKeyName()
    {
        return 'username';
    }*/
    public static function isRequestedPathAPost()
    {
        return !preg_match('/[^\w\d\-\_]+/', \Request::path()) &&
            User::whereUserName(\Request::path())->exists();
    }


    public function getTrialPeriodAttribute()
    {


        $fdate = $this->created_at;
        $tdate = date('Y-m-d H:i:s');


        $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $fdate);
        $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $tdate);

        $diff_in_days = $to->diffInDays($from);

        if ($diff_in_days > 10) {
            return 0;
        } else {
            return 1;
        }


    }

    public function city()
    {
        return $this->belongsTo(\App\Models\v3\City::class, 'city_id', 'serial_city');
    }


    public function getEmpAttribute()
    {
        if ($this->employer_id != null) {
            $user = User::where('id', $this->employer_id)
                ->first();
            if ($user) {
                return $user;
            }

            return null;
        }


    }


    public function getProfilePercentageAttribute()
    {
        $percentage = 0;
        if ($this->account_type == 'owner') {
            if ($this->logo) {
                $percentage = $percentage + 25;
            }
            if ($this->onwer_name) {
                $percentage = $percentage + 25;
            }
            if ($this->email) {
                $percentage = $percentage + 25;
            }
            if ($this->is_iam_complete == 1) {
                $percentage = $percentage + 25;
            }
        } else {
            if ($this->logo) {
                $percentage = $percentage + 10;
            }
            if ($this->onwer_name) {
                $percentage = $percentage + 10;
            }
            if ($this->name) {
                $percentage = $percentage + 10;
            }
            if ($this->email) {
                $percentage = $percentage + 10;
            }
            if ($this->services_id) {
                $percentage = $percentage + 10;
            }
            if ($this->members_id) {
                $percentage = $percentage + 10;
            }
            if ($this->license_number) {
                $percentage = $percentage + 10;
            }
            if ($this->experiences_id) {
                $percentage = $percentage + 10;
            }
            if ($this->courses_id) {
                $percentage = $percentage + 10;
            }
            if ($this->is_iam_complete == 1) {
                $percentage = $percentage + 10;
            }
        }

        return $percentage;

    }

    public function fund_request_offers()
    {
        return $this->hasMany(FundRequestOffer::class, 'provider_id', 'id')->whereHas('estate')->whereHas('fund_request');
    }

    public function Iam_information()
    {
        return $this->hasOne(IamProvider::class, 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id', 'id');
    }

    public function employee()
    {
        return $this->hasMany(User::class, 'employer_id', 'id');
    }

    public function userEstate()
    {
        return $this->hasMany(Estate::class, 'user_id', 'id');
    }

    public function relatedUserEstates()
    {
        return $this->hasMany(Estate::class, 'user_id', 'employer_id');
    }

    public function allUserRelations()
    {
        return $this->userRelations->merge($this->relatedUserRelations);
    }

    public function estate()
    {

        /* $array=  $this->employee()->pluck('id')
                ->toArray();*/

        // $array=array_push()

        if ($this->account_type == 'company' && @$this->employee()->count() > 0 && @$this->employee()->whereHas('estate')->count() > 0) {
            return $this->hasMany(Estate::class, 'company_id', 'id');
        } else {
            return $this->hasMany(Estate::class, 'user_id', 'id');
        }
        /*  return $this->hasMany(Estate::class, 'user_id', 'id')
                     ->orWhereIn('user_id', $this->employee()->pluck('id')
                         ->toArray());*/
    }

    public function group_estate()
    {

        /* $array=  $this->employee()->pluck('id')
                ->toArray();*/

        // $array=array_push()

        if ($this->account_type == 'company' && @$this->employee()->count() > 0 && @$this->employee()->whereHas('group_estate')->count() > 0) {
            return $this->hasMany(GroupEstate::class, 'company_id', 'id');
        } else {
            return $this->hasMany(GroupEstate::class, 'user_id', 'id');
        }
        /*  return $this->hasMany(Estate::class, 'user_id', 'id')
                     ->orWhereIn('user_id', $this->employee()->pluck('id')
                         ->toArray());*/
    }

    public function TentPayUser()
    {
        return $this->hasMany(TentPayUser::class, 'user_id', 'id');
    }

    public function TentContract()
    {
        return $this->hasMany(RentalContracts::class, 'user_id', 'id');
    }

    public function PayContract()
    {
        return $this->hasMany(PayContracts::class, 'user_id', 'id');
    }

    public function platform_plan()
    {
        return $this->hasMany(PlatformSubscriptions::class, 'user_id', 'id');
    }

    //have_platform_subscription
    public function getHavePlatformSubscriptionAttribute()
    {
         $subscription = $this->platform_plan->where('status', 'active')->first();
        if ($subscription) {
            return true;
        }else{
            return false;
        }
    }


}
