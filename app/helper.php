<?php


use App\Models\v3\District;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use GeometryLibrary\SphericalUtil;
use http\Url;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;
use Symfony\Component\DomCrawler\Crawler;

DEFINE('DS', DIRECTORY_SEPARATOR);
//DEFINE('ejar_authorization', 'Ym8tMjA1MDEyMjI1MzpIUEpiSlpMN2ZGcmRUR05ZSWJOWWdIT25ERGk1Tk13NGVnemJvZDE0akg3dlFVOWRON0JwVXdFeFRidll4eWw0');
DEFINE('ejar_authorization', 'QXFhcnpSRUJyVXNlcjoyMGFkNzdCXzcpYjFeRUZCYSE2ZDA2KDU1MTBlOERB');
DEFINE('ejar_url', 'https://integration-test.housingapps.sa/Ejar/ECRS/');
DEFINE('api_url', 'https://api2.aqarz.sa/');
const ROOT_PATH = __DIR__;

function mainResponse($status, $message, $data, $code, $key, $validator)
{
    try {


        $result['status'] = $status;
        $result['code'] = $code;
        $result['message'] = $message;

        if ($validator && $validator->fails()) {
            $errors = $validator->errors();
            $errors = $errors->toArray();
            $message = '';
            foreach ($errors as $key => $value) {
                $message .= $value[0] . ',';
            }
            $result['message'] = $message;
            return response()->json($result, $code);
        } elseif (!is_null($data)) {


            if ($status) {
                if ($data != null && array_key_exists('data', $data)) {
                    $result[$key] = $data['data'];
                } else {
                    $result[$key] = $data;
                }
            } else {
                $result[$key] = $data;
            }
        }


        return response()->json($result, $code);
    } catch (Exception $ex) {


        return response()->json([
            'line' => $ex->getLine(),
            'message' => $ex->getMessage(),
            'getFile' => $ex->getFile(),
            'getTrace' => $ex->getTrace(),
            'getTraceAsString' => $ex->getTraceAsString(),
        ], $code);
    }
}

function checkIfMobileStartCode($mobile, $country_code = null)
{

    $western_arabic = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $eastern_arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $mobile = str_replace($eastern_arabic, $western_arabic, $mobile);
    $mobile = str_replace(['+', '-'], '', filter_var($mobile, FILTER_SANITIZE_NUMBER_INT));

    if (strlen($mobile) > 10) {
        return intval($mobile);
    }

    if (!$country_code) {
        $country_code = '966';
    }

    $start_with_code = substr($mobile, 0, strlen($country_code)) === $country_code;
    if ($start_with_code) {
        return $mobile;
    }

    if (strpos($mobile, "00") === 0 || strpos($mobile, "+") === 0) {
        return intval($mobile);
    }

    $mobile = intval($country_code) . intval($mobile);

    return $mobile;

}


function checkIfNumber($number)
{

    $western_arabic = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '36'];
    $eastern_arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '+35'];
    $mobile = str_replace($eastern_arabic, $western_arabic, $number);
    $mobile = str_replace(['+', '-'], '', filter_var($mobile, FILTER_SANITIZE_NUMBER_INT));

    if (strlen($mobile) > 10) {
        return intval($mobile);
    }


    $mobile = intval($mobile);

    return $mobile;

}

function str_random($length = 16)
{
    return Str::random($length);
}

function send_sms($msg, $numbers, $sender_name = "")
{
    $sender_name = $sender_name ? $sender_name : getSettings('smsSender');
    $settingsSmsGateway = getSettings('smsGateway');
    $settingsSmsUsername = urlencode(getSettings('smsUsername'));
    $settingsSmsPassword = urlencode(getSettings('smsPassword'));
    $settingsSmsSender = urlencode($sender_name);

    $msg = urlencode($msg);

    if (is_array($numbers)) {
        $numbers = implode(',', $numbers);
    }

    if (strpos($settingsSmsGateway, 'dreams')) {
        $url = $settingsSmsGateway . "?" . "username=" . $settingsSmsUsername . "&password=" . $settingsSmsPassword . "&numbers=" . $numbers . "&sender=" . $settingsSmsSender . "&message=" . $msg . "&lang=ar";
    } else {
        $url = $settingsSmsGateway . "?" . "mobile=" . $settingsSmsUsername . "&password=" . $settingsSmsPassword . "&numbers=" . $numbers . "&sender=" . $settingsSmsSender . "&msg=" . $msg . "&lang=3";
    }

    if (config('app.debug')) {
        \Log::info('debug ' . $url);
        return 100;
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
    ]);
    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
}

