<?php

namespace App\Http\Controllers\DashBoard\rent;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Models\v2\AttachmentEstate;
use App\Models\v2\AttachmentPlanned;
use App\Models\v2\ComfortEstate;

use App\Models\v2\Estate;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Image;

class RealEstateController extends Controller
{




    public function addEstate(Request $request)
    {


        //  dd($request->all());

        $user = auth()->guard('Rent')->user();


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
            'city_id' => 'sometimes|required|exists:cities,serial_city',

            'photo'   => 'required|array',
            'photo.*' => 'required|image|mimes:jpg,jpeg,png',
            'lat'                     => 'required',
            'lan'                     => 'required',


        ]);

        Log::channel('slack')->info(['data'=>$request->all(),'user_id'=>$user->id,'user_name'=>$user->name]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $age=0;
        if($request->get('estate_age'))
        {
       $age=     checkIfNumber($request->get('estate_age'));
        }

        $request->merge([

            'user_id' => $user->id,
            'estate_age' => $age,
            'is_rent' => 1,
            'operation_type_id' => 2,
        ]);


       /* if (isset(Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'])&& Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
            return response()->error(__("views.the location must be inside Saudi Arabia"));
        }*/

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
            'in_fund_offer',


        ]));


        $estate = Estate::find($estate->id);

        $watermarkSource = public_path('mark.png');
        $temp = public_path('temp/');






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


                $atta = New AttachmentPlanned();
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
                    'estate_id'  => $estate->id,
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




                watermark($path, $watermarkSource);



                $file = $value;

                $imageName = $file->getClientOriginalName();

                $img = Image::make($file);


//detach method is the key! Hours to find it... :/
                $resource = $img->stream()->detach();

               $path = Storage::disk('s3')->put(
                   $imageName,
                    $resource
                );


               dd($path);
            //    $path = $value->store('images', 's3');


                $atta = New AttachmentEstate();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $imageName;
                $atta->save();


            }


        }

        $estate->save();
        $user->count_offer = $user->count_offer + 1;
        // $user->count_request =  $user->count_request+1;
        $user->save();
        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);
        return response()->success(__("views.Estate"), $estate);
        // return ['data' => $user];
    }

}
