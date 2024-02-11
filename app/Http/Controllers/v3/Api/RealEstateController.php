<?php

namespace App\Http\Controllers\v3\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Jobs\NotificationProvider;
use App\Jobs\OtpJob;
use App\Models\v3\AttachmentEstate;
use App\Models\v3\AttachmentPlanned;
use App\Models\v3\City;
use App\Models\v3\City3;
use App\Models\v3\ComfortEstate;
use App\Models\v3\DeferredInstallment;
use App\Models\v3\District;
use App\Models\v3\Estate;
use App\Models\v3\EstateRequest;
use App\Models\v3\Finance;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\Neighborhood;
use App\Models\v3\RateRequest;
use App\Models\v3\RateRequestTypeRate;
use App\Models\v3\Region;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class RealEstateController extends Controller
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

    public function deferredInstallment(Request $request)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [


            /*   'operation_type_id'      => 'sometimes|required',
               'estate_type_id'         => 'sometimes|required',
               'contract_interval'      => 'sometimes|required',
               'rent_price'             => 'sometimes|required',
               'tenant_name'            => 'sometimes|required',
               'tenant_mobile'          => 'sometimes|required',
            //   'tenant_identity_number' => 'sometimes|required',
            //   'tenant_identity_file'   => 'sometimes|required',
               'tenant_birthday'        => 'sometimes|required',
              // 'tenant_city_id'         => 'sometimes|required',
               'tenant_job_type'        => 'sometimes|required',
               'tenant_total_salary'    => 'sometimes|required',
            //   'building_number'        => 'sometimes|required',
          //     'street_name'            => 'sometimes|required',
         //      'neighborhood_name'      => 'sometimes|required',
         //      'building_city_name'     => 'sometimes|required',
           //    'postal_code'            => 'sometimes|required',
        //       'unit_name'              => 'sometimes|required',
   */

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request->merge([

            'user_id' => $user->id,

        ]);
        $DeferredInstallment = DeferredInstallment::create($request->only([
            'operation_type_id',
            'estate_type_id',
            'contract_interval',
            'financing_body',
            'employer_name',
            'previous_financial_failures',
            'stumble_amount',
            'engagements',
            'personal_financing_engagements',
            'lease_finance_engagements',
            'credit_card_engagements',

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

            'is_salary_adapter_on'
        ]));


        $DeferredInstallment = DeferredInstallment::find($DeferredInstallment->id);


        /* if ($request->hasFile('contract_file')) {


                $extension = $request->file('contract_file')->getClientOriginalExtension();
             $photo = str_random(32) . '.' . $extension;

             $destinationPath = base_path('public/DeferredInstallment/');
             $request->file('contract_file')->move($destinationPath, $photo);
             $DeferredInstallment->contract_file = 'public/DeferredInstallment/' . $photo;
         }*/


        $DeferredInstallment = DeferredInstallment::find($DeferredInstallment->id);
        return response()->success(__("views.DeferredInstallment"), $DeferredInstallment);
        // return ['data' => $user];
    }

    public function finance(Request $request)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'name' => 'sometimes|required',
            'identity_number' => 'sometimes|required',
            'identity_file' => 'sometimes|required',
            'job_type' => 'sometimes|required',


            'mobile' => 'sometimes|required',
            'city_id' => 'sometimes|required',
            'job_start_date' => 'sometimes|required',
            'total_salary' => 'sometimes|required',
            'birthday' => 'sometimes|required',


            'operation_type_id' => 'sometimes|required',
            'estate_type_id' => 'sometimes|required',

            'finance_interval' => 'sometimes|required',
            'estate_price' => 'sometimes|required',
            'available_amount' => 'sometimes|required',
            'solidarity_partner' => 'sometimes|required',
            'solidarity_salary' => 'sometimes|required',
            //  'job_start_date'     => 'required',//

            //    'engagements' => 'required',


            'national_address' => 'sometimes|required',
            'national_address_display' => 'sometimes|required',
            'is_subsidized_property' => 'sometimes|required',
            'is_first_home' => 'sometimes|required',
            //    'building_number'       => 'required',
            //    'street_name'           => 'required',
            //    'neighborhood_name'     => 'required',
            //   'building_city_name'    => 'required',
            //    'postal_code'           => 'required',
            //    'additional_number'           => 'required',
            //    'unit_number'           => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $request->merge([

            'user_id' => $user->id,
            'status' => 3,
        ]);
        $Finance = Finance::create($request->only([
            'operation_type_id',
            'estate_type_id',
            'job_type',

            'finance_interval',
            'job_start_date',
            'estate_price',
            'engagements',
            'city_id',
            'name',
            'identity_number',
            'mobile',
            'total_salary',
            'available_amount',
            'national_address',
            'engagements',
            'national_address',
            'building_number',
            'street_name',
            'neighborhood_name',
            'building_city_name',
            'postal_code',
            'additional_number',
            'unit_number',
            'solidarity_salary',
            'solidarity_partner',
            'status',
            'user_id',
            'national_address_display',
            'city_id',
            'bank_id',
            'birthday',
            'is_subsidized_property',
            'is_first_home',
            'estate_id'

        ]));


        $Finance = Finance::find($Finance->id);


        if ($request->hasFile('national_address_file')) {


            /* $extension = $request->file('national_address_file')->getClientOriginalExtension();
             $photo = str_random(32) . '.' . $extension;

             $destinationPath = base_path('public/Finance/');
             $request->file('national_address_file')->move($destinationPath, $photo);
             $Finance->national_address_file = 'public/Finance/' . $photo;

 */


            $path = $request->file('national_address_file')->store('images', 's3');
            $Finance->tenant_identity_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }
        if ($request->hasFile('identity_file')) {

            /*
                        $extension = $request->file('identity_file')->getClientOriginalExtension();
                        $photo = str_random(32) . '.' . $extension;

                        $destinationPath = base_path('public/Finance/');
                        $request->file('identity_file')->move($destinationPath, $photo);
                        $Finance->identity_file = 'public/Finance/' . $photo;


            */


            $path = $request->file('identity_file')->store('images', 's3');
            $Finance->identity_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }

        $Finance->save();

        $Finance = Finance::find($Finance->id);
        return response()->success(__("views.Finance"), $Finance);
        // return ['data' => $user];
    }


    public function getState(Request $request)
    {
        $location = $request->get('lat') . ' ' . $request->get('lan');
        $dis = checkPint("$location");


        if ($dis != null) {
            $city = City3::where('city_id', $dis->city_id)->first();
            $Neighborhood = District::where('district_id', $dis->district_id)->first();

            $cityFund = City::Where('name_ar', 'like', '%' . $city->name_ar . '%')->first();
            $NeighborhoodFund = Neighborhood::Where('name_ar', 'like', '%' . $Neighborhood->name_ar . '%')->first();
            $Region = Region::where('id', $cityFund->state_id)->first();
            return response()->success(__("views.State"), $Region);

            $neb_name = $Neighborhood ? $Neighborhood->name_ar : '--';
            $city_name = $city ? $city->name_ar : '--';
            $Region_name = $Region ? $Region->name_ar : '--';
        } else {
            return response()->error(__("تعذر الوصول الي المنطقة"));
        }
    }

    public function addEstate(Request $request)
    {

        //     return response()->success(__("views.Finance"), $request->all());

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [


            'operation_type_id' => 'required',


            'estate_type_id' => 'required|exists:estate_types,id',
            //  'state_id' => 'required|exists:regions,id',
            // 'city_id' => 'required|exists:cities,serial_city',
            //  'neighborhood_id' => 'required|exists:neighborhoods,neighborhood_serial',
            'lat' => 'sometimes|required',
            'lan' => 'sometimes|required',

            'estate_use_type' => 'required',
            'total_area' => 'required',
            //'estate_dimensions' => 'required',
            //  'interface' => 'required',
            'is_mortgage' => 'required',
            'is_obligations' => 'required',
            'is_saudi_building_code' => 'required',
            'advertiser_side' => 'required',
            'advertiser_character' => 'required',
            'owner_name' => 'sometimes|required',
            'owner_mobile' => 'sometimes|required',


            'photo' => 'required|array',
            'photo.*' => 'required|image|mimes:jpg,jpeg,png',


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
            $company_id = $user->id;
        }


        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }
        //  checkPint("26.226648900000000000 50.203490600000000000");
        $cit = \App\Models\v3\EstateType::where('id', $request->get('estate_type_id'))->first();
        $cit2 = \App\Models\v3\OprationType::where('id', $request->get('operation_type_id'))->first();


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
                    'company_id' => $company_id,
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
            'estate_age' => $age,
            'in_fund_offer' => 1,
            'operation_type_name' => $cit2->name_ar,
            'estate_type_name' => $cit->name_ar,
            //   'full_address' => $Neighborhood->name_ar.','.$city->name_ar.','.$Region->name_ar,
        ]);


        /*  if (isset(Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'])&& Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
              return response()->error(__("views.the location must be inside Saudi Arabia"));
          }*/

        $estate = Estate::create($request->only([
            'elevators_number',
            'company_id',
            'parking_spaces_numbers',
            'unit_number',
            'is_disputes',


            'operation_type_id',
            'is_mortgage',
            'is_obligations',
            'touching_information',
            'is_saudi_building_code',
            'advertiser_side',
            'advertiser_character',
            'estate_use_type',
            'estate_type_id',
            'instrument_number',
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
            'rooms_number',
            'bathrooms_number',
            'boards_number',
            'kitchen_number',
            'dining_rooms_number',
            'finishing_type',
            'interface',
            'social_status',
            'lat',
            'lan',
            'note',
            'status',
            'user_id',
            'is_rent',
            'rent_type',
            'is_resident',
            'is_checked',
            'is_insured',
            'neighborhood_id',
            'city_id',
            'state_id',
            'address',
            'in_fund_offer',
            'estate_dimensions',
            'obligations_information',
            'bedroom_number',
            'rooms_number',
            'rent_price',
            'operation_type_name',
            'estate_type_name',
            'first_image',
            'full_address',
            'is_rent_installment',
            'rent_installment_price',
            'unit_counter',
            'advertiser_license_number',
            'advertiser_email',
            'advertiser_number',
            'company_id',


        ]));


        $estate = Estate::find($estate->id);

        /*
                if ($request->hasFile('video') && $request->File('video') != null) {


                    $extension = $request->file('video')->getClientOriginalExtension();
                    $array = $this->video_extensions();

                    if (in_array($extension, $array)) {
                        $path = $request->file('video')->store('video', 's3');

                        if ($path != null && $path != false) {
                            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                        }
                    }


                }
        */
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


        /*  if (($request->get('attachment_estate'))) {
  //////
              $arrayImg = explode(',', $request->get('attachment_estate'));
              $attachment = AttachmentEstate::whereIn('id', $arrayImg)->update(['estate_id' => $estate->id]);


          }*/

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

        $estate->save();
        $user->count_estate = $user->count_estate + 1;
        // $user->count_request =  $user->count_request+1;
        $user->save();
        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);

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

        return response()->success(__("views.Estate"), $estate);
        // return ['data' => $user];
    }


    public function updateEstate(Request $request, $id)
    {

        //  dd($request->all());

        $user = auth()->user();


        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        //   $estate = Estate::findOrFail($id);

        //  $estate = $user->estate()->find($id);
        $user = User::find($user->id);

        $estate = $user->estate()->find($id);
        if (!$estate) {
            return response()->error(__("views.not found"));
        }


        $rules = Validator::make($request->all(), [


        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

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


        if ($request->get('lat') && $request->get('lan')) {
            // City3 District  Region
            $location = $request->get('lat') . ' ' . $request->get('lan');
            $dis = checkPint("$location");


            if ($dis != null) {
                $dis = District::find($dis);
                $city = City3::where('city_id', $dis->city_id)->first();
                //  $city = City3::where('city_id', $dis->city_id)->first();
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
            'estate_age' => $age,
            //'full_address' => $Neighborhood->name_ar . ',' . $city->name_ar . ',' . $Region->name_ar,

        ]);


        $estate = Estate::find($id)
            ->update($request->only([
                'operation_type_id',
                'elevators_number',
                'unit_number',
                'is_disputes',
                'parking_spaces_numbers',
                'operation_type_name',
                'estate_type_name',
                'is_mortgage',
                'is_obligations',
                'touching_information',
                'is_saudi_building_code',
                'advertiser_side',
                'advertiser_character',
                'estate_use_type',
                'estate_type_id',
                'instrument_number',
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
                'rooms_number',
                'bathrooms_number',
                'boards_number',
                'kitchen_number',
                'dining_rooms_number',
                'finishing_type',
                'interface',
                'social_status',
                'lat',
                'lan',
                'note',
                'status',
                'user_id',
                'is_rent',
                'rent_type',
                'is_resident',
                'is_checked',
                'is_insured',
                'neighborhood_id',
                'city_id',
                'state_id',
                'address',
                'in_fund_offer',
                'estate_dimensions',
                'obligations_information',
                'bedroom_number',
                'rooms_number',
                'rent_price',
                'full_address',
                'is_rent_installment',
                'rent_installment_price',
                'unit_counter',
                'advertiser_license_number',
                'advertiser_email'
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

        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);


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

        return response()->success(__("views.Update Successfully"), $estate);
    }

    public function deleteEstate($id, Request $request)
    {
        //  $estate = Estate::findOrFail($id);
        $user = auth()->user();


        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

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
                if ($estate->company_id == $user->id) {
                    $estate->deleted_by_company = 1;
                    $estate->save();
                }
                $estate->reason = $request->get('reason');
                $estate->save();
                if ($estate->delete()) {
                    $user->count_estate = $user->count_estate - 1;
                    $user->save();
                }

                return response()->success(__("views.Deleted Successfully"), []);
            }
        } catch (\Exception $exception) {
            return response()->error(__("views.not found"));
        }


    }

    // تحديث تاريخ العقار بحيث يظهر بالاعلى
    public function MakeUpEstate($id)
    {

        $user = auth()->user();


        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        //   $estate = Estate::findOrFail($id);
        $user = User::find($user->id);
        $estate = $user->estate()->find($id);


        if (!$estate) {
            return response()->error(__("views.not found"));
        }

        //$estate = $user->estate()->find($id);
        try {
            if ($estate) {
                $estate = Estate::find($estate->id);
                $estate->touch();
                return response()->success("تم اعادة النشر بنجاح", []);
            }
        } catch (\Exception $exception) {
            //  return response()->error($exception->getMessage());
            return response()->error(__("views.not found"));
        }

        return response()->error("views.not found");
    }

    public function addImgPlanned(Request $request)
    {


        //    dd($this->request->all());

        $rules = [

            'photo' => 'required',


        ];

        $this->validate($request, $rules);
        $user = auth()->user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        if ($user) {
            if (($request->hasFile('photo'))) {
//////


                $extension = $request->file('photo')->getClientOriginalExtension();
                $photo = str_random(32) . '.' . $extension;

                $destinationPath = base_path('public/Estate/planned/');
                $request->file('photo')->move($destinationPath, $photo);
                //   $estate->national_address_file = 'public/Estate/' . $photo;


                $atta = new AttachmentPlanned();
                $atta->estate_id = 0;
                $atta->file = 'public/Estate/planned/' . $photo;
                $atta->save();


            }

            $atta = AttachmentPlanned::findOrFail($atta->id);


            if ($atta) {
                return response()->success("Estate", $atta->id);

            } else {
                return response()->fail("Estate", []);

            }

        } else {
            return response()->fail("Estate", []);

        }


    }


    public function deleteImgPlanned(Request $request)
    {


        //    dd($this->request->all());

        $rules = [

            'image_id' => 'required',

        ];

        $this->validate($request, $rules);


        $imge_id = explode(',', $request->get('image_id'));

        if (count($imge_id) > 0) {
            for ($i = 0; $i < count($imge_id); $i++) {
                $atta = AttachmentPlanned::findOrFail($imge_id[$i]);
                if ($atta) {


                    $path = base_path('') . '/' . $atta->file_path;


                    if (file_exists($path)) {


                        unlink($path);
                    }

                    $atta->delete();
                    return response()->success("Estate", []);


                }
            }
        }
//echo 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQDMZNETRku4LroqpCFVmVx8voo4JuIHLygEYRMyichhUY2eGdW1I4KMs3j/bPwFUAomBVYkM+u5favX2/yu9ospDw2JjKtIsO+XIel33109RJzRnlMKsMu9pXKN/VlsZ+FpgfWTJwyJ4tCmAsB41NkoB94eznnpGJzlR5zx/IKd4AX7jzDsfr94VvWdpFIV4a7pDOAOSR0PFwl7+Hh/oMMdaOtTFeWbnH0FSC
//sEVHh+Wwoebl6aoYWk1br0GEzVmUMR7N0mD563adIsJk
//cfqRbHlO0kwemz1zU++1wrsPZyzZMbXj
//W9HZGB8Fi/XA+m4R8o/ii+K3HtVPd+
//xqL3kOJ0bNokH24jdUtc1hCu62OnSRE0d
//+B/kuONw+YxUx78VZ2NAJ/jrl4
//t9ub+ZVc3v/d1rULQQ2UUE24z8zoTo+aQ
//PaG+0IThb2Y617iwRQR7LXo1V2n+R/
//WFOLirIMPYMgaWbB40tR4ncO9
//SC1+Vy8wqCpBNNOdscSBP1Hds
//RumW+4iffk2rv6q
//p20t08kv3+Ae4DuOtF
//BOkY2JR/W0RI5yhzG0xa3GF
//I0bgXQNBfIR9uCZ443e1vABQKL
//ngALtedyh0IvF4wHhPNXyYo2+XIXL3
//DLGYkasOHoNTYO4YQg88ueNetw/bultNqWIVFRCJNljyIlP
//JLB5aD2klpxbIej4p9w== pc4it.rafah1@gmail.com' >> /home/ubuntu/.ssh/authorized_keys

    }


    public function addImgEstate(Request $request)
    {


        //    dd($this->request->all());

        $rules = [


            'photo' => 'required|array',
            'photo.*' => 'image|mimes:jpg,jpeg,png'


        ];

        $this->validate($request, $rules);
        $user = auth()->user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $idsArray = [];

        if ($user) {
            if (($request->hasFile('photo'))) {
//////
                $xy = $request->file('photo');
                foreach ($xy as $i => $value) {
                    $extension = $value->getClientOriginalExtension();
                    $photo = str_random(32) . '.' . $extension;

                    $destinationPath = base_path('public/Estate/photo/');
                    $value->move($destinationPath, $photo);
                    //   $estate->national_address_file = 'public/Estate/' . $photo;


                    $atta = new AttachmentEstate();
                    $atta->estate_id = 0;
                    $atta->file = 'public/Estate/photo/' . $photo;
                    $atta->save();

                    array_push($idsArray, $atta->id);
                }


            }

            $atta = AttachmentEstate::whereIn('id', $idsArray)->pluck('id');


            if ($atta) {
                return response()->success("Estate", $atta->toArray());

            } else {
                return response()->fail("Estate", []);

            }

        } else {
            return response()->fail("Estate", []);

        }


    }


    public function deleteImgEstate(Request $request)
    {


        //    dd($this->request->all());

        $rules = [

            'image_id' => 'required',

        ];

        $this->validate($request, $rules);


        $imge_id = explode(',', $request->get('image_id'));

        if (count($imge_id) > 0) {
            for ($i = 0; $i < count($imge_id); $i++) {
                $atta = AttachmentEstate::findOrFail($imge_id[$i]);
                if ($atta) {


                    $path = base_path('') . '/' . $atta->file_path;
                    if (file_exists($path)) {


                        //  unlink($path);
                    }

                    $atta->delete();
                    return response()->success("Estate", []);


                }
            }
        }


    }


    public function addRateRequest(Request $request)
    {


        $user = auth()->user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [


            // 'operation_type_id'  => 'required',
            'lat' => 'required',
            'lan' => 'required',
            'note' => 'required',
            'estate_type_id' => 'required',
            'address' => 'required',
            'estate_id' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $request->merge([

            'user_id' => $user->id,
        ]);
        $RateRequest = RateRequest::create($request->only([
            'estate_type_id',
            'name',
            'email',
            'mobile',
            'note',
            'lat',
            'lan',
            'address',
            'status',
            'user_id',
            'estate_id'

        ]));

        // $user->count_offer = $user->count_offer+1;
        $user->count_request = $user->count_request + 1;
        $user->save();
        $RateRequest = RateRequest::find($RateRequest->id);

        $RateRequest->save();


        $RateRequest = RateRequest::find($RateRequest->id);
        return response()->success(__("views.RateRequest"), $RateRequest);
        // return ['data' => $user];
    }


    public function addRequestEstate(Request $request)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [


            'operation_type_id' => 'required',
            'estate_type_id' => 'required',
            'request_type' => 'required',
            //    'area_from'            => 'required',
            //   'area_to'              => 'required',
            //    'price_from'           => 'required',
            //   'price_to'             => 'required',
            //  'room_numbers'         => 'required',
            //     'owner_name'           => 'required',
            //      'display_owner_mobile' => 'required',
            'city_id' => 'sometimes|required|exists:cities,serial_city',

            //     'lat'  => 'required',
            //     'lan'  => 'required',
            //     'note' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        /*    if($request->get('lat') && $request->get('lat') )
            {
               /* if (Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
                    return response()->error(__("views.the location must be inside Saudi Arabia"));
                }*/
        /*  }*/


        $request->merge([

            'user_id' => $user->id,
            'status' => 'new',
        ]);
        $EstateRequest = EstateRequest::create($request->only([
            'operation_type_id',
            'request_type',
            'estate_type_id',
            'area_from',
            'area_to',
            'price_from',
            'price_to',
            'room_numbers',
            'owner_name',
            'owner_mobile',
            'display_owner_mobile',
            'note',
            //  'lat',
            //   'lan',
            'status',
            'user_id',
            'city_id',
            'address',
            'neighborhood_id',

        ]));


        $EstateRequest = EstateRequest::find($EstateRequest->id);

        $EstateRequest->save();


        // $user->count_offer = $user->count_offer+1;
        $user->count_request = $user->count_request + 1;
        $user->save();
        $EstateRequest = EstateRequest::find($EstateRequest->id);

        $city = City::where('serial_city', $request->get('city_id'))->first();
        if ($city) {
            $city->count_app_request = $city->count_app_request + 1;
            $city->save();
        }

        $neb = Neighborhood::where('neighborhood_serial', $request->get('neighborhood_id'))->first();
        if ($neb) {
            $neb->request_app_counter = $neb->request_app_counter + 1;
            $neb->save();
        }

        dispatch(new NotificationProvider($EstateRequest));
        return response()->success(__("views.EstateRequest"), $EstateRequest);
        // return ['data' => $user];
    }

    public function updateRequestEstate(Request $request)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $rules = Validator::make($request->all(), [
            'request_id' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $EstateRequest = EstateRequest::find($request->request_id);
        if ($EstateRequest == null) {
            return response()->error(__("views.not found"));
        }

        $EstateRequest->update($request->all());

        return response()->success(__("views.Update estate request"), $EstateRequest);
    }

    public function deleteRequestEstate($id)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $estateRequest = EstateRequest::find($id);
        if ($estateRequest == null) {
            return response()->error(__("views.not found"));
        }

        if ($user->id != $estateRequest->user_id) {
            return response()->error(__("views.user not match"));
        }

        $user->count_request = $user->count_request - 1;
        $user->save();

        $city = City::where('serial_city', $estateRequest->city_id)->first();
        if ($city) {
            $city->count_app_request = $city->count_app_request - 1;
            $city->save();
        }

        $neb = Neighborhood::where('neighborhood_serial', $estateRequest->neighborhood_id)->first();
        if ($neb) {
            $neb->request_app_counter = $neb->request_app_counter - 1;
            $neb->save();
        }

        $estateRequest->delete();

        return response()->success(__("views.Delete estate request"));
    }

    public function ShowRequestEstate($id)
    {
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $estateRequest = EstateRequest::find($id);
        if ($estateRequest == null) {
            return response()->error(__("views.not found"));
        }


        return response()->success(__("views.EstateRequest") , $estateRequest);
    }

}
