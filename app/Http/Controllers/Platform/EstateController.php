<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\AllAccountStatementResource;
use App\Http\Resources\AllEstateGroupResource;
use App\Http\Resources\AllTentPayUsersResource;
use App\Http\Resources\Dashboard\EstateRequestPreviewResource;
use App\Http\Resources\EstateExprensesResource;
use App\Http\Resources\EstateGroupNotesResource;
use App\Http\Resources\EstateGroupPlatformResource;
use App\Http\Resources\EstateNotesResource;
use App\Http\Resources\EstatePlatformResource;
use App\Http\Resources\EstateResourceV3;
use App\Http\Resources\FinancialBondResource;
use App\Http\Resources\FinancialMovementResource;
use App\Http\Resources\SingleEstateGroupPlatformResource;
use App\Http\Resources\SingleEstatePlatformResource;
use App\Http\Resources\TentPayUserNoteResource;
use App\Http\Resources\TentPayUserResource;
use App\Http\Resources\UserResource;

use App\Models\v3\AttachmentEstate;
use App\Models\v3\AttachmentPlanned;
use App\Models\v3\Bank;
use App\Models\v3\City;
use App\Models\v3\City3;
use App\Models\v3\ComfortEstate;
use App\Models\v3\District;
use App\Models\v3\Estate;
use App\Models\v3\EstateExpense;
use App\Models\v3\EstateGroupOwnerNote;
use App\Models\v3\EstateOwnerNote;
use App\Models\v3\EstateType;
use App\Models\v3\FinancialMovement;
use App\Models\v3\GroupEstate;
use App\Models\v3\Neighborhood;
use App\Models\v3\OprationType;
use App\Models\v3\PayContracts;
use App\Models\v3\RateEstate;
use App\Models\v3\Region;
use App\Models\v3\RentalContracts;
use App\Models\v3\RentContractFinancialMovement;
use App\Models\v3\TentPayUser;
use App\Models\v3\TentPayUserNote;
use App\Models\v4\Cities;
use App\Models\v4\EstateRequestPreview;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use App\User;
use Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use QL\QueryList;


class EstateController extends Controller
{

    public function video_extensions()
    {

        return array("3g2",
            "3gp",
            "aaf",
            "asf",
            "avchd",
            "avi",
            "drc",
            "flv",
            "m2v",
            "m3u8",
            "m4p",
            "m4v",
            "mkv",
            "mng",
            "mov",
            "mp2",
            "mp4",
            "MP4",
            "mpe",
            "mpeg",
            "mpg",
            "mpv",
            "mxf",
            "nsv",
            "ogg",
            "ogv",
            "qt",
            "rm",
            "rmvb",
            "roq",
            "svi",
            "vob",
            "webm",
            "wmv",
            "yuv");

    }