function getSettings($key = false)
{
    $settings = require(base_path() . '/../app/statics/settings.php');
    if ($key) {
        return $settings[$key] ?? $key;
    }
    return $settings;

}


function sendOtp($phone, $confirmation_code)
{

    $unifonicMessage = new UnifonicMessage();
    $unifonicClient = new UnifonicClient();
    $unifonicMessage->content = "Your Verification Code Is: " . $confirmation_code;
    $to = $phone;
    $data = $unifonicClient->sendVerificationCode($to, $unifonicMessage);
    Log::channel('single')->info($data);
    return $data;
}

function destroyFile($files)
{

    if (!is_array($files)) {
        $files = [$files];
    }
    foreach ($files as $file) {
        if (!empty($file) and file_exists(storage_path('public' . DIRECTORY_SEPARATOR . $file))) {
            unlink(base_path('public' . DIRECTORY_SEPARATOR . $file));
        }
        // unlink(public_path('storage'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.$file));
    }
}

function startsWith($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function get_class_name($object = null)
{
    if (!is_object($object) && !is_string($object)) {
        return false;
    }

    $class = explode('\\', (is_string($object) ? $object : get_class($object)));
    return $class[count($class) - 1];
}

function array_flatten($array, $depth = INF)
{
    return Arr::flatten($array, $depth);
}

function distance($lat1, $lon1, $lat2, $lon2, $unit)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else {
            if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }
}


function dirctions($id)
{
    $array = ['شمال', 'جنوب', 'شرق', 'غرب', 'الكل'];

    return $array[$id - 1];
}


function state_estate($id)
{
    $array = ['1' => 'جديد', '2' => 'مستخدم', '3' => 'جديد و مستخدم'];

    if (isset($array[$id])) {
        return $array[$id];
    } else {
        return null;
    }

}

function generateCode($length = 4)
{
    // start with a blank password
    $vCode = "";
    // define possible characters
    $possible = "0123456789bcdfghjkmnpqrstvwxyz";
    // set up a counter
    $i = 0;
    // add random characters to $password until $length is reached
    while ($i < $length) {
        // pick a random character from the possible ones
        $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
        // we don't want this character if it's already in the password
    }
}


function rate($number)
{


    $string = '';

    $rateFill = 5 - $number;
    for ($i = 0; $i < $number; $i++) {
        $string .= '
                                    <span class="fa fa-star checked"></span>


                                ';
    }

    for ($i = 0; $i < $rateFill; $i++) {
        $string .= '
                                    <span class="fa fa-star"></span>


                                ';
    }


    return $string;

}

function getQRCodeImgFromString($qrcode_hash)
{
    $path = trim($qrcode_hash . '.png');
    if (!\Storage::exists('uploads/qrcode/' . $path)) { // TODO remove this check
        \Storage::put('uploads/qrcode/' . $path,
            \QrCode::format('png')->size(300)->backgroundColor(241, 212, 18)->generate($qrcode_hash));
    }
    return url('uploads/qrcode/' . $path);
}


