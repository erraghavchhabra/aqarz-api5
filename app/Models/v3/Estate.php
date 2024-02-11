<?php

namespace App\Models\v3;

//use App\search\geo_search;
use App\Http\Resources\RentContractResource;
use App\Models\v4\Cities;
use App\User;

//use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

//use Laravel\Scout\Searchable;

class Estate extends Model
{

    use SoftDeletes;

//    use ElasticquentTrait;

    protected $indexSettings = [
        'analysis' => [
            'char_filter' => [
                'replace' => [
                    'type' => 'mapping',
                    'mappings' => [
                        '&=> and '
                    ],
                ],
            ],
            'filter' => [
                'word_delimiter' => [
                    'type' => 'word_delimiter',
                    'split_on_numerics' => false,
                    'split_on_case_change' => true,
                    'generate_word_parts' => true,
                    'generate_number_parts' => true,
                    'catenate_all' => true,
                    'preserve_original' => true,
                    'catenate_numbers' => true,
                ]
            ],
            'analyzer' => [
                'default' => [
                    'type' => 'custom',
                    'char_filter' => [
                        'html_strip',
                        'replace',
                    ],
                    'tokenizer' => 'whitespace',
                    'filter' => [
                        'lowercase',
                        'word_delimiter',
                    ],
                ],
            ],
        ],
    ];

    function getIndexName()
    {
        return 'custom_index_name';
    }

    function getTypeName()
    {
        return 'custom_index_name';
    }
    //  use \Awobaz\Compoships\Compoships;
    /* use Searchable;
     use geo_search;*/
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    // protected $connection = 'customer';

    protected $guarded = [

    ];
    /* public function toSearchableArray()
     {
         $record = $this->toArray();

         $record['_geoloc'] = [
             'lat' => $record['lat'],
             'lng' => $record['lan'],
         ];

         unset($record['created_at'], $record['updated_at']); // Remove unrelevant data
         unset($record['lat'], $record['lan']);

         return $record;
     }*/


    protected $fillable = [
        'additional_code',
        'payment_value',
        'operation_type_id',
        'deleted_by_company',
        'company_id',
        'reason',
        'estate_type_id',
        'instrument_number',
        'is_disputes',
        'is_complete',
        'instrument_file',
        'pace_number',
        'planned_number',
        'land_number',
        'total_area',
        'estate_age',
        'floor_number',
        'street_view',
        'total_price',
        'meter_price',
        'owner_name',
        'owner_mobile',
        'lounges_number',
        'bathrooms_number',
        'rooms_number',
        'boards_number',
        'bedroom_number',
        'kitchen_number',
        'dining_rooms_number',
        'finishing_type',
        'interface',
        'instrument_status',
        'social_status',
        'lat',
        'lan',
        'note',
        'status',
        'estate_status',
        'user_id',
        'is_rent',
        'rent_type',
        'is_resident',
        'is_checked',
        'is_insured',
        'exclusive_contract_file',
        'neighborhood_id',
        'city_id',
        'address',
        'exclusive_marketing',
        'in_fund_offer',
        'is_from_rent_system',
        'video',
        'state_id',
        'estate_use_type',
        'estate_dimensions',
        'is_mortgage',
        'is_obligations',
        'postal_code',
        'estate_name',
        'instrument_date',
        'touching_information',
        'is_saudi_building_code',
        'elevators_number',
        'parking_spaces_numbers',
        'advertiser_side',
        'advertiser_character',
        'obligations_information',
        'full_address',
        'estate_type_name',
        'operation_type_name',
        'first_image',
        'rent_price',
        'is_updated_image',
        'is_rent_installment',
        'rent_installment_price',
        'total_price_number',
        'total_area_number',
        'unit_counter',
        'elevators_number',
        'unit_number',
        'parking_spaces_numbers',
        'is_hide',
        'advertiser_license_number',  //رقم تفويض المعلن
        'advertiser_email',
        'advertiser_mobile',
        'advertiser_number',
        'advertiser_name',
        'bank_id',
        'bank_name',
        'owner_management_commission_type',
        'owner_management_commission',
        'guard_name',
        'guard_mobile',
        'guard_identity',
        'building_number',
        'owner_estate_name',
        'owner_estate_mobile',
        'owner_birth_day',
        'group_estate_id',
        'street_name',
        'os_type',
        'neighborhood_name_request',
        'city_name_request',
        'appearing_count',
        'message_count',
        'message_whatsapp_count',
        'share_count',
        'favorite_count',
        'screenshot_count',
        'show_video_count',
        'location_count',
        'call_count',
        'license_number',
        'advertising_license_number',  //رقم ترخيص الاعلان
        'brokerageAndMarketingLicenseNumber', //رقم رخصة الوساطة والتسويق العقاري
        'channels', //قنوات الاعلان
        'creation_date',  // تاريخ انشاء ترخيص الاعلان
        'end_date', //تاريخ انتهاء ترخيص الاعلان
    ];

