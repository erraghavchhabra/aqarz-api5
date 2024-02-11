<?php

use App\Models\v2\RequestFund;
use App\Models\v3\Content;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\Request;

Route::get('/report-user-estate-count', function (Request $request) {
    return Excel::download(new \App\Imports\UserExport(), 'report_user' . '.xlsx');
});

Route::get('/privacy_policy', function (Request $request) {
    $local = $request->lang ? $request->lang : 'ar';
    $cloum = 'value_' . $local;
    $privacy_policy = Content::where('key', 'privacy_policy')->first()->$cloum;
    return view('privacy_policy', compact('privacy_policy'));
});

Route::get('/terms_and_conditions', function (Request $request) {
    $local = $request->lang ? $request->lang : 'ar';
    $cloum = 'value_' . $local;
    $terms_and_conditions = Content::where('key', 'terms_and_conditions')->first()->$cloum;
    return view('terms_and_conditions', compact('terms_and_conditions'));
});

Route::get('/update_user_name', function () {
   $users = \App\User::all();
   foreach ($users as $user)
   {
       if (!$user->user_name)
       {
           $user->user_name = 'aqarz_user_' . $user->id;
           $user->save();
       }
   }

});


Route::get('/properties', function () {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://integration-test.housingapps.sa/Ejar/ECRS/Properties',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('[data][ownership_document][attributes][document_number]' => '1686129754', '[data][ownership_document][attributes][issue_place]' => 'East Katharina', '[data][ownership_document][attributes][issued_by]' => 'Grocery', '[data][ownership_document][attributes][issued_date]' => 'Thu Aug 04 2022 00:56:46 GMT+0300 (Eastern European Summer Time)', '[data][ownership_document][attributes][legal_document_type_name]' => 'font', '[data][ownership_document][attributes][ownership_document_type]' => 'paper_title_deed', '[data][owners][attributes][role]' => 'property_owner', '[data][owners][attributes][entity_id]' => '{{TEST_LESSOR_ID}}', '[data][owners][attributes][entity_type]' => 'individual_entities', '[data][property][attributes][address][attributes][region]' => 'al_riyadh', '[data][property][attributes][address][attributes][district]' => '', '[data][property][attributes][address][attributes][city]' => 'Riyadh', '[data][property][attributes][address][attributes][building_number]' => '4710', '[data][property][attributes][address][attributes][street_name]' => 'Lloyd Lights', '[data][property][attributes][address][attributes][postal_code]' => '11111', '[data][property][attributes][address][attributes][additional_number]' => '11111', '[data][property][attributes][address][attributes][latitude]' => '49.4150', '[data][property][attributes][address][attributes][longitude]' => '-18.4816', '[data][property][attributes][property_name]' => 'Property 1686129754', '[data][property][attributes][property_number]' => '1686129754', '[data][property][attributes][total_floors]' => '919', '[data][property][attributes][property_usage]' => 'residential_families', '[data][property][attributes][property_type]' => 'building', '[data][property][attributes][established_date]' => 'Mon Dec 05 2022 08:03:26 GMT+0200 (Eastern European Standard Time)', '[data][property][attributes][units_per_floor]' => '812', '[data][property][attributes][associated_facilities][parking_spaces]' => '629', '[data][property][attributes][associated_facilities][security_entries]' => '763', '[data][property][attributes][associated_facilities][security_service]' => '704', '[data][property][attributes][associated_facilities][banquet_hall]' => '816', '[data][property][attributes][associated_facilities][elevators]' => '635', '[data][property][attributes][associated_facilities][gyms_fitness_centers]' => '829', '[data][property][attributes][associated_facilities][transfer_service]' => '508', '[data][property][attributes][associated_facilities][cafeteria]' => '269', '[data][property][attributes][associated_facilities][baby_nursery]' => '115', '[data][property][attributes][associated_facilities][games_room]' => '12', '[data][property][attributes][associated_facilities][football_yard]' => '550', '[data][property][attributes][associated_facilities][volleyball_court]' => '111', '[data][property][attributes][associated_facilities][tennis_court]' => '519', '[data][property][attributes][associated_facilities][basketball_court]' => '51', '[data][property][attributes][associated_facilities][swimming_pool]' => '114', '[data][property][attributes][associated_facilities][children_playground]' => '810', '[data][property][attributes][associated_facilities][grocery_store]' => '410', '[data][property][attributes][associated_facilities][laundry]' => '445', '[data][ownership_document][attributes][scanned_documents]' => new CURLFILE('/home/quang/Pictures/Screenshot from 2021-06-14 16-43-33.png')),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic QXFhcnpSRUJyVXNlcjoyMGFkNzdCXzcpYjFeRUZCYSE2ZDA2KDU1MTBlOERB'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    return [
        'status_code' => $status_code,
        'response' => json_decode($response, true)
    ];

});