function send_push($device_token, $payload_data = [], $type = null)
{

    error_log('device_token: ' . json_encode($device_token));
    if ($device_token instanceof Illuminate\Support\Collection) {
        $device_token = $device_token->toArray();
    }

    if (!$device_token) {
        return true;
    }
    $device_token = array_filter((array)$device_token);

    if (!$device_token) {
        return true;
    }

    $notificationBuilder = new PayloadNotificationBuilder($payload_data['title']);
    $notificationBuilder->setBody($payload_data['body'])
        ->setBodyLocationKey($payload_data['id'])
        ->setClickAction($payload_data['id'])
        ->setSound('default');
    $notification = $notificationBuilder->build();

    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData($payload_data);
    $data = $dataBuilder->build();

    //


    if ($type == 'android') {


        FCM::sendTo($device_token, null, null, $data);
    } else {


        FCM::sendTo($device_token, null, $notification, $data);

        // FCM::sendTo($device_token, null, $notification, null);
        // FCM::sendTo($device_token, null, $notification, null);
    }


    Log::channel('slack')->info(['device_token' => $device_token, 'data' => $notification]);


}


function send_push_to_topic($topic_name, $title, $message, $id)
{
    $payload_data = [
        'title' => $title,
        'body' => $message,
        'id' => $id,
        'content_available' => true,
        'priority' => 'high',
        'timestamp' => date('Y-m-d G:i:s'),
        'icon' => 'appicon',
        'type' => 'marketing',

    ];

    $notificationBuilder = new PayloadNotificationBuilder($payload_data['title']);
    $notificationBuilder->setBody($payload_data['body'])
        ->setSound('default');
    $notification = $notificationBuilder->build();

    $dataBuilder = new PayloadDataBuilder();
    $dataBuilder->addData($payload_data);

    $data = $dataBuilder->build();

    $topic = new Topics();
    $topic->topic($topic_name);

    FCM::sendToTopic($topic, null, $notification);
// send_push_to_topic($cat_name->name, $news->title, $first, $news->id);
}


function getCoordinationFromAddress($address)
{

    $address = str_replace(" ", "+", $address);

    $json = '';


    $region = 'SA';
    $key = 'AIzaSyAtdvEJAz8r2k5V6Q618BosZla0iGrdMqU';


    $url = 'https://maps.google.com/maps/api/geocode/json?key=' . $key . '&address=' . $address . '&sensor=false&region=' . $region;

    $data = [
        'key' => $key,
        'address' => $address,
        'sensor' => 'false',
        'region' => $region,


    ];
    $data_json = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
    curl_setopt($ch, CURLOPT_HEADER, 0);


    curl_setopt($ch, CURLOPT_HEADER, 1);

    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    $result = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headerstring = substr($result, 0, $header_size);
    $body = substr($result, $header_size);
    $err = curl_error($ch);
    curl_close($ch);


    if ($result == false) {
        return 123;
    }

    $headerArr = explode(PHP_EOL, $headerstring);
    foreach ($headerArr as $headerRow) {
        preg_match('/([a-zA-Z\-]+):\s(.+)$/', $headerRow, $matches);
        if (!isset($matches[0])) {
            continue;
        }
        $header[$matches[1]] = $matches[2];
    }


    $json = json_decode($body);


    if (isset($json->{'results'}[0])) {


        $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};


        // dd($json->{'results'}[0]->{'address_components'}['1']);
        echo $lat . ',' . $long;
        echo '<br>';
        //  print_r($json->{'results'});
        return $lat . ',' . $long;
    }


