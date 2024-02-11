<?php

namespace App\Http\Controllers\v1\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Models\v1\AttachmentEstate;
use App\Models\v1\AttachmentPlanned;
use App\Models\v1\ComfortEstate;
use App\Models\v1\DeferredInstallment;
use App\Models\v1\Estate;
use App\Models\v1\EstateRequest;
use App\Models\v1\Finance;
use App\Models\v1\RateRequest;
use App\Models\v1\RateRequestTypeRate;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RealEstateController extends Controller
{

    public function deferredInstallment(Request $request)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error("not authorized");
        }
        $rules = Validator::make($request->all(), [


            'operation_type_id'      => 'sometimes|required',
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


            $extension = $request->file('national_address_file')->getClientOriginalExtension();
            $photo = str_random(32) . '.' . $extension;

            $destinationPath = base_path('public/DeferredInstallment/');
            $request->file('national_address_file')->move($destinationPath, $photo);
            $DeferredInstallment->national_address_file = 'public/DeferredInstallment/' . $photo;
        }
        if ($request->hasFile('owner_identity_file')) {


            $extension = $request->file('owner_identity_file')->getClientOriginalExtension();
            $photo = str_random(32) . '.' . $extension;

            $destinationPath = base_path('public/DeferredInstallment/');
            $request->file('owner_identity_file')->move($destinationPath, $photo);
            $DeferredInstallment->owner_identity_file = 'public/DeferredInstallment/' . $photo;
        }
        if ($request->hasFile('tenant_identity_file')) {


            $extension = $request->file('tenant_identity_file')->getClientOriginalExtension();
            $photo = str_random(32) . '.' . $extension;

            $destinationPath = base_path('public/DeferredInstallment/');
            $request->file('tenant_identity_file')->move($destinationPath, $photo);
            $DeferredInstallment->tenant_identity_file = 'public/DeferredInstallment/' . $photo;
        }
        $DeferredInstallment->save();

        $DeferredInstallment = DeferredInstallment::find($DeferredInstallment->id);
        return response()->success("DeferredInstallment ", $DeferredInstallment);
        // return ['data' => $user];
    }

    public function finance(Request $request)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error("not authorized");
        }

        $rules = Validator::make($request->all(), [
            'name'            => 'sometimes|required',
            'identity_number' => 'sometimes|required',
            'identity_file'   => 'sometimes|required',
            'job_type'        => 'sometimes|required',


            'mobile'         => 'sometimes|required',
            'city_id'        => 'sometimes|required',
            'job_start_date' => 'sometimes|required',
            'total_salary'   => 'sometimes|required',
            'birthday'   => 'sometimes|required',




            'operation_type_id' => 'sometimes|required',
            'estate_type_id'    => 'sometimes|required',

            'finance_interval'   => 'sometimes|required',
            'estate_price'       => 'sometimes|required',
            'available_amount'   => 'sometimes|required',
            'solidarity_partner' => 'sometimes|required',
            'solidarity_salary'  => 'sometimes|required',
            //  'job_start_date'     => 'required',//

        //    'engagements' => 'required',


            'national_address'         => 'sometimes|required',
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
            'status'  => 0,
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

        ]));


        $Finance = Finance::find($Finance->id);


        if ($request->hasFile('national_address_file')) {


            $extension = $request->file('national_address_file')->getClientOriginalExtension();
            $photo = str_random(32) . '.' . $extension;

            $destinationPath = base_path('public/Finance/');
            $request->file('national_address_file')->move($destinationPath, $photo);
            $Finance->national_address_file = 'public/Finance/' . $photo;
        }
        if ($request->hasFile('identity_file')) {


            $extension = $request->file('identity_file')->getClientOriginalExtension();
            $photo = str_random(32) . '.' . $extension;

            $destinationPath = base_path('public/Finance/');
            $request->file('identity_file')->move($destinationPath, $photo);
            $Finance->identity_file = 'public/Finance/' . $photo;
        }

        $Finance->save();

        $Finance = Finance::find($Finance->id);
        return response()->success("Finance", $Finance);
        // return ['data' => $user];
    }


    public function addEstate(Request $request)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error("not authorized");
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


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $request->merge([

            'user_id' => $user->id,
        ]);
        $estate = Estate::create($request->only([
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


        ]));


        $estate = Estate::find($estate->id);


        if ($request->hasFile('instrument_file')) {


            $extension = $request->file('instrument_file')->getClientOriginalExtension();
            $photo = str_random(32) . '.' . $extension;

            $destinationPath = base_path('public/Estate/');
            $request->file('instrument_file')->move($destinationPath, $photo);
            $estate->instrument_file = 'public/Estate/' . $photo;
        }


        if ($request->hasFile('exclusive_contract_file')) {


            $extension = $request->file('exclusive_contract_file')->getClientOriginalExtension();
            $photo = str_random(32) . '.' . $extension;

            $destinationPath = base_path('public/Estate/');
            $request->file('exclusive_contract_file')->move($destinationPath, $photo);


            $estate->exclusive_contract_file = 'public/Estate/' . $photo;

        }


        if (($request->get('attachment_planned'))) {
//////


            $arrayImg = explode(',', $request->get('attachment_planned'));


            $attachment = AttachmentPlanned::whereIn('id', $arrayImg)->update(['estate_id' => $estate->id]);


        }


        if (($request->get('attachment_estate'))) {
//////
            $arrayImg = explode(',', $request->get('attachment_estate'));
            $attachment = AttachmentEstate::whereIn('id', $arrayImg)->update(['estate_id' => $estate->id]);


        }

        if ($request->get('estate_comforts')) {


            $comforts = explode(',', $request->get('estate_comforts'));

            for ($i = 0; $i < count($comforts); $i++) {
                $comfort = ComfortEstate::create([
                    'estate_id'  => $estate->id,
                    'comfort_id' => $comforts[$i],

                ]);
            }

        }


        $estate->save();

        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);
        return response()->success("Estate", $estate);
        // return ['data' => $user];
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


                $atta = New AttachmentPlanned();
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

                $destinationPath = base_path('public/Estate/photo/');
                $request->file('photo')->move($destinationPath, $photo);
                //   $estate->national_address_file = 'public/Estate/' . $photo;


                $atta = New AttachmentEstate();
                $atta->estate_id = 0;
                $atta->file = 'public/Estate/photo/' . $photo;
                $atta->save();


            }

            $atta = AttachmentEstate::findOrFail($atta->id);


            if ($atta) {
                return response()->success("Estate", $atta->id);

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


                        unlink($path);
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
            return response()->error("not authorized");
        }
        $rules = Validator::make($request->all(), [


            'operation_type_id'  => 'required',
            'lat'                => 'required',
            'lan'                => 'required',
            'note'               => 'required',
            'rate_request_types' => 'required'


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $request->merge([

            'user_id' => $user->id,
        ]);
        $RateRequest = RateRequest::create($request->only([
            'operation_type_id',
            'name',
            'email',
            'mobile',
            'note',
            'lat',
            'lan',
            'status',
            'user_id'

        ]));


        $RateRequest = RateRequest::find($RateRequest->id);

        $RateRequest->save();


        if ($request->get('rate_request_types')) {


            $rate_request_types = explode(',', $request->get('rate_request_types'));

            for ($i = 0; $i < count($rate_request_types); $i++) {
                $RateRequestTypeRate = RateRequestTypeRate::create([
                    'rate_request_id'      => $RateRequest->id,
                    'rate_request_type_id' => $rate_request_types[$i],

                ]);
            }

        }

        $RateRequest = RateRequest::with('rate_request_types')->find($RateRequest->id);
        return response()->success("RateRequest", $RateRequest);
        // return ['data' => $user];
    }


    public function addRequestEstate(Request $request)
    {


        $user = auth()->user();

        if ($user == null) {
            return response()->error("not authorized");
        }
        $rules = Validator::make($request->all(), [


            'operation_type_id'    => 'required',
            'estate_type_id'       => 'required',
            'request_type'         => 'required',
            'area_from'            => 'required',
            'area_to'              => 'required',
            'price_from'           => 'required',
            'price_to'             => 'required',
            'room_numbers'         => 'required',
            'owner_name'           => 'required',
            'display_owner_mobile' => 'required',

            'lat'  => 'required',
            'lan'  => 'required',
            'note' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


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
            'lat',
            'lan',
            'status',
            'user_id',
            'city_id',
            'address',
            'neighborhood_id',

        ]));


        $EstateRequest = EstateRequest::find($EstateRequest->id);

        $EstateRequest->save();


        $EstateRequest = EstateRequest::find($EstateRequest->id);
        return response()->success("EstateRequest", $EstateRequest);
        // return ['data' => $user];
    }


    // public function
}