    public function myEstate(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        if ($request->get('emp_id')) {
            $estate = Estate::where('deleted_at', null)->where('user_id', $request->get('emp_id'));

            if ($request->get('search')) {
                $estate->where('id', $request->get('search'));
            }
        } else {
            $user = $request->user();
            $typeEstateArray = ['2', '4'];

            $user = User::with('employee')->find($user->id);
//            $estate = $user->estate();
            if ($user->account_type == 'company' && @$user->employee()->count() > 0 && @$user->employee()->whereHas('estate')->count() > 0) {
                $estate = Estate::where(function ($query) use ($user) {
                    $query->where('company_id', $user->id)
                        ->orWhere('user_id', $user->id);
                });
            } else {
                $estate = Estate::where('user_id', $user->id);
            }


            //  $estate = Estate::where('user_id',$user->id);


            if ($request->get('status')) {
                $estate = $estate->whereRaw('status LIKE "%completed%"');

            }

            //->where('available', '1');

            if ($request->get('city_id') && $request->get('city_id') != null) {

                $estate = explode(',', $request->get('city_id'));

                $estate->whereRaw('  city_id   IN ("' . $estate . '") ');

                //  $estate = $estate->whereIn('city_id', $estate);
            }

            if ($request->get('search') && $request->get('search') != null) {


                if ((filter_var($request->get('search'),
                            FILTER_VALIDATE_INT) !== false) && Estate::find($request->get('search'))) {
                    //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                    //   $query .= ' and id   = ' . $request->get('search');
                    $estate = $estate->whereRaw('  id =' . $request->get('search'));

                } elseif ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) == false)) {
                    /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                         ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                         ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
     */
                    /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                    $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                    //$estate = $estate->whereRaw(' interface  like ' . ' %'.$request->get('search').'' . ' % ');
                    $estate = $estate->where(function ($query) use ($request) {
                        $query->where('address', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('full_address', 'like', '%' . $request->get('search') . '%')
                            ->orWhere('estate_name', 'like', '%' . $request->get('search') . '%');
                    });
                    //   $estate = $estate->orWhere('rent_type', 'like', '%' . $request->get('search') . '%');
                    //  $estate = $estate->orWhere('estate_name', 'like', '%' . $request->get('search') . '%');


                    //$estate = $estate->orwhereRaw('  rent_type like  ' . '%'.$request->get('search').'' . ' % ');
                    // = $estate->orwhereRaw('  estate_name like  ' . '%'.$request->get('search').'' . ' % ');


                }
                if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false)) {
                    //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                    //   $query .= ' and id   = ' . $request->get('search');
                    $estate = $estate->whereRaw('  id =' . $request->get('search'));

                }


            }


            if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


                if (in_array($request->get('estate_type_id'), $typeEstateArray)) {

                    // $estate = $estate->whereIn('estate_type_id', $typeEstateArray);
                    $array = array_map('intval', $typeEstateArray);
                    // $array = implode(",", $array);
                    //   $query .= ' and city_id IN ' . $array;
                    $array = join(",", $array);
                    //  $array = '(' . $array . ')';


                    $estate = $estate->whereRaw('  estate_type_id   IN (2,4) ');
                    //  dd($estate->toSql());

                } else {
                    $estate = $estate->whereRaw('estate_type_id =' . $request->get('estate_type_id'));
                }


            }

            if ($user->account_type == 'company' && @$user->employee()->count() > 0 && @$user->employee()->whereHas('estate')->count() > 0) {

                $estate = $estate->where(function ($query) use ($user) {
                    $query->where('company_id', $user->id)
                        ->orWhere('user_id', $user->id);
                });
                //  return $this->hasMany(Estate::class, 'company_id', 'id');
            } else {
                $estate = $estate->where('user_id', $user->id);
                //   return $this->hasMany(Estate::class, 'user_id', 'id');
            }
        }


        $estate = $estate->orderByRaw(DB::Raw(' `estates`.`id` desc '));

        if ($request->per_page) {
            $size = $request->per_page;
        } else {
            $size = 15;
        }


        $estate = $estate->paginate($size);


        $estate = EstatePlatformResource::collection($estate)->response()->getData(true);

        if ($estate) {
            return response()->success(__("views.Estates"), $estate);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }

    public function addEstate(Request $request)
    {


        if (!$request->user()) {

            return response()->error(__('views.not authorized'));


        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'operation_type_id' => 'required|exists:opration_types,id',
            'estate_status' => 'sometimes|required|in:1,2,3',
            'estate_type_id' => 'required|exists:estate_types,id',
//            'rent_price' => 'required_if:operation_type_id,2',
            'rent_type' => 'required_if:operation_type_id,2',
//            'payment_value' => 'required_if:operation_type_id,2',
//            'total_price' => 'required_if:operation_type_id,!=,2',
            'lat' => 'sometimes|required',
            'lan' => 'sometimes|required',
            'estate_use_type' => 'required',
            'total_area' => 'required',
            'is_mortgage' => 'required',
            'is_obligations' => 'required',
            'is_saudi_building_code' => 'required',
            'owner_management_commission' => 'sometimes|required',
            'owner_management_commission_type' => 'sometimes|required',
            'bank_id' => 'required',
            'building_number' => 'required',
            'guard_name' => 'sometimes|required',
            'guard_mobile' => 'sometimes|required',
            'guard_identity' => 'sometimes|required',
            'owner_estate_name' => 'required',
            'owner_estate_mobile' => 'required',
            'postal_code' => 'required',
            'estate_name' => 'required',
            //  'instrument_date' => 'required',
            'photo' => 'required|array',
            'photo.*' => 'required|image|mimes:jpg,jpeg,png',
            'group_estate_id' => 'sometimes|required|exists:estate_groups,id',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $company_id = null;
        if ($user->employer_id != null) {
            $company_id = $user->id;
        } elseif ($user->account_type == 'company') {
            $company_id = $user->employer_id ?? $user->id;
        }
        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }
        //  checkPint("26.226648900000000000 50.203490600000000000");
        $cit = EstateType::where('id', $request->get('estate_type_id'))->first();
        $cit2 = OprationType::where('id', $request->get('operation_type_id'))->first();

        if ($request->city_id) {
            $request->merge([
                'city_id' => $request->city_id,
            ]);

        }

        if ($request->neighborhood_id) {
            $request->merge([
                'neighborhood_id' => $request->neighborhood_id,
            ]);
        }

        if ($request->city_id && !$request->neighborhood_id) {

            $city = Cities::find($request->city_id);
            if ($city) {
                $request->merge([
                    'full_address' => $city->name_ar . ',' . @$city->region->name,
                ]);
            }
        } elseif ($request->neighborhood_id) {
            $district = \App\Models\v4\District::find($request->neighborhood_id);
            if ($district) {
                $request->merge([
                    'full_address' => $district->name . ',' . @$district->city->name . ',' . @$district->city->region->name,
                ]);
            }
        } elseif (!$request->city_id && !$request->neighborhood_id) {
            if ($request->get('lat') && $request->get('lan')) {
                $location = $request->get('lat') . ' ' . $request->get('lan');
                $dis = checkPint("$location");
                if ($dis != null) {
                    $dis = \App\Models\v4\District::where('district_id', $dis)->first();
                    $request->merge([
                        'full_address' => $dis->name . ',' . @$dis->city->name . ',' . @$dis->city->region->name,
                        'company_id' => $company_id,
                    ]);
                    $request->merge([
                        'city_id' => $dis->city_id,
                        'neighborhood_id' => $dis->district_id,
                    ]);
                } else {
                    $request->merge([
                        'full_address' => $request->get('full_address'),
                    ]);
                }
            }
        }

        if ($request->advertiser_number) {
            if ($user->advertiser_number) {
                $advertiser_number = $user->advertiser_number;
            } else {
                $advertiser_number = $request->advertiser_number;
                $user->advertiser_number = $advertiser_number;
                $user->save();
            }
        }


        $request->merge([

            'user_id' => $user->id,
            'company_id' => $company_id,
            'estate_age' => $age,
            'in_fund_offer' => 1,
            'operation_type_name' => $cit2->name_ar,
            'estate_type_name' => $cit->name_ar,
            'os_type' => app('request')->header('deviceType'),
            //   'full_address' => $Neighborhood->name_ar.','.$city->name_ar.','.$Region->name_ar,
        ]);


        /*  if (isset(Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'])&& Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
              return response()->error(__("views.the location must be inside Saudi Arabia"));
          }*/

        $estate = Estate::create($request->only([
            'operation_type_id',
            'deleted_by_company',
            'company_id',
            'reason',
            'rent_type',
            'payment_value',
            'rent_price',
            'postal_code',
            'estate_name',
            'instrument_date',
            'estate_type_id',
            'owner_estate_name',
            'owner_estate_mobile',
            'instrument_number',
            'is_disputes',
            'is_complete',
            'instrument_file',
            'instrument_status',
            'pace_number',
            'planned_number',
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
            'state_id',
            'estate_use_type',
            'estate_dimensions',
            'is_mortgage',
            'is_obligations',
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
            'advertiser_license_number',
            'advertiser_email',
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
            'user_id',
            'company_id',
            'address',
            'full_address',
            'group_estate_id',
            'unit_counter',
            'owner_estate_name',
            'owner_estate_mobile',
            'owner_estate_mobile',
            'owner_birth_day',
            'additional_code',
            'estate_name',
            'instrument_date',
            'os_type',
            'city_name_request',
            'neighborhood_name_request',
            'owner_name',
            'owner_mobile',
            'advertiser_number',
            'advertiser_side',
            'advertiser_character',
            'license_number',
            'advertising_license_number',
            'brokerageAndMarketingLicenseNumber',
            'channels',
            'creation_date',
            'end_date',
        ]));


        $estate = Estate::find($estate->id);


        if ($request->hasFile('instrument_file')) {


            $path = $request->file('instrument_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        if ($request->hasFile('exclusive_contract_file')) {


            $path = $request->file('exclusive_contract_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;

        }


        if (($request->hasFile('attachment_planned'))) {
//////

            $xy = $request->file('attachment_planned');
            $first_image = '';
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');


                $atta = new AttachmentPlanned();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $atta->save();


            }


        }


        if ($request->get('estate_comforts')) {


            $comforts = explode(',', $request->get('estate_comforts'));

            for ($i = 0; $i < count($comforts); $i++) {
                $comfort = ComfortEstate::create([
                    'estate_id' => $estate->id,
                    'comfort_id' => $comforts[$i],

                ]);
            }

        }
        if (($request->hasFile('photo'))) {
//////

            $xy = $request->file('photo');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');

                $atta = AttachmentEstate::create([
                    'estate_id' => $estate->id,
                    'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                ]);
                //    $atta = new AttachmentEstate();
                //   $atta->estate_id = $estate->id;
                //    $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                //   $atta->save();
                if ($i == 0) {
                    $estate->first_image = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                    $estate->save();
                }


            }


        }


        if (($request->hasFile('video'))) {
//////

            $xy = $request->file('video');
            foreach ($xy as $i => $value) {
                $extension = $value->getClientOriginalExtension();
                $array = $this->video_extensions();
                if (in_array($extension, $array)) {
                    $path = $value->store('videos', 's3');

                    if ($path != null && $path != false) {


                        $atta = AttachmentEstate::create([
                            'estate_id' => $estate->id,
                            'type' => 'videos',
                            'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                        ]);

                        if ($i == 0) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                            $estate->save();
                        }
                    }
                }


            }


        }

        $bank = Bank::find($request->get('bank_id'));
        if ($bank) {
            $estate->bank_name = $bank->name;
        }
        $estate->save();
        $user->count_estate = $user->count_estate + 1;

        // $user->count_request =  $user->count_request+1;
        $user->save();
        $estate = Estate::find($estate->id);

        $city = City::where('serial_city', $request->get('city_id'))->first();
        if ($city) {
            $city->count_app_estate = $city->count_app_estate + 1;
            $city->save();
        }

        $neb = Neighborhood::where('neighborhood_serial', $request->get('neighborhood_id'))->first();
        if ($neb) {
            $neb->estate_counter = $neb->estate_counter + 1;
            $neb->save();
        }

        if ($estate->total_price) {
            $price = explode(',', $estate->total_price);
        } else {
            $price = [];
        }
        $area = explode(',', $estate->total_area);
        $full_number = '';

        $full_area = '';
        for ($i = 0; $i < count($price); $i++) {
            $full_number .= $price[$i];
        }
        for ($i = 0; $i < count($area); $i++) {
            $full_area .= $area[$i];
        }

        $estate->total_price_number = $full_number;
        $estate->total_area_number = $full_area;
        $estate->save();
        $estate = Estate::find($estate->id);

        $estate = EstatePlatformResource::collection([$estate]);

        return response()->success(__("views.Done"), $estate[0]);
        // return ['data' => $user];
    }

    public function updateEstate(Request $request, $id)
    {

        //  dd($request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        //   $estate = Estate::findOrFail($id);

        //  $estate = $user->estate()->find($id);
        $user = User::find($user->id);

        $estate = $user->estate()->find($id);
        if (!$estate) {
            return response()->error(__("views.not found"));
        }


//        $rules = Validator::make($request->all(), [
//            'group_estate_id' => 'sometimes|required|exists:estate_groups,id',
//
//
//        ]);
//        if ($rules->fails()) {
//            return JsonResponse::fail($rules->errors()->first(), 400);
//        }

        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }

        $cit = null;
        $cit2 = null;
        if ($request->get('estate_type_id')) {
            $cit = \App\Models\v3\EstateType::where('id', $request->get('estate_type_id'))->first();
        }
        if ($request->get('operation_type_id')) {
            $cit2 = \App\Models\v3\OprationType::where('id', $request->get('operation_type_id'))->first();
        }


        if ($cit != null) {
            $request->merge([

                'estate_type_name' => $cit->name_ar
            ]);
        }
        if ($cit2 != null) {
            $request->merge([

                'operation_type_name' => $cit2->name_ar,

            ]);
        }


        /*    $city = City::where('serial_city', $request->get('city_id'))->first();
            $Region = Region::where('id', $request->get('state_id'))->first();
            $Neighborhood = Neighborhood::where('neighborhood_serial', $request->get('neighborhood_id'))->first();

    */


        if ($request->city_id) {
            $request->merge([
                'city_id' => $request->city_id,
            ]);

        }

        if ($request->neighborhood_id) {
            $request->merge([
                'neighborhood_id' => $request->neighborhood_id,
            ]);
        }

        if ($request->city_id && !$request->neighborhood_id) {

            $city = Cities::find($request->city_id);
            if ($city) {
                $request->merge([
                    'full_address' => $city->name_ar . ',' . @$city->region->name,
                ]);
            }
        } elseif ($request->neighborhood_id) {
            $district = \App\Models\v4\District::find($request->neighborhood_id);
            if ($district) {
                $request->merge([
                    'full_address' => $district->name . ',' . @$district->city->name . ',' . @$district->city->region->name,
                ]);
            }
        } elseif (!$request->city_id && !$request->neighborhood_id) {
            if ($request->get('lat') && $request->get('lan')) {
                $location = $request->get('lat') . ' ' . $request->get('lan');
                $dis = checkPint("$location");
                if ($dis != null) {
                    $dis = \App\Models\v4\District::where('district_id', $dis)->first();
                    $request->merge([
                        'full_address' => $dis->name . ',' . @$dis->city->name . ',' . @$dis->city->region->name,
                        'company_id' => $company_id,
                    ]);
                    $request->merge([
                        'city_id' => $dis->city_id,
                        'neighborhood_id' => $dis->district_id,
                    ]);
                } else {
                    $request->merge([
                        'full_address' => $request->get('full_address'),
                    ]);
                }
            }
        }

        $request->merge([

            'user_id' => $user->id,
            'estate_age' => $age,
//            'status' => 'new',
            //'full_address' => $Neighborhood->name_ar . ',' . $city->name_ar . ',' . $Region->name_ar,

        ]);


        $estate = Estate::find($id)
            ->update($request->only([
                'operation_type_id',
                'deleted_by_company',
                'company_id',
                'reason',
                'payment_value',
                'rent_price',
                'postal_code',
                'estate_name',
                'instrument_date',
                'estate_type_id',
                'owner_estate_name',
                'owner_estate_mobile',
                'instrument_number',
                'is_disputes',
                'is_complete',

                'instrument_status',
                'pace_number',
                'planned_number',
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
                'state_id',
                'estate_use_type',
                'estate_dimensions',
                'is_mortgage',
                'is_obligations',
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
                'advertiser_license_number',
                'advertiser_email',
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
                'user_id',
                'company_id',
                'address',
                'full_address',
                'group_estate_id',
                'unit_counter',
                'owner_estate_name',
                'owner_estate_mobile',
                'owner_estate_mobile',
                'owner_birth_day',
                'additional_code',
                'estate_name',
                'instrument_date',
                'city_name_request',
                'neighborhood_name_request',
                'owner_name',
                'owner_mobile',
                'advertiser_number',
                'advertiser_side',
                'advertiser_character',
                'license_number',
                'advertising_license_number',
                'brokerageAndMarketingLicenseNumber',
                'channels',
                'creation_date',
                'end_date',
            ]));


        $estate = Estate::find($id);


        if ($request->get('estate_comforts')) {

            $comfort = ComfortEstate::where('estate_id', $id)->delete();
            $comforts = explode(',', $request->get('estate_comforts'));

            for ($i = 0; $i < count($comforts); $i++) {
                $comfort = ComfortEstate::create([
                    'estate_id' => $estate->id,
                    'comfort_id' => $comforts[$i],

                ]);
            }

        }
        if (($request->hasFile('photo'))) {
//////

            $att = AttachmentEstate::where('estate_id', $id)
                ->where('type', 'images')
                ->delete();


            $xy = $request->file('photo');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');
                $atta = AttachmentEstate::create([
                    'estate_id' => $estate->id,
                    'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                ]);

                //  $atta = new AttachmentEstate();
                //  $atta->estate_id = $estate->id;
                //  $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                //   $atta->save();
                if ($i == 0) {
                    $estate->first_image = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                    $estate->save();
                }


            }


        }
        if (($request->hasFile('video'))) {
//////


            $att = AttachmentEstate::where('estate_id', $id)
                ->where('type', 'videos')
                ->delete();


            $xy = $request->file('video');
            foreach ($xy as $i => $value) {
                $extension = $value->getClientOriginalExtension();
                $array = $this->video_extensions();
                if (in_array($extension, $array)) {
                    $path = $value->store('videos', 's3');

                    if ($path != null && $path != false) {


                        $atta = AttachmentEstate::create([
                            'estate_id' => $estate->id,
                            'type' => 'videos',
                            'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                        ]);

                        if ($i == 0) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                            $estate->save();
                        }
                    }
                }


            }


        }


        $bank = Bank::find($request->get('bank_id'));
        if ($bank) {
            $estate->bank_name = $bank->name;
        }
        if (($request->hasFile('attachment_planned'))) {
//////

            $att = AttachmentPlanned::where('estate_id', $id)->delete();
            $xy = $request->file('attachment_planned');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');


                $atta = new AttachmentPlanned();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $atta->save();


            }


        }
        if (($request->hasFile('instrument_file'))) {
//////

            $path = $request->File('instrument_file')->store('images', 's3');


            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


        }


        $estate->save();


        $estate = Estate::find($estate->id);


        $price = explode(',', $estate->total_price);
        $area = explode(',', $estate->total_area);
        $full_number = '';

        $full_area = '';
        for ($i = 0; $i < count($price); $i++) {
            $full_number .= $price[$i];
        }
        for ($i = 0; $i < count($area); $i++) {
            $full_area .= $area[$i];
        }

        $estate->total_price_number = $full_number;
        $estate->total_area_number = $full_area;
        $estate->save();
        $estate = Estate::find($estate->id);
        $estate = EstatePlatformResource::collection([$estate]);
        return response()->success(__("views.Update Successfully"), $estate[0]);
    }

    public function EstateStatus(Request $request, $id)
    {

        //  dd($request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        //   $estate = Estate::findOrFail($id);

        //  $estate = $user->estate()->find($id);
        $user = User::find($user->id);

        $estate = $user->estate()->find($id);
        if (!$estate) {
            return response()->error(__("views.not found"));
        }


        $rules = Validator::make($request->all(), [
            'status' => 'required|in:new,under_edit_publish',

        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $estate = Estate::find($id)
            ->update($request->only([
                'status'
            ]));


        $estate = Estate::find($id);


        $estate = EstatePlatformResource::collection([$estate]);
        return response()->success(__("views.Update Successfully"), $estate[0]);
    }

    public function deleteEstate($id, Request $request)
    {
        //  $estate = Estate::findOrFail($id);

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        /*  if ($estate->user_id != $user->id) {
              return response()->error(__("views.Cant Delete"));
          }*/


        $user = User::with('employee')->find($user->id);


        $estate = $user->estate()->find($id);


        if (!$estate) {
            return response()->error(__("views.not found"), []);
        }
        try {
            if ($estate) {

                $city = City::where('serial_city', $estate->city_id)->first();
                if ($city) {
                    $city->count_app_estate = $city->count_app_estate - 1;
                    $city->save();
                }
                $neb = Neighborhood::where('neighborhood_serial', $estate->neighborhood_id)->first();
                if ($neb) {
                    $neb->estate_counter = $neb->estate_counter - 1;
                    $neb->save();
                }

                //  $estate->delete();
                //  $estate->available = 0;
                //   $estate->save();


                $estate_note = EstateOwnerNote::where('estate_id', $id)->delete();
                $estate_exprensess = EstateExpense::where('estate_id', $id)->delete();


                if ($estate->delete()) {
                    $user->count_estate = $user->count_estate - 1;
                    $user->save();

                    $data = \App\Models\v3\RequestOffer::where('estate_id', $id)->get();
                    foreach ($data as $dataItem) {
                        $dataItem->delete();
                    }

                    $request_review = EstateRequestPreview::where('estate_id', $id)->get();
                    foreach ($request_review as $request_reviewItem) {
                        $request_reviewItem->delete();
                    }


                }

                return response()->success(__("views.Deleted Successfully"), []);
            }
        } catch (\Exception $exception) {
            return response()->error(__("views.not found"));
        }


    }


    public function single_estate($id, Request $request)
    {

        $EstateRequest = Estate::with('rent_contract', 'estate_group', 'EstateCostExpense', 'estate_notes', 'EstateFile', 'comforts', 'user')->find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        if (!$EstateRequest) {
            return response()->error("NOT Found", []);
        }


        //    $EstateRequest = SingleEstatePlatformResource::collection([$EstateRequest]);
        //   return response()->success(__("views.Estate"), $EstateRequest[0]);
        return response()->success(__("views.Estate"), $EstateRequest);
    }


    public function addEstateExpenses(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'estate_id' => 'required|exists:estates,id',
            'rent_contract_id' => 'sometimes|required|exists:rental_contracts,id',
            'due_date' => 'required',
            'statement_note' => 'required',
            'price' => 'required',
            'type' => 'required|in:rent_payment,electricity_expenses',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);

        $tent_id = '';
        if ($request->get('rent_contract_id')) {
            $rentContract = RentalContracts::find($request->get('rent_contract_id'));
            if ($rentContract) {
                $tent_id = $rentContract->tent_id;
            }
        }

        $request->merge([

            'user_id' => $user->id,
            'tent_id' => $tent_id,
            'estate_id' => $request->get('estate_id'),
            'cost_type' => $request->get('rent_contract_id') != null ? 'with_contract' : 'without_contract',


        ]);
        $estate = $user->estate()->find($request->get('estate_id'));

        if (!$estate) {
            return response()->error(__("views.not found"), []);
        }
        $estate_expense = EstateExpense::create($request->only([
            'estate_id',
            'user_id',
            'type',
            'due_date',
            'statement_note',
            'price',
            'rent_contract_id',
            'cost_type',


        ]));


        $estate_expense = EstateExpense::find($estate_expense->id);
        if ($estate_expense && $request->get('rent_contract_id')) {

            $rent_contract = RentalContracts::find($request->get('rent_contract_id'));
            $request->merge([

                'user_id' => $user->id,
                'client_id' => $rent_contract->tent_id,
                'estate_expenses_id' => $estate_expense->id,
                'owed_amount' => $request->get('price'),
                'paid_amount' => 0,
                'remaining_amount' => $request->get('price'),
                'status' => 'not_paid',
                'rental_contracts_id' => $request->get('rent_contract_id'),
                'rental_contract_invoice_id' => null,


            ]);
            $RentContractFinancialMovement = RentContractFinancialMovement::create($request->only([
                'rental_contracts_id',
                'client_id',
                'owed_amount',
                'paid_amount',
                'remaining_amount',
                'rental_contract_invoice_id',
                'estate_expenses_id',
                'statement',
                'status',
                'user_id',
                'remaining_amount',

            ]));
        }

        return response()->success(__("views.Estate_expense"), $estate_expense);
        // return ['data' => $user];
    }

    public function updateEstateExpenses($id, Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'due_date' => 'sometimes|required',
            'statement_note' => 'sometimes|required',
            'price' => 'sometimes|required',
            'type' => 'sometimes|required|in:rent_payment,electricity_expenses',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $estate_expense = EstateExpense::find($id);
        if (!$estate_expense) {
            return response()->error(__("views.not found"), []);
        }
        $request->merge([

            'user_id' => $user->id,
            'estate_id' => $request->get('estate_id'),


        ]);

        $estate_expense = $estate_expense->update($request->only([
            'type',
            'due_date',
            'statement_note',
            'price',
            'rent_contract_id',


        ]));


        $estate_expense = EstateExpense::find($id);


        return response()->success(__("views.Estate_expense"), $estate_expense);
        // return ['data' => $user];
    }

    public function delete_estate_expense($id, Request $request)
    {

        $EstateExpense = EstateExpense::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$EstateExpense) {
            return response()->error("NOT Found", []);
        }

        $user = $request->user();
        if ($EstateExpense) {
            $rent_contract = RentalContracts::find($request->get('rent_contract_id'));
            if ($rent_contract) {
                $request->merge([

                    'client_id' => $rent_contract->tent_id,


                ]);
            }
            $request->merge([

                'user_id' => $user->id,
                'estate_expenses_id' => $EstateExpense->id,
                'owed_amount' => 0,
                'paid_amount' => $EstateExpense->price,
                'remaining_amount' => 0,
                'status' => 'not_paid',
                'rental_contracts_id' => $EstateExpense->rent_contract_id,
                'rental_contract_invoice_id' => null,


            ]);


            $RentContractFinancialMovement = RentContractFinancialMovement::create($request->only([
                'rental_contracts_id',
                'client_id',
                'owed_amount',
                'paid_amount',
                'remaining_amount',
                'rental_contract_invoice_id',
                'estate_expenses_id',
                'statement',
                'status',
                'user_id',
                'remaining_amount',

            ]));
        }
        $EstateExpense->delete();
        return response()->success(__("views.Done"), []);
    }

    public function single_estate_expense($id, Request $request)
    {

        $EstateExpense = EstateExpense::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$EstateExpense) {
            return response()->error("NOT Found", []);
        }
        //  $estate_exprense = EstateExprensesResource::collection([$EstateExpense]);
        //->response()->getData(true);;

        //  return response()->success(__("views.Estate_expense"), $estate_exprense);
        return response()->success(__("views.Estate_expense"), $EstateExpense);
    }


    public function allEstateExpenses(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $user = User::with('employee')->find($user->id);


        $estate = $user->estate()->pluck('id');

        if (!count($estate)) {
            return response()->success(__("views.no_aqarz_found"), []);

            //  return response()->error(__("views.no_aqarz_found"), []);
        }
        $estate_exprense = EstateExpense::with('user', 'estate')->whereIn('estate_id', $estate->toArray());


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateExpense::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $estate_exprense = $estate_exprense->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                $estate_exprense = $estate_exprense->where('type', $request->get('search'));
                $estate_exprense = $estate_exprense->orwhere('due_date', $request->get('search'));


            }
            if ((filter_var($request->get('search'), FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $estate_exprense = $estate_exprense->whereRaw('  id =' . $request->get('search'));

            }


        }

        if ($request->get('estate_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $estate_exprense = $estate_exprense->whereRaw('  estate_id =' . $request->get('estate_id'));

        }
        if ($request->get('rent_contract_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $estate_exprense = $estate_exprense->whereRaw('  rent_contract_id =' . $request->get('rent_contract_id'));

        }
        $estate_exprense = $estate_exprense->orderByRaw(DB::Raw(' `estate_expenses`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $estate_exprense = $estate_exprense->paginate($size);

        $estate_exprense = EstateExprensesResource::collection($estate_exprense)->response()->getData(true);


        if ($estate_exprense) {
            return response()->success(__("views.Estate_expense"), $estate_exprense);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }


    public function addImgEstate(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }
        $rules = Validator::make($request->all(), [


            'photo' => 'required|array',
            'estate_id' => 'required|exists:estates,id',
            'photo.*' => 'image|mimes:jpg,jpeg,png'


        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = $request->user();

        //    dd($this->request->all());
        $user = User::find($user->id);


        $estate = $user->estate()
            ->with('EstateExpense', 'plannedFile', 'EstateFile', 'comforts', 'user')
            ->find($request->get('estate_id'));

        $idsArray = [];

        if ($user) {
            if (($request->hasFile('photo'))) {
//////

                $xy = $request->file('photo');
                foreach ($xy as $i => $value) {

                    $path = $value->store('images', 's3');

                    $atta = AttachmentEstate::create([
                        'estate_id' => $estate->id,
                        'file' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path,
                    ]);


                }


            }

            $atta = AttachmentEstate::whereIn('id', $idsArray)->pluck('id');


            if ($atta) {
                $estate = $user->estate()
                    ->with('EstateExpense', 'plannedFile', 'EstateFile', 'comforts', 'user')
                    ->find($request->get('estate_id'));
                return response()->success("Estate", $estate);

            } else {
                return response()->fail("Estate", []);

            }

        } else {
            return response()->fail("Estate", []);

        }


    }


    public function deleteImgEstate(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }
        $rules = Validator::make($request->all(), [


            'image_id' => 'required|exists:attachment_estate,id',
            'estate_id' => 'required|exists:estates,id',


        ]);


        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $user = $request->user();

        //    dd($this->request->all());
        $user = User::find($user->id);


        $estate = $user->estate()
            ->with('EstateExpense', 'plannedFile', 'EstateFile', 'comforts', 'user')
            ->find($request->get('estate_id'));

        if (!$estate) {
            return response()->error(__('views.not found'));
        }


        $atta = AttachmentEstate::findOrFail($request->get('image_id'));
        if ($atta) {


            $atta->delete();
            $estate = $user->estate()
                ->with('EstateExpense', 'plannedFile', 'EstateFile', 'comforts', 'user')
                ->find($request->get('estate_id'));
            return response()->success("Estate", $estate);


        }


    }


    public function addEstateNotes(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'estate_id' => 'required|exists:estates,id',
            'notes' => 'required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);


        $request->merge([

            'user_id' => $user->id,
            'estate_id' => $request->get('estate_id'),


        ]);
        $estate = $user->estate()->find($request->get('estate_id'));

        if (!$estate) {
            return response()->error(__("views.not found"), []);
        }
        $EstateOwnerNote = EstateOwnerNote::create($request->only([
            'estate_id',
            'user_id',
            'notes',


        ]));


        $EstateOwnerNote = EstateOwnerNote::find($EstateOwnerNote->id);


        return response()->success(__("views.Estate_notes"), $EstateOwnerNote);
        // return ['data' => $user];
    }

    public function updateEstateNotes($id, Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'notes' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $estate_notes = EstateOwnerNote::find($id);
        if (!$estate_notes) {
            return response()->error(__("views.not found"), []);
        }
        $request->merge([

            'user_id' => $user->id,


        ]);

        $estate_notes = $estate_notes->update($request->only([
            'notes',


        ]));


        $EstateOwnerNote = EstateOwnerNote::find($id);


        return response()->success(__("views.Estate_notes"), $EstateOwnerNote);

        // return ['data' => $user];
    }

    public function delete_estate_notes($id, Request $request)
    {

        $estate_notes = EstateOwnerNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$estate_notes) {
            return response()->error("NOT Found", []);
        }
        $estate_notes->delete();
        return response()->success(__("views.Done"), []);
    }


    public function single_estate_notes($id, Request $request)
    {

        $estate_notes = EstateOwnerNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$estate_notes) {
            return response()->error("NOT Found", []);
        }

        return response()->success(__("views.Estate_expense"), $estate_notes);
    }


    public function allEstateNotes(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        $typeEstateArray = ['2', '4'];

        $user = User::with('employee')->find($user->id);


        $estate = $user->estate()->pluck('id');

        if (!count($estate)) {
            return response()->error(__("views.not found"), []);
        }
        $estate_notes = EstateOwnerNote::with('user', 'estate')->whereIn('estate_id', $estate->toArray());


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateOwnerNote::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $estate_notes = $estate_notes->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                //  $estate_notes = $estate_notes->where('type', $request->get('search'));
                //    $estate_notes = $estate_notes->orwhere('due_date', $request->get('search'));


            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $estate_notes = $estate_notes->whereRaw('  id =' . $request->get('search'));

            }


        }

        if ($request->get('estate_id')) {
            $estate_notes = $estate_notes->whereRaw('  estate_id =' . $request->get('estate_id'));

        }


        $estate_notes = $estate_notes->orderByRaw(DB::Raw(' `estate_owner_notes`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $estate_notes = $estate_notes->paginate($size);

        $estate_notes = EstateNotesResource::collection($estate_notes)->response()->getData(true);;


        if ($estate_notes) {
            return response()->success(__("views.Estate_notes"), $estate_notes);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }


    public function allEstateGroup(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        $typeEstateArray = ['2', '4'];

        $user = User::with('employee')->find($user->id);


        $group = $user->group_estate();
        // $group = GroupEstate::where('user_id',$user->id);


        //->where('available', '1');

        if ($request->get('city_id') && $request->get('city_id') != null) {

            $estate = explode(',', $request->get('city_id'));

            $estate->whereRaw('  city_id   IN ("' . $estate . '") ');

            //  $estate = $estate->whereIn('city_id', $estate);
        }

        if ($request->get('estate_type_id') && $request->get('estate_type_id') != null) {


            if (in_array($request->get('estate_type_id'), $typeEstateArray)) {

                // $estate = $estate->whereIn('estate_type_id', $typeEstateArray);
                $array = array_map('intval', $typeEstateArray);
                // $array = implode(",", $array);
                //   $query .= ' and city_id IN ' . $array;
                $array = join(",", $array);
                //  $array = '(' . $array . ')';


                $group = $group->whereRaw('  estate_type_id   IN (2,4) ');
                //  dd($estate->toSql());

            } else {
                $group = $group->whereRaw('estate_type_id =' . $request->get('estate_type_id'));
            }


        }
        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && Estate::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $group = $group->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {

                $group = $group->Where('group_name', 'like', '%' . $request->get('search') . '%');
                //     $group = $group->orWhere('city_name', 'like', '%' . $request->get('search') . '%');
                //     $group = $group->orWhere('state_name', 'like', '%' . $request->get('search') . '%');
                //      $group = $group->orWhere('neighborhood_name', 'like', '%' . $request->get('search') . '%');
                //      $group = $group->orWhere('interface', 'like', '%' . $request->get('search') . '%');

            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $group = $group->whereRaw('  id =' . $request->get('search'));

            }


        }

        if ($user->account_type == 'company' && @$user->employee()->count() > 0 && @$user->employee()->whereHas('estate')->count() > 0) {

            $group = $group->where('company_id', $user->id);
            //  return $this->hasMany(Estate::class, 'company_id', 'id');
        } else {
            $group = $group->where('user_id', $user->id);
            //   return $this->hasMany(Estate::class, 'user_id', 'id');
        }


        $group = $group->orderByRaw(DB::Raw(' `estate_groups`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }


        $group = $group->paginate($size);

        $group = AllEstateGroupResource::collection($group)->response()->getData(true);

        if ($group) {
            return response()->success(__("views.groups"), $group);
        } else {
            return response()->success(__("views.not found"), []);
        }

    }

    public function addEstateGroup(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'group_name' => 'required',
            'owner_management_commission' => 'required',
            'owner_management_commission_type' => 'required',
            'building_number' => 'required',
            'city_name' => 'required',
            'neighborhood_name' => 'required',
            'postal_code' => 'required',
            'additional_code' => 'required',
            'owner_estate_name' => 'required',
            'lat' => 'required',
            'lan' => 'required',
            'owner_estate_mobile' => 'required',
            'unit_counter' => 'required',
            'unit_number' => 'required',
            'full_address' => 'required',
            'instrument_number' => 'required',
            'instrument_file' => 'required',
            'instrument_status' => 'required',
            'bank_id' => 'required',


            'guard_name' => 'required',
            'guard_mobile' => 'required',
            'guard_identity' => 'required',
            'interface' => 'required',
            'owner_birth_day' => 'required',
            'image' => 'required|image|mimes:jpg,jpeg,png',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);


        $company_id = null;
        if ($user->employer_id != null) {
            $company_id = $user->employer_id;
        } elseif ($user->account_type == 'company') {
            $company_id = $user->employer_id;
        }
        //  checkPint("26.226648900000000000 50.203490600000000000");


        if ($request->get('lat') && $request->get('lan')) {
            // City3 District  Region
            $location = $request->get('lat') . ' ' . $request->get('lan');
            $dis = checkPint("$location");


            if ($dis != null) {
                $dis = District::find($dis);
                $city = City3::where('city_id', $dis->city_id)->first();
                $Neighborhood = District::where('district_id', $dis->district_id)->first();

                $cityFund = City::Where('name_ar', 'like', '%' . $city->name_ar . '%')->first();
                $NeighborhoodFund = Neighborhood::Where('name_ar', 'like', '%' . $Neighborhood->name_ar . '%')->first();
                $Region = Region::where('id', $cityFund->state_id)->first();


                $neb_name = $Neighborhood ? $Neighborhood->name_ar : '--';
                $city_name = $city ? $city->name_ar : '--';
                $Region_name = $Region ? $Region->name_ar : '--';
                $full_address = $neb_name . ',' . $city_name . ',' . $Region_name;

                $request->merge([

                    'full_address' => $full_address,
                ]);

                if ($cityFund) {
                    $request->merge([

                        'city_id' => $cityFund->serial_city,


                    ]);
                }

                if ($NeighborhoodFund) {
                    $request->merge([


                        'neighborhood_id' => $NeighborhoodFund->neighborhood_serial,

                    ]);
                }


            } else {
                $request->merge([

                    'full_address' => $request->get('full_address'),
                ]);
            }
        }


        $request->merge([

            'user_id' => $user->id,
            'company_id' => $company_id,

        ]);


        $GroupEstate = GroupEstate::create($request->only([
            'group_name',
            'company_id',
            'owner_management_commission',
            'owner_management_commission_type',
            'building_number',
            'user_id',
            'city_name',
            'state_name',
            'neighborhood_name',
            'postal_code',
            'additional_code',
            'owner_estate_name',
            'lat',
            'lan',
            'owner_estate_mobile',
            'unit_counter',
            'unit_number',
            'status',
            'full_address',
            'instrument_number',

            'instrument_status',
            'bank_id',
            'bank_name',
            'guard_name',
            'guard_mobile',
            'guard_identity',
            'interface',
            'additional_code',
            'owner_birth_day',


        ]));


        $GroupEstate = GroupEstate::find($GroupEstate->id);


        if ($request->hasFile('image')) {


            $path = $request->file('image')->store('images', 's3');
            $GroupEstate->image = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }
        if ($request->hasFile('instrument_file')) {


            $path = $request->file('instrument_file')->store('images', 's3');
            $GroupEstate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        $bank = Bank::find($request->get('bank_id'));
        if ($bank) {
            $GroupEstate->bank_name = $bank->name;
        }
        $GroupEstate->save();
        // $user->count_estate = $user->count_estate + 1;
        // $user->count_request =  $user->count_request+1;
        // $user->save();
        $GroupEstate = GroupEstate::find($GroupEstate->id);

        $GroupEstate = EstateGroupPlatformResource::collection([$GroupEstate]);
        return response()->success(__("views.GroupEstate"), $GroupEstate[0]);
        // return ['data' => $user];
    }

    public function updateGroup(Request $request, $id)
    {

        //  dd($request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        //   $estate = Estate::findOrFail($id);

        //  $estate = $user->estate()->find($id);
        $user = User::find($user->id);

        $group_estate = $user->group_estate()->find($id);
        if (!$group_estate) {
            return response()->success(__("views.not found"), []);
        }


        $rules = Validator::make($request->all(), [


        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        if ($request->get('lat') && $request->get('lan')) {
            // City3 District  Region
            $location = $request->get('lat') . ' ' . $request->get('lan');
            $dis = checkPint("$location");


            if ($dis != null) {
                $dis = District::find($dis);
                $city = City3::where('city_id', $dis->city_id)->first();
                $Neighborhood = District::where('district_id', $dis->district_id)->first();

                $cityFund = City::Where('name_ar', 'like', '%' . $city->name_ar . '%')->first();
                $NeighborhoodFund = Neighborhood::Where('name_ar', 'like', '%' . $Neighborhood->name_ar . '%')->first();
                $Region = Region::where('id', $cityFund->state_id)->first();

                $neb_name = $Neighborhood ? $Neighborhood->name_ar : '--';
                $city_name = $city ? $city->name_ar : '--';
                $Region_name = $Region ? $Region->name_ar : '--';
                $full_address = $neb_name . ',' . $city_name . ',' . $Region_name;

                $request->merge([

                    'full_address' => $full_address,
                ]);

                if ($cityFund) {
                    $request->merge([

                        'city_id' => $cityFund->serial_city,


                    ]);
                }

                if ($NeighborhoodFund) {
                    $request->merge([


                        'neighborhood_id' => $NeighborhoodFund->neighborhood_serial,

                    ]);
                }


            } else {
                $request->merge([

                    'full_address' => $request->get('full_address'),
                ]);
            }
        } else {
            $request->merge([


                'full_address' => $request->get('full_address')

            ]);
        }

        $request->merge([

            'user_id' => $user->id,


        ]);


        $group_estate = GroupEstate::find($id)
            ->update($request->only([
                'group_name',
                'owner_management_commission',
                'owner_management_commission_type',
                'building_number',
                'user_id',
                'city_name',
                'state_name',
                'neighborhood_name',
                'postal_code',
                'additional_code',
                'owner_estate_name',
                'lat',
                'lan',
                'owner_estate_mobile',
                'unit_counter',
                'unit_number',
                'status',
                'full_address',
                'instrument_number',

                'instrument_status',
                'bank_id',
                'bank_name',
                'guard_name',
                'guard_mobile',
                'guard_identity',
                'interface',
                'additional_code',
                'owner_birth_day',
            ]));


        $group_estate = GroupEstate::find($id);


        if ($request->hasFile('image')) {


            $path = $request->file('image')->store('images', 's3');
            $group_estate->image = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }
        if ($request->hasFile('instrument_file')) {


            $path = $request->file('instrument_file')->store('images', 's3');
            $group_estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        $bank = Bank::find($request->get('bank_id'));
        if ($bank) {
            $group_estate->bank_name = $bank->name;
        }
        $group_estate->save();
        $group_estate = GroupEstate::find($id);
        $group_estate = EstateGroupPlatformResource::collection([$group_estate]);
        return response()->success(__("views.Update Successfully"), $group_estate[0]);
    }

    public function deleteGroup($id, Request $request)
    {
        //  $estate = Estate::findOrFail($id);

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        /*  if ($estate->user_id != $user->id) {
              return response()->error(__("views.Cant Delete"));
          }*/


        $user = User::with('employee')->find($user->id);


        $group_estate = $user->group_estate()->find($id);


        if (!$group_estate) {
            return response()->success(__("views.not found"), []);
        }
        try {
            $estate = Estate::where('group_estate_id', $id)
                ->update(['group_estate_id' => null]);
            $estate_group_note = EstateGroupOwnerNote::where('group_estate_id', $id)->delete();
            $group_estate->delete();
            return response()->success(__("views.Done"), []);
        } catch (\Exception $exception) {
            return response()->success(__("views.not found"), []);
        }


    }


    public function single_group_estate($id, Request $request)
    {

        $group_estate = GroupEstate::with('user', 'group_estate_notes', 'estate.rent_contract')->find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        if (!$group_estate) {
            return response()->success("NOT Found", []);
        }

        //   $group_estate = SingleEstateGroupPlatformResource::collection([$group_estate]);
        return response()->success(__("views.GroupEstate"), $group_estate);
    }


    public function addEstateGroupNotes(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'group_estate_id' => 'required|exists:estate_groups,id',
            'notes' => 'required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);


        $request->merge([

            'user_id' => $user->id,
            'group_estate_id' => $request->get('group_estate_id'),


        ]);
        $estate = $user->group_estate()->find($request->get('group_estate_id'));

        if (!$estate) {
            return response()->success(__("views.not_found_group_estate"), []);
        }
        $EstateGroupOwnerNote = EstateGroupOwnerNote::create($request->only([
            'group_estate_id',
            'user_id',
            'notes',


        ]));


        $EstateGroupOwnerNote = EstateGroupOwnerNote::find($EstateGroupOwnerNote->id);


        return response()->success(__("views.Estate_group_notes"), $EstateGroupOwnerNote);
        // return ['data' => $user];
    }

    public function updateEstateGroupNotes($id, Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'notes' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $estate_group_notes = EstateGroupOwnerNote::find($id);
        if (!$estate_group_notes) {
            return response()->success(__("views.not_found_estate_group_note"), []);
        }
        $request->merge([

            'user_id' => $user->id,


        ]);

        $estate_notes = $estate_group_notes->update($request->only([
            'notes',


        ]));


        $EstateGroupOwnerNote = EstateGroupOwnerNote::find($id);


        return response()->success(__("views.Estate_group_notes"), $EstateGroupOwnerNote);

        // return ['data' => $user];
    }

    public function delete_estate_group_notes($id, Request $request)
    {

        $estate_group_notes = EstateGroupOwnerNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$estate_group_notes) {
            return response()->success(__("views.not_found_estate_group_note"), []);
        }
        $estate_group_notes->delete();
        return response()->success(__("views.Done"), []);
    }

    public function single_estate_group_notes($id, Request $request)
    {

        $estate_group_notes = EstateGroupOwnerNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$estate_group_notes) {
            return response()->success(__("views.not_found_estate_group_note"), []);
        }

        return response()->success(__("views.Estate_group_expense"), $estate_group_notes);
    }


    public function allEstateGroupNotes(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $user = User::find($user->id);


        $group_estate = $user->group_estate()->pluck('id');


        if (!count($group_estate)) {
            return response()->success(__("views.not_found_estate_group"), []);
            //  return response()->error(__("views.not found"), []);
        }
        $estate_group_notes = EstateGroupOwnerNote::with('user', 'GroupEstate')->whereIn('group_estate_id', $group_estate->toArray());


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && EstateOwnerNote::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $estate_group_notes = $estate_group_notes->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                //  $estate_notes = $estate_notes->where('type', $request->get('search'));
                //    $estate_notes = $estate_notes->orwhere('due_date', $request->get('search'));


            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $estate_group_notes = $estate_group_notes->whereRaw('  id =' . $request->get('search'));

            }


        }
        if ($request->get('group_estate_id')) {
            $estate_group_notes = $estate_group_notes->whereRaw('  group_estate_id =' . $request->get('group_estate_id'));

        }


        $estate_group_notes = $estate_group_notes->orderByRaw(DB::Raw(' `estate_group_owner_notes`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $estate_group_notes = $estate_group_notes->paginate($size);


        $estate_group_notes = EstateGroupNotesResource::collection($estate_group_notes)->response()->getData(true);;

        if ($estate_group_notes) {
            return response()->success(__("views.Estate_group_notes"), $estate_group_notes);
        } else {
            return response()->success(__("views.not_found_estate_group_note"), []);
        }

    }


    public function AllTentPayUsers(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        $TentPayUser = TentPayUser::query()->where('user_id', $user->id);

        if (!$TentPayUser) {
            return response()->success(__("views.not_found_tent_pay_user"), []);
            // return response()->error(__("views.not found"), []);
        }


        //->where('available', '1');


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && TentPayUser::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $TentPayUser = $TentPayUser->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                $TentPayUser = $TentPayUser->Where('name', 'like', '%' . $request->get('search') . '%');
                $TentPayUser = $TentPayUser->orWhere('nationality', 'like', '%' . $request->get('search') . '%');

            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $TentPayUser = $TentPayUser->whereRaw('  id =' . $request->get('search'));

            }


        }


        $TentPayUser = $TentPayUser->orderByRaw(DB::Raw(' `tent_pay_users`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $TentPayUser = $TentPayUser->paginate($size);

        foreach ($TentPayUser as $TentPayUserItem) {
            $checkContract = RentalContracts::where('tent_id', $TentPayUserItem->id)->first();
            $checkPayContract = PayContracts::where('payer_id', $TentPayUserItem->id)->first();

            if ($checkContract && $TentPayUserItem->customer_character == 'tent') {
                $TentPayUserItem->estate_id = $checkContract->estate_id;
                $TentPayUserItem->count_month = $checkContract->count_month;
            } elseif ($checkPayContract && $TentPayUserItem->customer_character == 'payer') {

                $TentPayUserItem->estate_id = $checkPayContract->estate_id;
                $TentPayUserItem->count_month = null;
            } else {
                $TentPayUserItem->estate_id = null;
                $TentPayUserItem->count_month = null;
            }

        }
        $TentPayUser = AllTentPayUsersResource::collection($TentPayUser)->response()->getData(true);
        if ($TentPayUser) {
            return response()->success(__("views.TentPayUser"), $TentPayUser);
        } else {
            return response()->success(__("views.not_found_tent_pay_user"), []);
            // return response()->error(__("views.not found"), []);
        }

    }

    public function AllTentPayUsersList(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $TentPayUser = TentPayUser::query()->where('user_id', $user->id);

        /* if (!$TentPayUser) {
             return response()->error(__("views.not found"), []);
         }*/


        //->where('available', '1');


        if ($request->get('customer_character')) {
            // $TentPayUser = $TentPayUser->where(' customer_character,  like % ' . $request->get('customer_character') . ' % ');
            //  $TentPayUser = $TentPayUser->whereRaw(' customer_character  like % ' . $request->get('customer_character') . ' % ');
            $TentPayUser = $TentPayUser->Where('customer_character', 'like', '%' . $request->get('customer_character') . '%');

        }


        $TentPayUser = $TentPayUser->orderByRaw(DB::Raw(' `tent_pay_users`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $TentPayUser = $TentPayUser->paginate($size);


        $TentPayUser = TentPayUserResource::collection($TentPayUser);
        if ($TentPayUser) {
            return response()->success(__("views.TentPayUser"), $TentPayUser);
        } else {
            return response()->success(__("views.not_found_tent_pay_user"), []);
            //return response()->error(__("views.not found"), []);
        }

    }

    public function addTentPayUsers(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        if ($request->get('mobile')) {
            if (startsWith($request->get('mobile'), '0')) {
                $mobile = substr($request->get('mobile'), 1, strlen($request->get('mobile')));
            } else {
                if (startsWith($request->get('mobile'), '00')) {
                    $mobile = substr($request->get('mobile'), 2, strlen($request->get('mobile')));
                } else {
                    $mobile = trim($request->get('mobile'));
                }
            }

            $request->merge(['mobile' => $mobile]);
        }

        if ($request->get('other_mobile')) {
            if (startsWith($request->get('other_mobile'), '0')) {
                $other_mobile = substr($request->get('other_mobile'), 1, strlen($request->get('other_mobile')));
            } else {
                if (startsWith($request->get('other_mobile'), '00')) {
                    $other_mobile = substr($request->get('other_mobile'), 2, strlen($request->get('other_mobile')));
                } else {
                    $other_mobile = trim($request->get('other_mobile'));
                }
            }

            $request->merge(['other_mobile' => $other_mobile]);
        }


        $rules = Validator::make($request->all(), [


            'name' => 'required',
            'customer_character' => 'required',
            //  'mobile' => 'required',
            'mobile' => 'required|numeric|regex:/(5)[0-9]{8}/|digits:9',
            'other_mobile' => 'numeric|regex:/(5)[0-9]{8}/|digits:9',

            //'other_mobile' => 'required',
            'identification' => 'required',
            'tax_number' => '',
            'date_of_birth' => 'required',
            'collector_name' => 'required_if:customer_character,=,tent',
            'nationality' => 'required',
            'movement_type' => 'required_if:customer_character,=,,tent',
            'building_number' => 'required',
            'postal_code' => 'required',
            'additional_code' => 'required',
            'email' => 'required',
            'bank_id' => 'required',
            'account_number' => 'required',
            'phone' => 'required',
            'fax_number' => '',
            'address' => 'required',
            'mail_box' => 'required',
            'guarantor' => 'required_if:customer_character,=,tent',
            'amount_paid' => 'required_if:customer_character,=,payer',
//            'identification_photo' => 'required|image|mimes:jpg,jpeg,png',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);

        //  checkPint("26.226648900000000000 50.203490600000000000");


        $request->merge([

            'user_id' => $user->id,


        ]);


        $TentPayUser = TentPayUser::create($request->only([
            'name',
            'customer_character',
            'mobile',
            'other_mobile',
            'identification',
            'identification_photo',
            'tax_number',
            'date_of_birth',
            'collector_name',
            'nationality',
            'movement_type',
            'building_number',
            'postal_code',
            'additional_code',
            'email',
            'bank_id',
            'bank_name',
            'account_number',
            'phone',
            'fax_number',
            'address',
            'mail_box',
            'guarantor',
            'amount_paid',
            'user_id',


        ]));


        $TentPayUser = TentPayUser::find($TentPayUser->id);


        if ($request->hasFile('identification_photo')) {


            $path = $request->file('identification_photo')->store('images', 's3');
            $TentPayUser->identification_photo = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        $bank = Bank::find($request->get('bank_id'));
        if ($bank) {
            $TentPayUser->bank_name = $bank->name;
        }
        $TentPayUser->save();
        // $user->count_estate = $user->count_estate + 1;
        // $user->count_request =  $user->count_request+1;
        // $user->save();
        $TentPayUser = TentPayUser::find($TentPayUser->id);


        return response()->success(__("views.TentPayUser"), $TentPayUser);
        // return ['data' => $user];
    }

    public function updateTentPayUsers(Request $request, $id)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'name' => 'sometimes|required',
            'customer_character' => 'sometimes|required',
            // 'mobile' => 'sometimes|required',
            'mobile' => 'sometimes|required|numeric|regex:/(5)[0-9]{8}/|digits:9',
            'other_mobile' => 'sometimes|required|numeric|regex:/(5)[0-9]{8}/|digits:9',

            // 'other_mobile' => 'sometimes|required',
            'identification' => 'sometimes|required',
            'tax_number' => 'sometimes',
            'date_of_birth' => 'sometimes|required',
            'collector_name' => 'sometimes|required_if:customer_character,=,tent',
            'nationality' => 'sometimes|required',
            'movement_type' => 'sometimes|required_if:customer_character,=,,tent',
            'building_number' => 'sometimes|required',
            'postal_code' => 'sometimes|required',
            'additional_code' => 'sometimes|required',
            'email' => 'sometimes|required',
            'bank_id' => 'sometimes|required',
            'account_number' => 'sometimes|required',
            'phone' => 'sometimes|required',
            'fax_number' => 'sometimes|required',
            'address' => 'sometimes|required',
            'mail_box' => 'sometimes|required',
            'guarantor' => 'sometimes|required_if:customer_character,=,tent',
            'amount_paid' => 'sometimes|required_if:customer_character,=,payer',
            'identification_photo' => 'sometimes|required|image|mimes:jpg,jpeg,png',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);

        //  checkPint("26.226648900000000000 50.203490600000000000");


        $request->merge([

            'user_id' => $user->id,


        ]);
        $TentPayUser = TentPayUser::find($id);


        if (!$TentPayUser) {
            return response()->success(__("views.not_found_tent_pay_user"), []);

            //   return response()->error(__('views.not found'));
        }

        $TentPayUser = $TentPayUser->update($request->only([
            'name',
            'customer_character',
            'mobile',
            'other_mobile',
            'identification',
            'identification_photo',
            'tax_number',
            'date_of_birth',
            'collector_name',
            'nationality',
            'movement_type',
            'building_number',
            'postal_code',
            'additional_code',
            'email',
            'bank_id',
            'bank_name',
            'account_number',
            'phone',
            'fax_number',
            'address',
            'mail_box',
            'guarantor',
            'amount_paid',
            'user_id',


        ]));


        $TentPayUser = TentPayUser::find($id);


        if ($request->hasFile('identification_photo')) {


            $path = $request->file('identification_photo')->store('images', 's3');
            $TentPayUser->identification_photo = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        $bank = Bank::find($request->get('bank_id'));
        if ($bank) {
            $TentPayUser->bank_name = $bank->name;
        }
        $TentPayUser->save();
        // $user->count_estate = $user->count_estate + 1;
        // $user->count_request =  $user->count_request+1;
        // $user->save();
        $TentPayUser = TentPayUser::find($TentPayUser->id);


        return response()->success(__("views.TentPayUser"), $TentPayUser);
        // return ['data' => $user];
    }

    public function DeleteTentPayUsers($id, Request $request)
    {
        //  $estate = Estate::findOrFail($id);

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        /*  if ($estate->user_id != $user->id) {
              return response()->error(__("views.Cant Delete"));
          }*/


        $TentPayUser = TentPayUser::find($id);


        if (!$TentPayUser) {
            return response()->success(__("views.not_found_tent_pay_user"), []);

            //  return response()->error(__("views.not found"), []);
        }
        try {

            $TentPayUser->delete();
            return response()->success(__("views.Done"), []);
        } catch (\Exception $exception) {
            return response()->error(__("views.not found"));
        }


    }


    public function SingleTentPayUsers($id, Request $request)
    {

        $TentPayUser = TentPayUser::with('tent_pay_notes')
            ->with('tent_contract')
            ->with('pay_contract')
            ->find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();

        if (!$TentPayUser) {
            return response()->success(__("views.not_found_tent_pay_user"), []);

            // return response()->error("NOT Found", []);
        }


        return response()->success(__("views.TentPayUser"), $TentPayUser);
    }


    public function addTentPayUserNotes(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [

            'tent_pay_user_id' => 'required|exists:tent_pay_users,id',
            'notes' => 'required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);


        $request->merge([

            'user_id' => $user->id,
            'tent_pay_user_id' => $request->get('tent_pay_user_id'),


        ]);
        $TentPayUser = TentPayUser::find($request->get('tent_pay_user_id'));

        if (!$TentPayUser) {
            return response()->error(__("views.not found"), []);
        }
        $TentPayUserNote = TentPayUserNote::create($request->only([
            'tent_pay_user_id',
            'user_id',
            'notes',


        ]));


        $TentPayUserNote = TentPayUserNote::find($TentPayUserNote->id);


        return response()->success(__("views.TentPayUserNote"), $TentPayUserNote);
        // return ['data' => $user];
    }

    public function updateTentPayUserNotes($id, Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $rules = Validator::make($request->all(), [


            'notes' => 'sometimes|required',


        ]);

        //  Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = User::find($user->id);
        $TentPayUserNote = TentPayUserNote::find($id);
        if (!$TentPayUserNote) {
            return response()->success(__("views.not_found_tent_pay_user_note"), []);

            //    return response()->error(__("views.not found"), []);
        }
        $request->merge([

            'user_id' => $user->id,


        ]);

        $TentPayUserNote = $TentPayUserNote->update($request->only([
            'notes',


        ]));


        $TentPayUserNote = TentPayUserNote::find($id);


        return response()->success(__("views.TentPayUserNote"), $TentPayUserNote);

        // return ['data' => $user];
    }

    public function DeleteTentPayUserNotes($id, Request $request)
    {

        $TentPayUserNote = TentPayUserNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$TentPayUserNote) {
            return response()->success(__("views.not_found_tent_pay_user_note"), []);

            return response()->error("NOT Found", []);
        }
        $TentPayUserNote->delete();
        return response()->success(__("views.Done"), []);
    }

    public function SingleTentPayUserNotes($id, Request $request)
    {

        $TentPayUserNote = TentPayUserNote::find($id);
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }


        if (!$TentPayUserNote) {
            return response()->success(__("views.not_found_tent_pay_user_note"), []);

            return response()->error("NOT Found", []);
        }

        return response()->success(__("views.TentPayUserNote"), $TentPayUserNote);
    }


    public function allTentPayUserNotes(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $user = User::find($user->id);


        $TentPayUser = TentPayUser::pluck('id');


        if (!count($TentPayUser)) {
            return response()->success(__("views.not_found_tent_pay_user"), []);

            //    return response()->error(__("views.not found"), []);
        }
        $TentPayUserNote = TentPayUserNote::whereIn('tent_pay_user_id', $TentPayUser->toArray());


        if ($request->get('search') && $request->get('search') != null) {


            if ((filter_var($request->get('search'),
                        FILTER_VALIDATE_INT) !== false) && TentPayUserNote::find($request->get('search'))) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $TentPayUserNote = $TentPayUserNote->whereRaw('  id =' . $request->get('search'));

            } elseif ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) == false)) {
                /* $Mechanic = $Mechanic->where('finishing_type', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('interface', 'like', '%' . $request->get('search') . '%')
                     ->orwhere('rent_type', 'like', '%' . $request->get('search') . '%');
 */
                /*$query .= ' and interface   like % ' . $request->get('search') . ' % ';
                $query .= ' or rent_type   like % ' . $request->get('search') . ' % ';*/
                //  $estate_notes = $estate_notes->where('type', $request->get('search'));
                //    $estate_notes = $estate_notes->orwhere('due_date', $request->get('search'));


            }
            if ((filter_var($request->get('search'),
                    FILTER_VALIDATE_INT) !== false)) {
                //    $Mechanic = $Mechanic->where('id', $request->get('search'));
                //   $query .= ' and id   = ' . $request->get('search');
                $TentPayUserNote = $TentPayUserNote->whereRaw('  id =' . $request->get('search'));

            }


        }
        if ($request->get('tent_pay_user_id')) {
            $TentPayUserNote = $TentPayUserNote->whereRaw('  tent_pay_user_id =' . $request->get('tent_pay_user_id'));

        }

        $TentPayUserNote = $TentPayUserNote->orderByRaw(DB::Raw(' `tent_pay_user_notes`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $TentPayUserNote = $TentPayUserNote->paginate($size);

        $TentPayUserNote = TentPayUserNoteResource::collection($TentPayUserNote)->response()->getData(true);


        if ($TentPayUserNote) {
            return response()->success(__("views.TentPayUserNote"), $TentPayUserNote);
        } else {
            return response()->success(__("views.not_found_tent_pay_user_note"), []);

            // return response()->error(__("views.not found"), []);
        }

    }

    public function financial_movements(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();
        $typeEstateArray = ['2', '4'];

        $user = User::with('employee')->find($user->id);


        $estate = $user->estate()->pluck('id');

        if (!count($estate)) {
            return response()->success(__("views.no_aqarz_found"), []);

            //    return response()->error(__("views.not found"), []);
        }
        $FinancialMovement = FinancialMovement::query();


        if ($request->get('estate_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $FinancialMovement = $FinancialMovement->whereRaw('  estate_id =' . $request->get('estate_id'));

        }
        if ($request->get('rent_contract_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $FinancialMovement = $FinancialMovement->whereRaw('  customer_id	 =' . $request->get('customer_id'));

        }
        if ($request->get('from_date') && $request->get('to_date')) {


            $FinancialMovement = $FinancialMovement->whereDate(
                'created_at',
                '>=',
                Carbon::parse($request->get('from_date'))
            );
            $FinancialMovement = $FinancialMovement->whereDate(
                'created_at',
                '<=',
                Carbon::parse($request->get('to_date'))
            );
        }
        $FinancialMovement = $FinancialMovement->orderByRaw(DB::Raw(' `financial_movements`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $FinancialMovement = $FinancialMovement->paginate($size);

        $FinancialMovement = FinancialMovementResource::collection($FinancialMovement)->response()->getData(true);;

        if ($FinancialMovement) {
            return response()->success(__("views.FinancialMovement"), $FinancialMovement);
        } else {
            return response()->success(__("views.no_FinancialMovement"), []);
            // return response()->error(__("views.not found"), []);
        }

    }

    public function account_statement(Request $request)
    {
        if (!$request->user()) {
            //return JsonResponse::fail('Credentials not match', 401);
            return response()->error(__('views.not authorized'));
            //return JsonResponse::fail(__('views.not authorized'));

        }

        $user = $request->user();


        $user = User::with('employee')->find($user->id);


        $account_statement = FinancialMovement::query()->where('user_id', $user->id);
        $account_statement_sum_owed_money = FinancialMovement::sum('owed_money');
        $account_statement_sum_paid_money = FinancialMovement::sum('paid_money');
        $account_statement_sum_total_money = $account_statement_sum_owed_money + $account_statement_sum_paid_money;


        if ($request->get('estate_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $account_statement = $account_statement->whereRaw('  estate_id =' . $request->get('estate_id'));

        }
        if ($request->get('customer_id')) {
            //    $Mechanic = $Mechanic->where('id', $request->get('search'));
            //   $query .= ' and id   = ' . $request->get('search');
            $account_statement = $account_statement->whereRaw('  customer_id	 =' . $request->get('customer_id'));

        }
        if ($request->get('from_date') && $request->get('to_date')) {


            $account_statement = $account_statement->whereDate(
                'created_at',
                '>=',
                Carbon::parse($request->get('from_date'))
            );
            $account_statement = $account_statement->whereDate(
                'created_at',
                '<=',
                Carbon::parse($request->get('to_date'))
            );
        }
        $account_statement = $account_statement->orderByRaw(DB::Raw(' `financial_movements`.`id` desc '));

        if ($request->size) {
            $size = $request->size;
        } else {
            $size = 15;
        }

        $account_statement = $account_statement->paginate($size);

        $account_statement = AllAccountStatementResource::collection($account_statement)->response()->getData(true);

        if ($account_statement) {
            return response()->success(__("views.AccountStatement"), [
                    'account_statement' => $account_statement,
                    'account_statement_sum_owed_money' => $account_statement_sum_owed_money,
                    'account_statement_sum_paid_money' => $account_statement_sum_paid_money,
                    'account_statement_sum_total_money' => $account_statement_sum_total_money
                ]
            );
        } else {
            return response()->success(__("views.no_account_statement"), []);
            //  return response()->error(__("views.not found"), []);
        }

    }

    public function estate_request_preview(Request $request)
    {

        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $user = $request->user();


        $page_number = $request->page_number ? $request->page_number : 15;
        $EstateRequestPreview = EstateRequestPreview::query()->where('owner_id', $user->id);

        if ($request->estate_id) {
            $EstateRequestPreview->where('estate_id', $request->estate_id);
        }

        if ($request->user_id) {
            $EstateRequestPreview->where('user_id', $request->user_id);

        }

        if ($request->owner_id) {
            $EstateRequestPreview->where('owner_id', $request->owner_id);
        }

        $EstateRequestPreview = $EstateRequestPreview->orderBy('id', 'desc')->paginate($page_number);

        return response()->success(__('طلبات معاينة العقارات'), \App\Http\Resources\Platform\EstateRequestPreviewResource::collection($EstateRequestPreview)->response()->getData(true));

    }

    public function estate_request_preview_show(Request $request , $id)
    {

        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }

        $EstateRequestPreview = EstateRequestPreview::query()->find($id);

        return response()->success(__('طلبات معاينة العقارات'), new \App\Http\Resources\Platform\EstateRequestPreviewResource($EstateRequestPreview));

    }


}