//echo(( $json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'}-1)]->{'short_name'}));
//echo(( '</br>'));
    // dd($json);
    //  dd(isset($json->{'results'}[0])&& isset($json->{'results'}[0]->{'address_components'} ));
    //  dd(isset($json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'})-1]) );
    //dd( isset($json->{'results'}[0])&& isset($json->{'results'}[0]->{'address_components'} ) && isset($json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'})-1]) &&$json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'})-1]->{'short_name'} !='SA');
    if (isset($json->{'results'}[0]) &&
        isset($json->{'results'}[0]->{'address_components'})
        && isset($json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'}) - 1])
        && isset($json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'}) - 2])
        && $json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'}) - 1]->{'short_name'} != 'SA'
        && $json->{'results'}[0]->{'address_components'}[count($json->{'results'}[0]->{'address_components'}) - 2]->{'short_name'} != 'SA') {

        /*$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
        return $lat . ',' . $long;*/

        return false;

        /*    print_r( $json->{'results'}[0]->{'address_components'}[2]->{'short_name'});
            return false;*/


    } else if (isset($json->{'results'}[0])) {


        $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};


        // dd($json->{'results'}[0]->{'address_components'}['1']);
        echo $lat . ',' . $long;
        echo '<br>';
        //  print_r($json->{'results'});
        return $lat . ',' . $long;
    } else {


        return false;
    }

//return [json_decode($body)];
    return [
        'code' => (json_decode($body)->code),
        'msg' => (json_decode($body)->message),
        'status' => json_decode($body)->status
    ];


    echo trim($address);
    $region = 'KSA';
    $key = 'AIzaSyAtdvEJAz8r2k5V6Q618BosZla0iGrdMqU';


    $string = htmlspecialchars_decode("https://maps.google.com/maps/api/geocode/json?key=$key&address=" . $address . "&sensor=false&region=$region");
    $string = str_replace('&amp;', '&', $string);
    // $string = iconv('cp1251','UTF-8',$string);


    echo $string;
    echo "<br>";
    $json = html_entity_decode($string);
    $json = file_get_contents($json);
    $json = json_encode($json);


    //dd($json->{'results'}[0]->{'address_components'}[2]->{'short_name'});


}

function Get_Address_From_Google_Maps($lat, $lon)
{


    $url = "https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyD7_RAhw_L6sPjaWPWJ825zuA4-5RA9o-w&latlng=$lat,$lon&sensor=false&language=ar";
//AIzaSyAtdvEJAz8r2k5V6Q618BosZla0iGrdMqU
// Make the HTTP request
    $data = @file_get_contents($url);


    // dd($data);
// Parse the json response
    $jsondata = json_decode($data, true);


// If the json data is invalid, return empty array
    if (!$jsondata && !check_status($jsondata)) {
        return [];
    }


    // dd($jsondata['status']);

    $address = null;
    if (isset($jsondata) && $jsondata['status'] != 'ZERO_RESULTS') {
        $address = [
            'country' => google_getCountry($jsondata),
            'province' => google_getProvince($jsondata),
            'city' => google_getCity($jsondata),
            'street' => google_getStreet($jsondata),
            'postal_code' => google_getPostalCode($jsondata),
            'country_code' => google_getCountryCode($jsondata),
            'formatted_address' => google_getAddress($jsondata),
        ];
    }


    return $address;
}

/*
* Check if the json data from Google Geo is valid
*/

function check_status($jsondata)
{


    if (!$jsondata) {
        return false;
    }

    if (isset($jsondata["status"]) && $jsondata["status"] == "OK") {
        return true;
    }
    return false;
}

/*
* Given Google Geocode json, return the value in the specified element of the array
*/

function google_getCountry($jsondata)
{
    if (isset($jsondata)) {
        return Find_Long_Name_Given_Type("country", $jsondata["results"][0]["address_components"]);

    }
}

function google_getProvince($jsondata)
{
    return Find_Long_Name_Given_Type("administrative_area_level_1", $jsondata["results"][0]["address_components"],
        true);
}

function google_getCity($jsondata)
{
    return Find_Long_Name_Given_Type("locality", $jsondata["results"][0]["address_components"]);
}

function google_getStreet($jsondata)
{
    return Find_Long_Name_Given_Type("street_number",
            $jsondata["results"][0]["address_components"]) . ' ' . Find_Long_Name_Given_Type("route",
            $jsondata["results"][0]["address_components"]);
}

function google_getPostalCode($jsondata)
{
    return Find_Long_Name_Given_Type("postal_code", $jsondata["results"][0]["address_components"]);
}

function google_getCountryCode($jsondata)
{
    return Find_Long_Name_Given_Type("country", $jsondata["results"][0]["address_components"], true);
}

function google_getAddress($jsondata)
{
    return $jsondata["results"][0]["formatted_address"];
}

/*
* Searching in Google Geo json, return the long name given the type.
* (If short_name is true, return short name)
*/

function Find_Long_Name_Given_Type($type, $array, $short_name = false)
{
    foreach ($array as $value) {
        if (in_array($type, $value["types"])) {
            if ($short_name) {
                return $value["short_name"];
            }
            return $value["long_name"];
        }
    }
}

