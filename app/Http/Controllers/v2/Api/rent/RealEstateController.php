<?php

namespace App\Http\Controllers\v2\Api\rent;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Jobs\NotificationProvider;
use App\Jobs\OtpJob;
use App\Models\v2\AttachmentEstate;
use App\Models\v2\AttachmentPlanned;
use App\Models\v2\ComfortEstate;
use App\Models\v2\DeferredInstallment;
use App\Models\v2\Estate;
use App\Models\v2\EstateRequest;
use App\Models\v2\Finance;
use App\Models\v2\RateRequest;
use App\Models\v2\RateRequestTypeRate;
use App\Models\v2\RequestOffer;
use App\User;
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

        $watermarkSource = public_path('mark2.png');
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
                 $extension = $value->getClientOriginalExtension();
                 $photo = str_random(32) . '.' . $extension;

                 $destinationPath = base_path('public/Estate/photo/');
                 $path = $value->move($destinationPath, $photo);
                 //   $estate->national_address_file = 'public/Estate/' . $photo;

              //   dd(base_path('public/Estate/photo/').$photo);



               // $img = Image::make(base_path('public/Estate/photo/').$photo);


                /* insert watermark at bottom-right corner with 10px offset */
            //    $img->insert(public_path('mark.png'), 'bottom-right', 50, 50);

            //    $img->save(base_path('public/Estate/photo/'.$photo));



              //  $file = $value;

              //  $imageName = $file->getClientOriginalName();





                watermark(base_path('public/Estate/photo/').$photo, $watermarkSource);
            /*    $img = Image::make(base_path('public/Estate/photo/').$photo);
//detach method is the key! Hours to find it... :/
                $resource = $img->stream()->detach();

                $s3 = \Storage::disk('s3');
                $s3->put($s3filePath, file_get_contents($image), 'public');


                return Storage::disk('s3')->response('images/' . $image->filename);
*/

             /*  $path = Storage::disk('s3')->put(
                   base_path('public/Estate/photo/').$photo,
                    $resource
                );
*/



          //      $image = $value;

                # get s3 object make sure your key matches with
                # config/filesystem.php file configuration
                $s3 = \Storage::disk('s3');

                # rename file name to random name
              //  $file_name = uniqid() .'.'. $image->getClientOriginalExtension();

                # define s3 target directory to upload file to
                $s3filePath = '/public/Estate/photo/' . $photo;

                # finally upload your file to s3 bucket
                $s3->put($s3filePath, file_get_contents( base_path('public/Estate/photo/').$photo), 'public');






         //       $path = $path->store('images', 's3');

            //    $path = $value->store('images', 's3');


                $atta = New AttachmentEstate();
                $atta->estate_id = $estate->id;
                $atta->file = 'https://aqarz.s3.me-south-1.amazonaws.com/Estate/photo/' . $photo;
                $atta->save();


            }


        }



        $estate->save();

        $request->merge([

            'name' =>  $request->get('employee_name'),
            'mobile' => $request->get('employee_mobile'),

        ]);


        /* if (isset(Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'])&& Get_Address_From_Google_Maps($request->get('lat'), $request->get('lan'))['country_code'] != "SA") {
             return response()->error(__("views.the location must be inside Saudi Arabia"));
         }*/

        $user = User::create($request->only([
            'city_id',
            'is_pay',
            'name',
            'email',
            'password',
            'type',
            'device_token',
            'device_type',
            'mobile',
            'api_token',
            'country_code',
            'confirmation_code',
            'logo',
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


        ]));



        $user->count_offer = $user->count_offer + 1;
        // $user->count_request =  $user->count_request+1;
        $user->save();

        $estate->user_id=$user->id;
        $estate->is_from_rent_system=1;
        $estate->save();


        $estate = Estate::with('plannedFile', 'EstateFile', 'comforts')->find($estate->id);
        return response()->success(__("views.Estate"), $estate);
        // return ['data' => $user];
    }
    public function myEstate(Request $request)
    {
        $user = auth()->guard('Rent')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }

        $estate = Estate::where('is_from_rent_system', 1);

        if ($request->get('city_id')) {

            $estate = explode(',', $request->get('city_id'));

            $estate = $estate->whereIn('city_id', $estate);
        }


        $estate = $estate->orderBy('id', 'desc')->get();
        if ($estate) {
            return response()->success(__("views.Estates"), $estate);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }

    public function myRequestOffer(Request $request)
    {
        $user = auth()->guard('Rent')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }



        $estate_request = RequestOffer::whereHas('estate', function ($query) use ($user) {
            $query->where('is_from_rent_system', 1);

        });


        $estate_request = $estate_request->orderBy('id', 'desc')->get();
        if ($estate_request) {
            return response()->success(__("views.EstatesRequest"), $estate_request);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }


    public function myRequest(Request $request)
    {
        $user = auth()->guard('Rent')->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }



        $estate_request = EstateRequest::where('request_type','rent');


        $estate_request = $estate_request->orderBy('id', 'desc')->get();
        if ($estate_request) {
            return response()->success(__("views.EstatesRequest"), $estate_request);
        } else {
            return response()->error(__("views.not found"), []);
        }

    }
}


