<?php

namespace App\Http\Controllers\v2\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Jobs\NotificationProvider;
use App\Jobs\OtpJob;
use App\Models\v2\AttachmentEstate;
use App\Models\v2\AttachmentPlanned;
use App\Models\v2\City;
use App\Models\v2\ComfortEstate;
use App\Models\v2\DeferredInstallment;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\Finance;
use App\Models\v2\Neighborhood;
use App\Models\v2\RateRequest;
use App\Models\v2\RateRequestTypeRate;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class RealEstateController extends Controller
{

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
            'status' => '3',
        ]);
        $DeferredInstallment = DeferredInstallment::create($request->only([
            'operation_type_id',
            'estate_type_id',
            'contract_interval',

            'rent_price',
            'owner_name',
            'owner_mobile',
            'owner_identity_number',
            'tenant_name',
            'tenant_mobile',
            'tenant_identity_number',
            'tenant_birthday',


            'tenant_city_id',
            'tenant_job_type',
            'tenant_job_start_date',
            'tenant_total_salary',
            'tenant_salary_bank_id',
            'tenant_engagements',
            'national_address',
            'building_number',
            'street_name',
            'neighborhood_name',
            'building_city_name',
            'postal_code',
            'status',
            'user_id',
            'unit_name',
            'tenant_mobile_tow',
            'estate_id',
            'city_id',
            'neighborhood_id',
            'employer_name',
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
        if ($request->hasFile('national_address_file')) {


            /*  $extension = $request->file('national_address_file')->getClientOriginalExtension();
              $photo = str_random(32) . '.' . $extension;

              $destinationPath = base_path('public/DeferredInstallment/');
              $request->file('national_address_file')->move($destinationPath, $photo);
              $DeferredInstallment->national_address_file = 'public/DeferredInstallment/' . $photo;*/

            $path = $request->file('national_address_file')->store('images', 's3');
            $DeferredInstallment->national_address_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


        }
        if ($request->hasFile('owner_identity_file')) {


            /*  $extension = $request->file('owner_identity_file')->getClientOriginalExtension();
              $photo = str_random(32) . '.' . $extension;

              $destinationPath = base_path('public/DeferredInstallment/');
              $request->file('owner_identity_file')->move($destinationPath, $photo);
              $DeferredInstallment->owner_identity_file = 'public/DeferredInstallment/' . $photo;
  */


            $path = $request->file('owner_identity_file')->store('images', 's3');
            $DeferredInstallment->owner_identity_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }
        if ($request->hasFile('tenant_identity_file')) {


            /*  $extension = $request->file('tenant_identity_file')->getClientOriginalExtension();
              $photo = str_random(32) . '.' . $extension;

              $destinationPath = base_path('public/DeferredInstallment/');
              $request->file('tenant_identity_file')->move($destinationPath, $photo);
              $DeferredInstallment->tenant_identity_file = 'public/DeferredInstallment/' . $photo;*/


            $path = $request->file('tenant_identity_file')->store('images', 's3');
            $DeferredInstallment->tenant_identity_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }
        $DeferredInstallment->save();

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


    public function addEstate(Request $request)
    {



        //  dd($request->all());

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [


           /* 'operation_type_id' => 'required',


            'estate_type_id' => 'required|exists:estate_types,id',
            'state_id' => 'required|exists:regions,id',
            'city_id' => 'required|exists:cities,serial_city',
            'neighborhood_id' => 'required|exists:neighborhoods,neighborhood_serial',
            'lat' => 'sometimes|required',
            'lan' => 'sometimes|required',

            'estate_use_type' => 'required',
            'total_area' => 'required',
            'estate_dimensions' => 'required',
            'interface' => 'required',
            'is_mortgage' => 'required',
            'is_obligations' => 'required',
            'is_saudi_building_code' => 'required',
            'advertiser_side' => 'required',
            'advertiser_character' => 'required',
            'owner_name' => 'sometimes|required',
            'owner_mobile' => 'sometimes|required',



            'photo' => 'required|array',
            'photo.*' => 'required|image|mimes:jpg,jpeg,png',*/


        ]);

        Log::channel('slack')->info(['data' => $request->all(), 'user_id' => $user->id, 'user_name' => $user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }


        $request->merge([

            'user_id' => $user->id,
            'estate_age' => $age,
            'in_fund_offer' => 1,
        ]);


        /*  if (isset(Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'])&& Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
              return response()->error(__("views.the location must be inside Saudi Arabia"));
          }*/

        $estate = Estate::create($request->only([
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
            'full_address'


        ]));


        $estate = Estate::find($estate->id);


        if ($request->hasFile('video')) {


            $path = $request->file('video')->store('video', 's3');
            $estate->video = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }

        if ($request->hasFile('instrument_file')) {


            $path = $request->file('instrument_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
        }


        if ($request->hasFile('exclusive_contract_file')) {


            $path = $request->file('exclusive_contract_file')->store('images', 's3');
            $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;

        }


        /* if (($request->get('attachment_planned'))) {
 //////


             $arrayImg = explode(',', $request->get('attachment_planned'));


             $attachment = AttachmentPlanned::whereIn('id', $arrayImg)->update(['estate_id' => $estate->id]);


         }*/


        if (($request->hasFile('attachment_planned'))) {
//////

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


                $atta = new AttachmentEstate();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $atta->save();


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


        return response()->success(__("views.Estate"), $estate);
        // return ['data' => $user];
    }


    public function deleteEstate($id)
    {
        $estate = Estate::findOrFail($id);
        $user = auth()->user();

        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        if ($estate->user_id != $user->id) {
            return response()->error(__("views.Cant Delete"));
        }

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

            $estate->delete();
            //  $estate->available = 0;
            //   $estate->save();
            $user->count_estate = $user->count_estate - 1;
            $user->save();
            return response()->success(__("views.Deleted Successfully"), []);
        }

    }

    public function updateEstate(Request $request, $id)
    {

        //  dd($request->all());

        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }


        $rules = Validator::make($request->all(), [


            /*  'operation_type_id'       => 'sometimes|required',
              'estate_type_id'          => 'sometimes|required|exists:estate_types,id',
           //   'instrument_number'       => 'sometimes|required',
         //     'instrument_file'         => 'sometimes|required',
         //     'pace_number'             => 'sometimes|required',
      //        'planned_number'          => 'sometimes|required',
              'total_area'              => 'sometimes|required',
       //       'estate_age'              => 'sometimes|required',
      //        'floor_number'            => 'sometimes|required',
          //    'street_view'             => 'sometimes|required',
              'total_price'             => 'sometimes|required',
          //    'meter_price'             => 'sometimes|required',
              'owner_name'              => 'sometimes|required',
              'owner_mobile'            => 'sometimes|required',
              'lounges_number'          => 'sometimes|required',
              'rooms_number'            => 'sometimes|required',
              'bathrooms_number'        => 'sometimes|required',
              'boards_number'           => 'sometimes|required',
              'kitchen_number'          => 'sometimes|required',
              'dining_rooms_number'     => 'sometimes|required',
              'finishing_type'          => 'sometimes|required',
              'interface'               => 'sometimes|required',
              'social_status'           => 'sometimes|required',
              'lat'                     => 'sometimes|required',
              'lan'                     => 'sometimes|required',
              'note'                    => 'sometimes|required',
              'is_rent'                 => 'sometimes|required',
              'rent_type'               => 'sometimes|required',
              'is_resident'             => 'sometimes|required',
              'is_checked'              => 'sometimes|required',
              'is_insured'              => 'sometimes|required',
              'exclusive_contract_file' => 'sometimes|required',*/
            /*     'city_id' => 'sometimes|required|exists:cities,serial_city',

                 'photo'   => 'required|array',
                 'photo.*' => 'image|mimes:jpg,jpeg,png'*/


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $age = 0;
        if ($request->get('estate_age')) {
            $age = checkIfNumber($request->get('estate_age'));
        }

        $request->merge([

            'user_id' => $user->id,
            'estate_age' => $age,
        ]);


        $estate = Estate::find($id)
            ->update($request->only([
                'operation_type_id',
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
                'address',
                'in_fund_offer',


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

            $att = AttachmentEstate::where('estate_id', $id)->delete();
            $xy = $request->file('photo');
            foreach ($xy as $i => $value) {
                /* $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

                 dd($path);*/

                $path = $value->store('images', 's3');


                $atta = new AttachmentEstate();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;
                $atta->save();


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
        return response()->success(__("views.Update Successfully"), $estate);
    }

    public function MakeUpEstate($id)
    {
        $estate = Estate::findOrFail($id);
        if ($estate) {
            $estate->touch();
            return response()->success("views.Update Successfully", []);
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


    // public function
}