/*
*  Print an array
*/

function d($a)
{
    echo "<pre>";
    print_r($a);
    echo "</pre>";
}


function regin($id)
{
    $regine = [
        '1' => 'الرياض',
        '2' => 'مكه المكرمة',
        '3' => 'جازان',
        '4' => 'الشرقية',
        '5' => 'عسير',
        '6' => 'القصيم',
        '7' => 'حائل',
        '8' => 'المدينة المنورة',
        '9' => 'الباحة',
        '10' => 'الحدود الشمالية',
        '11' => 'تبوك',
        '12' => 'نجران',
        '13' => 'الجوف',
    ];

    if (isset($regine[$id])) {
        return $regine[$id];
    } else {
        return null;
    }


}


function regin_mobile($id)
{


}


function utf8ize($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

function watermark($name, $watermarkSource)
{
    $thumbnail = Image::make($name);
    $watermark = Image::make($watermarkSource);
    if ($thumbnail->height() > $thumbnail->width()) {
        $watermark_width = $thumbnail->width();
        $watermark_height = round(($watermark_width * $watermark->height()) / $watermark->width(), 2);
    } else {
        $watermark_height = $thumbnail->height();
        $watermark_width = round(($watermark_height * $watermark->width()) / $watermark->height(), 2);
    }
    $watermark->resize($watermark_width, $watermark_height);
    $thumbnail->insert($watermark, 'center');
    $thumbnail->save($name)->destroy();
    $watermark->destroy();
}


function pointInPolygon($point, $polygon, $pointOnVertex = true)
{


    // Transform string coordinates into arrays with x and y values
    $point = pointStringToCoordinates($point);


    $vertices = array();
    foreach ($polygon as $vertex) {
        $vertices[] = pointStringToCoordinatesPoly($vertex);
    }

    // Check if the lat lng sits exactly on a vertex
    if ($pointOnVertex == true and pointOnVertex($point, $vertices) == true) {
        return "vertex";
    }

    // Check if the lat lng is inside the polygon or on the boundary
    $intersections = 0;
    $vertices_count = count($vertices);

    for ($i = 1; $i < $vertices_count; $i++) {
        $vertex1 = $vertices[$i - 1];
        $vertex2 = $vertices[$i];
        if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
            return "boundary";
        }
        if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
            $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
            if ($xinters == $point['x']) { // Check if lat lng is on the polygon boundary (other than horizontal)
                return "boundary";
            }
            if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                $intersections++;
            }
        }
    }
    // If the number of edges we passed through is odd, then it's in the polygon.
    if ($intersections % 2 != 0) {
        return "inside";
    } else {
        return "outside";
    }
}

function pointOnVertex($point, $vertices)
{
    foreach ($vertices as $vertex) {
        if ($point == $vertex) {
            return true;
        }
    }

}

function pointStringToCoordinates($pointString)
{


    $coordinates = explode(" ", $pointString);
    return array("x" => $coordinates[0], "y" => $coordinates[1]);
}

function pointStringToCoordinatesPoly($pointString)
{

//    dd($pointString[0]);
    // $coordinates = explode(" ", $pointString);
    return array("x" => $pointString[1], "y" => $pointString[0]);
}

function checkPint($points)
{
    //$points = array("22.367582 70.711816", "21.43567582 72.5811816","22.367582117085913 70.71181669186944","22.275334996986643 70.88614147123701","22.36934302329968 70.77627818998701"); // Array of latlng which you want to find
//
    $dis = \App\Models\v4\District::all();


    // dd($dis->boundaries[0]->jsonSerialize()->getCoordinates());

    // The last lat lng must be the same as the first one's, to "close the loop"
    //foreach($points as $key => $point) {

    foreach ($dis as $key => $disItem) {
        if (pointInPolygon($points, $disItem->boundaries[0]->jsonSerialize()->getCoordinates()) == 'inside' || pointInPolygon($points, $disItem->boundaries[0]->jsonSerialize()->getCoordinates()) == 'vertex') {
            return $disItem->district_id;

        }

    }
    // }
}


