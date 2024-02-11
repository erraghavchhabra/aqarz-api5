<?php

namespace App\Http\Controllers;



use App\Helpers\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use phpseclib\Crypt\RSA;

class IamController extends Controller
{
    public function login(Request $request)
    {

        // dd(GUID());

        $uuid = GUID();
        $time = time();
        $url = 'https://iambeta.elm.sa/authservice/authorize?' .
            'scope=openid&' .
            'response_type=id_token&' .
            'response_mode=form_post&' .
            'client_id=16373065&' .
            'redirect_uri=https://apibeta.aqarz.sa/api/login/auth/callback&' .
            'nonce=' . $uuid . '&' .
            'ui_locales=ar&' .
            'prompt=login&max_age=' . $time;

        $fp = fopen(public_path('certificate_new.pem'), "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        $pkeyid = openssl_pkey_get_private($priv_key);

        $pubkeyid = openssl_pkey_get_public($priv_key);

        $binary_signature = "";


        /////

        $rsa = new RSA();

        //  $rsa->setPassword('Aqarz@All');
        //$chek = $rsa->loadKey(file_get_contents(public_path('certificate_new_p.pem')));


        $chek =  $rsa->loadKey(file_get_contents(public_path('certificate_new_p.pem')));


        // $rsa->setPassword('Aqarz@All');

        $urlBase = hash('sha256', $url);
        $rsa->setHash('sha256');
        $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
        $signature = $rsa->sign($url);
        $signature = base64_encode($signature);
        // $rsa->loadKey(($rsa->getPublicKey())); // public key


        $rsa->loadKey($rsa->getPublicKey());
        // echo $rsa->verify($urlBase, $signature) ? 'verified' : 'unverified';


        $cheek=$rsa->verify($url, base64_decode($signature));



        $signature = urlencode($signature);

        $algo = "SHA256";


        $param_name = 'state';



        $urlBase = hash('sha256', $url);

        openssl_sign($url, $binary_signature, $pkeyid, $algo);


        $binary_signature = base64_encode($binary_signature);


        $ok = openssl_verify($url, base64_decode($binary_signature), $pubkeyid, OPENSSL_ALGO_SHA256);



        $binary_signature = urlencode($binary_signature);


        $join = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        $completeUrl = $url . $join . $param_name . '=' . $binary_signature;


        // return($completeUrl);

        if ($ok) {


            return response()->success(__('رابط الدخول'), $completeUrl);

            return $completeUrl;
            $client = new Client();


            $res = $client->request('GET', $completeUrl);
            echo $res->getStatusCode();
            // 200
            echo $res->getHeader('application/x-www-form-urlencoded');
            // 'application/json; charset=utf8'
            echo $res->getBody();

            dd($completeUrl);
        }

    }


    public function logout()
    {
        return response()->success('تم تسجيل الخروج بنجاح', []);
    }


    public function authCallback2(Request $request)
    {


        $rules = Validator::make($request->all(), [

            'id_token' => 'required',
            'state' => 'required',

        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        //  $jwt='eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMDEwMDU2MzQ3IiwiZW5nbGlzaE5hbWUiOiJBbGkgTW9oYW1tZWQgQWRlbCBLaGFsaWQiLCJhcmFiaWNGYXRoZXJOYW1lIjoi2YXYrdmF2K8iLCJlbmdsaXNoRmF0aGVyTmFtZSI6Ik1vaGFtbWVkIiwiZ2VuZGVyIjoiTWFsZSIsImlzcyI6Imh0dHBzOlwvXC93d3cuaWFtLmdvdi5zYVwvdXNlcmF1dGgiLCJjYXJkSXNzdWVEYXRlR3JlZ29yaWFuIjoiVGh1IE5vdiAxNiAwMDowMDowMCBBU1QgMjAxNyIsImVuZ2xpc2hHcmFuZEZhdGhlck5hbWUiOiJBZGVsIiwidXNlcmlkIjoiMTAxMDA1NjM0NyIsImlkVmVyc2lvbk5vIjoiNCIsImFyYWJpY05hdGlvbmFsaXR5Ijoi2KfZhNi52LHYqNmK2Kkg2KfZhNiz2LnZiNiv2YrYqSIsImFyYWJpY05hbWUiOiLYudmE2Yog2YXYrdmF2K8g2LnYp9iv2YQg2K7Yp9mE2K8iLCJhcmFiaWNGaXJzdE5hbWUiOiLYudmE2YoiLCJuYXRpb25hbGl0eUNvZGUiOiIxMTMiLCJpcWFtYUV4cGlyeURhdGVIaWpyaSI6IjE0NDhcLzA5XC8xMSIsImV4cCI6MTYzNjMwNTA0OSwibGFuZyI6ImFyIiwiaWF0IjoxNjM2MzA0ODk5LCJqdGkiOiJodHRwczpcL1wvaWFtYmV0YS5lbG0uc2EsQzc3REVCODYtNUY4Mi00QjQxLUE3NkUtQ0FCODNGODQxMTY2IiwiaXFhbWFFeHBpcnlEYXRlR3JlZ29yaWFuIjoiVGh1IEZlYiAxOCAwMDowMDowMCBBU1QgMjAyNyIsImlkRXhwaXJ5RGF0ZUdyZWdvcmlhbiI6IlRodSBGZWIgMTggMDA6MDA6MDAgQVNUIDIwMjciLCJpc3N1ZUxvY2F0aW9uQXIiOiLYp9mE2LHZitin2LYiLCJkb2JIaWpyaSI6IjE0MDFcLzA1XC8xNyIsImNhcmRJc3N1ZURhdGVIaWpyaSI6IjE0MzlcLzAyXC8yNyIsImVuZ2xpc2hGaXJzdE5hbWUiOiJBbGkiLCJpc3N1ZUxvY2F0aW9uRW4iOiJSeWFkaCIsImFyYWJpY0dyYW5kRmF0aGVyTmFtZSI6Iti52KfYr9mEIiwiYXVkIjoiaHR0cHM6XC9cL2FwaWJldGEuYXFhcnouc2FcL2FwaVwvIiwibmJmIjoxNjM2MzA0NzQ5LCJuYXRpb25hbGl0eSI6IlNhdWRpIEFyYWJpYSIsImRvYiI6IlN1biBNYXIgMjkgMDA6MDA6MDAgQVNUIDE5ODEiLCJlbmdsaXNoRmFtaWx5TmFtZSI6IktoYWxpZCIsImlkRXhwaXJ5RGF0ZUhpanJpIjoiMTQ0OFwvMDlcLzExIiwiYXNzdXJhbmNlX2xldmVsIjoiIiwiYXJhYmljRmFtaWx5TmFtZSI6Itiu2KfZhNivIn0.UweW_luXxLkwNuXLSHXT65OZ6C6tRRAO7DTBSNIWIH63EnhMLT5J4gqMwW4sNabR4mEwqEjnjfXbqhZNpXWaVPuNPILR9QmGUYrbQB22-XbcCvrX-BEOAL9mu6ZR-LEkCZkkFf0FgjdNvAjyxjidUiX386q6VJ2qdAYoOwppiwLXQCC-kuOm_teR-ksHteHAUV-3HQQCglAIJKOyNe_LCenHkiwzMEykErRENutuNgATnu78T6JXlY8NiqjUZZe5YGNbpoBj1ZSRsP-VW9zU1l_OzGdABZlEo9ZG3NUgpLiOgdioP5iiNDXF6aReV-5KabwKr7gEJyHTfA8Sb7dwMQ';
        //   $key='cSyCMJCAuMQHnHkZetIRJHYTZWy8odOPcHqEJqIH5dU=';
        $jwt = $request->get('id_token');
        $data = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $jwt)[1]))));


    //    $data={
        //"status":true,
        //"code":200,
        //"message":"User Profile",
        //"data":
        //{"sub":"1010056347",
        //"englishName":"Ali Mohammed Adel Khalid",
        //"arabicFatherName":"\u0645\u062d\u0645\u062f",
        //"englishFatherName":"Mohammed",
        //"gender":"Male",
        //"iss":"https:\/\/www.iam.gov.sa\/userauth",
        //"cardIssueDateGregorian":"Thu Nov 16 00:00:00 AST 2017",
        //"englishGrandFatherName":"Adel",
        //"userid":"1010056347",
        //"idVersionNo":"4",
        //"arabicNationality":"\u0627\u0644\u0639\u0631\u0628\u064a\u0629 \u0627\u0644\u0633\u0639\u0648\u062f\u064a\u0629",
        //"arabicName":"\u0639\u0644\u064a \u0645\u062d\u0645\u062f \u0639\u0627\u062f\u0644 \u062e\u0627\u0644\u062f",
        //"arabicFirstName":"\u0639\u0644\u064a",
        //"nationalityCode":"113",
        //"iqamaExpiryDateHijri":"1448\/09\/11",
        //"exp":1636584482,
        //"lang":"ar",
        //"iat":1636584332,
        //"jti":"https:\/\/iambeta.elm.sa,
        //91CEEC51-412C-4C30-9217-4AE1EA065AFE",
        //"iqamaExpiryDateGregorian":"Thu Feb 18 00:00:00 AST 2027",
        //"idExpiryDateGregorian":"Thu Feb 18 00:00:00 AST 2027",
        //"issueLocationAr":"\u0627\u0644\u0631\u064a\u0627\u0636",
        //"dobHijri":"1401\/05\/17",
        //"cardIssueDateHijri":"1439\/02\/27",
        //"englishFirstName":"Ali",
        //"issueLocationEn":"Ryadh",
        //"arabicGrandFatherName":"\u0639\u0627\u062f\u0644",
        //"aud":"https:\/\/apibeta.aqarz.sa\/api\/",
        //"nbf":1636584182,
        //"nationality":"Saudi Arabia",
        //"dob":"Sun Mar 29 00:00:00 AST 1981",
        //"englishFamilyName":"Khalid",
        //"idExpiryDateHijri":"1448\/09\/11",
        //"assurance_level":"",
        //"arabicFamilyName":"\u062e\u0627\u0644\u062f"}}

        return response()->success(__('views.User Profile'), $data);
    }
}