Route::get('/propertiess', function () {

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://integration-test.housingapps.sa/Ejar/ECRS/Properties',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic QXFhcnpSRUJyVXNlcjoyMGFkNzdCXzcpYjFeRUZCYSE2ZDA2KDU1MTBlOERB'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;


});


Route::get('/export_fund', function () {
    $data =  RequestFund::with('neighborhood', 'offers')->get();
    return (new FastExcel($data))->download('fundRequests_' . Carbon::now(). '.xlsx' , function ($data) {
        return [
            'رقم الطلب' => $data->id,
            'نوع العقار' => $data->estate_type_name_web,
            'اتجاه العقار' => $data->dir_estate,
            'رقم المستفيد' => $data->beneficiary_mobile,
            'المدينة' => $data->city_name_web,
            'الحي' => $data->neighborhood_name,
            'تاريخ الطلب' => Carbon::parse($data->created_at)->format('d/m/Y - h:i A'),
            'السعر المطلوب' => $data->estate_price_range,
            'المساحة المطلوبة' => $data->street_view_range,
            'العروض' => $data->offers()->count(),
            'حالة العقار' => $data->estate_status,
        ];
    });
});

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/add/estate', function () {
    return view('welcomeAddEstate');

})->name('addestate');

Route::get('/ejar', function () {
    return view('ejar');

})->name('ejar');

Route::get('map', function () {
    $locations = \App\Models\v4\District::where('city_id' , '1061')->get();
    return view('map' , compact('locations'));

})->name('map');

Route::get('payment' , function (Request $request){
    $request->validate([
        'subscription_id' => 'required|numeric|exists:platform_subscriptions,id',
    ]);

    $subscription = \App\Models\v4\PlatformSubscriptions::find($request->subscription_id);
    $user = \App\User::find($subscription->user_id);

    if ($subscription->status != 'pending')
        return 'error subscription status not pending';
    return view('payment' , compact('user' , 'subscription'));
});


Route::get('/js/lang.js', function () {

    // $lang = config('app.locale');
    $lang = 'ar';

    // $strings = \Cache::rememberForever('lang-'.$lang.'.js', function () use ($lang) {
    // $files = glob(resource_path('lang/' . $lang . '/*.php'));
    $files = glob(resource_path('lang/' . $lang . '/*.php'));

    // dd(resource_path('lang/' . $lang . '/*.php'));
    $strings = [];

    foreach ($files as $file) {
        $name = basename($file, '.php');
        $strings[$name] = require $file;
    }

    return json_encode($strings);
    // });
    // dd(json_encode($strings));
    header('Content-Type: text/javascript');
    echo('window.i18n = ' . json_encode($strings) . ';');
    exit();
})->name('assets.i18n');

Route::get('/', function () {
    $city = \App\Models\v3\City::query()->limit(20)->get();
    $neb = \App\Models\v3\Neighborhood::query()->limit(20)->get();
    return view('welcome', compact('city', 'neb'));
})->middleware('cors');

Route::match(['get', 'post'], '/botman', 'BotManController@handle');