function getCenter()
{
//28.484647285978,36.465996682962
    //  $location = '28.484647285978' . ' ' . '36.465996682962';
    // $dis = checkPint("$location");
    //  dd($dis);
    $de = \App\Models\v3\District::get();


    foreach ($de as $deItem) {
        $polygon = $deItem->boundaries[0]->jsonSerialize()->getCoordinates();


        $NumPoints = count($polygon);

        if ($polygon[$NumPoints - 1] == $polygon[0]) {
            $NumPoints--;
        } else {
            //Add the first point at the end of the array.
            $polygon[$NumPoints] = $polygon[0];
        }

        $x = 0;
        $y = 0;

        $lastPoint = $polygon[$NumPoints - 1];

        for ($i = 0; $i <= $NumPoints - 1; $i++) {
            $point = $polygon[$i];
            $x += ($lastPoint[0] + $point[0]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
            $y += ($lastPoint[1] + $point[1]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
            $lastPoint = $point;
        }


        $path = ComputeArea($polygon);

        $x /= -6 * $path;
        $y /= -6 * $path;


        $location = '29.830315444733' . ' ' . '39.573296345567';
        // $dis = checkPint("$location");
        // if($dis != null)
        //  {
        $locationForSave = $y . ',' . $x;
        $deItem->center = $locationForSave;
        $deItem->save();
        //  }
        // Log::channel('slack')->info(['data'=>$deItem->id,'msg'=>'from job create center']);


        //   return array($x, $y);
    }

}

function ComputeArea($polygon)
{
    $NumPoints = count($polygon);

    if ($polygon[$NumPoints - 1] == $polygon[0]) {
        $NumPoints--;
    } else {
        //Add the first point at the end of the array.
        $polygon[$NumPoints] = $polygon[0];
    }

    $area = 0;

    for ($i = 0; $i < $NumPoints; $i++) {
        $i1 = ($i + 1) % $NumPoints;
        $area += ($polygon[$i][1] + $polygon[$i1][1]) * ($polygon[$i1][0] - $polygon[$i][0]);
    }

    $area /= 2;
    return $area;
}

function nearest($lat, $lng, $radius = 30)
{
    // Km
    if (empty($radius)) $radius = 30;
    $angle_radius = $radius / 111;
    $location['min_lat'] = $lat - $angle_radius;
    $location['max_lat'] = $lat + $angle_radius;
    $location['min_lng'] = $lng - $angle_radius;
    $location['max_lng'] = $lng + $angle_radius;

    return (object)$location;

}

function copyLogFile($logFileName, $destination)
{
    $logPathDir = storage_path() . '/logs/';
    $logFilePath = $logPathDir . $logFileName;

    if (file_exists($logFilePath)) {
        if (!@copy($logFilePath, $destination)) {
            echo "File can't be copied! \n";
            $errors = error_get_last();
            echo "COPY ERROR TYPE: " . $errors['type'] . PHP_EOL;
            echo "COPY ERROR MESSAGE: " . $errors['message'];
        } else {
            echo "File has been copied! \n";
        }
    } else {
        echo "The file $logFilePath does not exist";
    }

}


function resizeImage($image, $width, $height, $scale, $imageNew = null, $dpi = false, $quality = 90)
{
    ini_set('memory_limit', '10000M');

    list($imagewidth, $imageheight, $imageType) = getimagesize($image);
    $imageType = image_type_to_mime_type($imageType);
    $newImageWidth = ceil($width * $scale);
    $newImageHeight = ceil($height * $scale);
    $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
    switch ($imageType) {
        case "image/gif":
            $source = imagecreatefromgif($image);
            imagefill($newImage, 0, 0, imagecolorallocate($newImage, 255, 255, 255));
            imagealphablending($newImage, true);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            $source = imagecreatefromjpeg($image);
            break;
        case "image/png":
        case "image/x-png":
            $source = imagecreatefrompng($image);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);

            //imagefill( $newImage, 0, 0, imagecolorallocate( $newImage, 255, 255, 255 ) );
            //imagealphablending( $newImage, TRUE );
            break;
    }
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $width, $height);
    // kill source

    imagedestroy($source);

    if ($dpi) {
        imageresolution($newImage, $dpi);
    }
    switch ($imageType) {
        case "image/gif":
            imagegif($newImage, $imageNew);
            break;
        case "image/pjpeg":
        case "image/jpeg":
        case "image/jpg":
            imagejpeg($newImage, $imageNew, $dpi ? 100 : $quality);
            break;
        case "image/png":
        case "image/x-png":
            imagepng($newImage, $imageNew);
            break;
    }

    chmod($image, 0777);
    return $image;
}