    protected $casts = [
        'lat' => 'float',
        'lan' => 'float',
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s',
        'channels' => 'array',
    ];

    protected $appends = [
        'link',
        'in_fav',
        'operation_type_name',
        // 'estate_type_name',
        //  'first_image',
        'city_name',
        'neighborhood_name',
        'estate_type_name_web',
        'city_name_web',
        'neighborhood_name_web',
        'interface_array',
        'estate_use_name',
        'advertiser_side_name',
        'advertiser_character_name',
        'state_name',
        'finishing_type_name',
        'comfort_names',
        'comfort_id',
        'instrument_file_react',

    ];
    protected $hidden = ['estate_type',
        'operation_type',
        'city',
        'neighborhood',
    ];

    public function operation_type()
    {
        return $this->belongsTo(OprationType::class);
    }

    public function estate_type()
    {
        return $this->belongsTo(EstateType::class);
    }

    public function estate_group()
    {
        return $this->belongsTo(GroupEstate::class, 'group_estate_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function getEstateAgeAttribute($value)
    {
        $age = null;
        if ($value || $value === '0') {

            if ($value === '0') {
                $age = __("views.new_age");
            } elseif ($value == 36) {
                $age = '+35 ' . __("views.year");
            } else {
                $age = $value . ' ' . __("views.year");
            }
        }
        return $age;
    }

    public function getOperationTypeNameAttribute()
    {
        $local = (app('request')->hasHeader('Accept-Language')) ? app('request')->header('Accept-Language') : 'ar';

        return $local == 'ar' ? @$this->operation_type->name_ar : @$this->operation_type->name_en;
    }

    public function getOwnerNameAttribute($value)
    {

        if (@$this->user->name) {
            return @$this->user->name;
        } else {
            if (@$this->user->is_iam_complete == 1) {
                return @$this->user->Iam_information->first_last_name;
            } else {
                return $value;
            }
        }
    }

//    public function getOwnerMobileAttribute($value)
//    {
//        return @$this->user->mobile;
//    }

    public function city()
    {
        return $this->belongsTo(Cities::class, 'city_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\v4\Region::class, 'state_id', 'id');
    }

    public function neighborhood()
    {
        return $this->belongsTo(\App\Models\v4\District::class, 'neighborhood_id', 'district_id');
    }


    public function getInFavAttribute()
    {


        $fav = Favorite::where('type_id', $this->id)
            ->where('type', 'estate')
            ->where('user_id', Auth::id())
            ->where('status', '1')
            ->first();
        if ($fav) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getInstrumentFileReactAttribute()
    {

        return ['file' => $this->instrument_file, 'type' => 'images'];
    }


    public function getEstateTypeNameAttribute()
    {


        return @$this->estate_type->name;
    }

    public function getEstateUseNameAttribute()
    {

        if ($this->estate_use_type) {
            return @__('views.' . $this->estate_use_type);
        } else {
            return null;
        }

    }

    public function getAdvertiserSideNameAttribute()
    {
        if ($this->advertiser_side) {
            return @__('views.' . $this->advertiser_side);
        } else {
            return null;
        }


    }

    public function getAdvertiserCharacterNameAttribute()
    {
        if ($this->advertiser_character) {
            return @__('views.' . $this->advertiser_character);
        } else {
            return null;
        }


    }

    public function getFinishingTypeNameAttribute()
    {
        if ($this->finishing_type) {
            return @__('views.' . $this->finishing_type);
        } else {
            return null;
        }


    }


    public function getEstateTypeNameWebAttribute()
    {


        return @$this->estate_type->name_ar;
    }

    public function getInstrumentFileAttribute()
    {

        if (isset($this->attributes['instrument_file'])) {
            return url((@$this->attributes['instrument_file']));
        } else {
            return null;
        }

    }


    public function getExclusiveContractFileAttribute($value)
    {
        if ($value) {
            return url($value);
        }
    }

    public function getFirstImageAttribute($value)
    {

        $img = AttachmentEstate::where('estate_id', $this->id)
            ->where('type', 'images')
            ->first();
        //    dd($img);
        if ($img) {
            return @$img->file;
        }
    }


    public function getComfortNamesAttribute()
    {

        $comfort = \App\Models\v3\ComfortEstate::with('comfort')->where('estate_id', $this->id)->get();
        $str = '';
        if ($comfort) {
            foreach ($comfort as $comfortItem) {
                $str .= @$comfortItem->comfort->name_ar . ',';
            }
            return $str;
        }

        return null;
    }

    public function getComfortIdAttribute()
    {

        $comfort = \App\Models\v3\ComfortEstate::with('comfort')->where('estate_id', $this->id)->get();
        $str = [];
        $i = 0;
        if ($comfort) {
            foreach ($comfort as $comfortItem) {
                $str[$i] = @$comfortItem->comfort->id;
                $i++;
            }
            return $str;
        }

        return null;
    }

    public function plannedFile()
    {
        return $this->hasMany(AttachmentPlanned::class, 'estate_id');
    }

    public function rates()
    {
        return $this->hasMany(RateEstate::class, 'estate_id');
    }

    public function EstateFile()
    {
        return $this->hasMany(AttachmentEstate::class, 'estate_id');
    }

    public function EstateExpense()
    {
        return $this->hasMany(EstateExpense::class, 'estate_id')->where('cost_type', 'with_contract');
    }

    public function EstateCostExpense()
    {
        return $this->hasMany(EstateExpense::class, 'estate_id')->where('cost_type', 'without_contract');
    }

    public function estate_notes()
    {
        return $this->hasMany(EstateOwnerNote::class, 'estate_id');
    }

    public function rent_contract()
    {
        return $this->hasMany(RentalContracts::class, 'estate_id');
    }

    public function comforts()
    {
        return $this->belongsToMany(Comfort::class, 'estate_comforts', 'estate_id');
    }

    public function getNeighborhoodNameAttribute()
    {

        return @$this->neighborhood->name_ar;

    }


    public function getCityNameAttribute()
    {

        return @$this->city->name_ar;

    }


    public function getNeighborhoodNameWebAttribute()
    {

        return @$this->neighborhood->name_ar;

    }


    public function getCityNameWebAttribute()
    {

        return @$this->city->name_ar;

    }

    public function getStateNameAttribute()
    {

        return @$this->status->name_ar;


        //  return null;

    }

    public function getLinkAttribute()
    {

        $url = 'https://aqarz.sa/';
        return @$url . 'estate/' . $this->id . '/show';

    }

    public function getLatAttribute($value)
    {
        return number_format((float)$value, 5, '.', '');

    }

    public function getLanAttribute($value)
    {
        return number_format((float)$value, 5, '.', '');

    }


    public function getInterfaceArrayAttribute()
    {

        $array = explode(',', $this->interface);

        return @$array;

    }

    public function getInterfaceAttribute($value)
    {

        //  $array = explode(',', $value);
        //$str = '';
        /* for ($i = 0; $i < count($array); $i++) {
             $str .=@(__('views.' . $array[$i])).',';
         }*/
        return $value;

    }

    public function getEstateStatusAttribute($value)
    {


        return @state_estate($value);
    }
}