function getHeight($image)
{
    $size = getimagesize($image);
    $height = intval($size[1]);
    return $height;
}

function getWidth($image)
{
    $size = getimagesize($image);

    $width = intval($size[0]);
    return $width;
}

function aws_s3_link($access_key, $secret_key, $bucket, $canonical_uri, $expires = 0, $region = 'us-east-1', $extra_headers = array())
{
    $encoded_uri = str_replace('%2F', '/', rawurlencode($canonical_uri));

    $signed_headers = array();
    foreach ($extra_headers as $key => $value) {
        $signed_headers[strtolower($key)] = $value;
    }
    if (!array_key_exists('host', $signed_headers)) {
        $signed_headers['host'] = ($region == 'us-east-1') ? "$bucket.s3.amazonaws.com" : "$bucket.s3-$region.amazonaws.com";
    }
    ksort($signed_headers);

    $header_string = '';
    foreach ($signed_headers as $key => $value) {
        $header_string .= $key . ':' . trim($value) . "\n";
    }
    $signed_headers_string = implode(';', array_keys($signed_headers));

    $timestamp = time();
    $date_text = gmdate('Ymd', $timestamp);
    $time_text = $date_text . 'T000000Z';

    $algorithm = 'AWS4-HMAC-SHA256';
    $scope = "$date_text/$region/s3/aws4_request";

    $x_amz_params = array(
        'X-Amz-Algorithm' => $algorithm,
        'X-Amz-Credential' => $access_key . '/' . $scope,
        'X-Amz-Date' => $time_text,
        'X-Amz-SignedHeaders' => $signed_headers_string
    );
    if ($expires > 0) $x_amz_params['X-Amz-Expires'] = $expires;
    ksort($x_amz_params);

    $query_string_items = array();
    foreach ($x_amz_params as $key => $value) {
        $query_string_items[] = rawurlencode($key) . '=' . rawurlencode($value);
    }
    $query_string = implode('&', $query_string_items);

    $canonical_request = "GET\n$encoded_uri\n$query_string\n$header_string\n$signed_headers_string\nUNSIGNED-PAYLOAD";
    $string_to_sign = "$algorithm\n$time_text\n$scope\n" . hash('sha256', $canonical_request, false);
    $signing_key = hash_hmac('sha256', 'aws4_request', hash_hmac('sha256', 's3', hash_hmac('sha256', $region, hash_hmac('sha256', $date_text, 'AWS4' . $secret_key, true), true), true), true);
    $signature = hash_hmac('sha256', $string_to_sign, $signing_key);

    $url = 'https://' . $signed_headers['host'] . $encoded_uri . '?' . $query_string . '&X-Amz-Signature=' . $signature;
    return $url;
}

function rsa_sha1_sign($policy, $private_key_filename)
{
    $signature = "";

    // load the private key
    $fp = fopen($private_key_filename, "r");
    $priv_key = fread($fp, 8192);
    fclose($fp);
    $pkeyid = openssl_get_privatekey($priv_key);

    // compute signature
    openssl_sign($policy, $signature, $pkeyid);

    // free the key from memory
    openssl_free_key($pkeyid);

    return $signature;
}

function GUID()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

//bank_dashboard,
// bank_finance_requests,
// bank_finance_requests_show,
// bank_deferred-installments,
// bank_deferred_installments_show,
//admin_dashboard,
//admin_users,
//admin_user_show,
//fund_dashboard,
//fund_providers,
//fund_fund_provider_show,
//fund_preview_requests,
//fund_preview_request_show,
//fund_estateList_with_filter,
//fund_estateList_with_filter,
//fund_estateList_with_filter,
//fund_unaccepted_estates,
//fund_unaccepted_estate_show,
//admin_send_sms,
//fund_delete_request,
//bank_installments_add_comment,
//bank_installments_display_in_app,
//bank_installments_add_comment,
//bank_installments_update_status,
//bank_finances,
//bank_finance_show,
//bank_finance_update,
//fund_preview_request_add_notes,
//fund_preview_request_update,
//fund_send_offer_dash,
//fund_available_request_show,
//fund_available_request_show,
//fund_available_estate_list,
//fund_available_estate_offers,
//fund_reject_offer,
//fund_provider_attchment_data,
//fund_provider_attchment_data,
//fund_provider_date_data,
//fund_offer_date_data,
//fund_offer_cancel,

function can($permission)
{
    //  $user = auth()->user();

    $userCheck = auth()->guard('admin')->check();
    $user = '';

    if ($userCheck == false) {
        return redirect('admin/login');
    } else {
        $user = auth()->guard('admin')->user();
    }


    if ($user->type == 1) {
        return true;
    }


    $minutes = 1200;
    $permissions = Cache::remember('permissions_' . $user->id, $minutes, function () use ($user) {

        return explode(',', $user->permission->permission);

    });


    $permissions = array_flatten($permissions);
    return in_array($permission, $permissions);


}


function number_format_short($n, $precision = 2)
{
    $n = str_replace(',', '', $n);

    if ($n < 1000) {
        // 0 - 900
        $n = floatval($n);

        $n_format = $n;
        $suffix = '';
    } else if ($n < 1000000) {
        // 0.9k-850k
        $n_format = $n / 1000;
        $suffix = app()->getLocale() == 'ar' ? ' ألف' : 'K';
    } else if ($n < 1000000000) {
        // 0.9m-850m
        $n_format = $n / 1000000;
        $suffix = app()->getLocale() == 'ar' ? ' مليون' : 'M';
    } else if ($n < 1000000000000) {
        // 0.9b-850b
        $n_format = $n / 1000000000;
        $suffix = app()->getLocale() == 'ar' ? ' مليار' : 'B';
    } else {
        // 0.9t+
        $n_format = $n / 1000000000000;
        $suffix = app()->getLocale() == 'ar' ? ' تريليون' : 'T';
    }

//    //change number to array to remove 0 after decimal
//    $n_format = explode('.', $n_format);
//    $last = $n_format[1];
//    $last = str_split($last);
//    $last = array_reverse($last);
//    foreach ($last as $key => $value) {
//        if ($value == 0) {
//            unset($last[$key]);
//        } else {
//            break;
//        }
//    }
//    $last = array_reverse($last);
//    $last = implode('', $last);
//    $n_format[1] = $last;
//    $n_format = implode('.', $n_format);
//

    return round($n_format , 2) . ' ' . $suffix;
}

function convert_number_to_english($number)
{
    $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    $english = range(0, 9);
    return str_replace($arabic, $english, $number);
}

//conver date to arabic
function convertDateToArabic($date)
{
    $months = array(
        "01" => "يناير",
        "02" => "فبراير",
        "03" => "مارس",
        "04" => "ابريل",
        "05" => "مايو",
        "06" => "يونيو",
        "07" => "يوليو",
        "08" => "اغسطس",
        "09" => "سبتمبر",
        "10" => "اكتوبر",
        "11" => "نوفمبر",
        "12" => "ديسمبر"
    );
    $date = explode('-', $date);
    if (count($date) == 3) {
        $date = convertNumberToArabic($date[2]) . ' ' . $months[$date[1]] . ' ' . convertNumberToArabic($date[0]);

    } else {
        $date = convertNumberToArabic($date[1]) . ' ' . $months[$date[0]];

    }
    return $date;
}

//conert number to arabic
function convertNumberToArabic($number)
{
    $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    $english = range(0, 9);
    return str_replace($english, $arabic, $number);
}
