<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Imports\CityImport;
use App\Imports\FundRequestExport;
use App\Imports\NeighborhoodImport;
use App\Models\v1\RequestFund;
use App\Models\v2\Estate;
use App\Models\v2\FundRequestOffer;
use App\User;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Config;
use Illuminate\Support\Facades\Validator;
use phpseclib\Crypt\RSA;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //  $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        echo '111';


        /*
                dd(Config::get('filesystems'));
                echo 'you are in api project';
                die();
        */
        /*   $url = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/SendSMSwhenReadyOffers';
           $data = ['uuid' => '47c1eeef-5ebc-415e-b216-705babd0a5f3'];
           $data_json = json_encode($data);

           $response = Http::withBasicAuth('Aqarz', 'A@qR3Zz#81$')
               ->withHeaders([
                   'Content-Type' => 'application/json',
                   'Accept'       => 'application/json'
               ])
               ->post($url);


           dd($response);
           /* $path = trim('hi'.'.png');


           // dd(\QrCode::format('png')->size(300)->backgroundColor(241,212,18)->generate($path));



            $response = response(file_get_contents(getQRCodeImgFromString('hi')), 200)->header('Content-Type', 'image/png');


            dd($response);
            $destinationPath = base_path('public/uploads/qrcode/');



            $photo = str_random(32) . '.png';
            // $request->file('photo')->storeAs('users/photo/', $photo);

            //  $request->file('photo')->storeAs(base_path('public/users/photo/'),$photo);
            \QrCode::format('png')->size(300)->backgroundColor(241,212,18)->generate($path)->move($destinationPath, $photo);

    */


        return view('home');
    }




    public function excel(Request $request)
    {


        $rules = [
            'date' => 'required',


        ];

        $this->validate($request, $rules);
        return Excel::download(new FundRequestExport(), 'invoices.xlsx');

        Excel::import(new CityImport(), public_path('citynew3.xlsx'));

        return back();
    }


    public function export_fund_request_page(Request $request)
    {

        return view('fundExport');
    }

    public function export_fund_request(Request $request)
    {


        $rules = [
            'date' => 'required',


        ];

        $date = $request->get('date');

        $this->validate($request, $rules);
        return Excel::download(new FundRequestExport($date), 'fundRequests_' . $request->get('date') . '_' . '.xlsx');

        Excel::import(new CityImport(), public_path('citynew3.xlsx'));

        return back();
    }

    public function excel2()
    {
        Excel::import(new NeighborhoodImport(), public_path('citynew.xlsx'));

        return back();
    }


    /* public function provider($id)
     {
         $privder = User::findOrFail($id);



         return view('provider',compact('privder'));
     }*/


    public function provider(User $privder)
    {

        if ($privder) {
            $privder->count_visit += 1;
            $privder->save();
            return view('provider', compact('privder'));
        } else {
            dd(44);
        }

    }


    public function estate($id)
    {
        $estate = Estate::findOrFail($id);


        return view('estate', compact('estate'));
    }

    public function paymentInvoices()
    {
        Artisan::call("pdf:create");


    }

    public function createOffer()
    {
        Artisan::call("offer:create");


    }

    public function download()
    {
        return view('download');


    }


    public function testFundApi()
    {

        $client = new Client([
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_PROXYPORT => 7443,
            ],
            'allow_redirects' => false,
            'cookies' => true,
            'verify' => false,
            'version' => 1.0
        ]);
        //   $client->setDefaultOption(array('verify', false));
        $URI = 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/Approvaloffers';
        $params['headers'] = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic QXFhcno6QUBxUjNaeiM4MSQ=',
            'User-Agent' => 'Moliza/5.0',
        ];
        $params['row'] = json_encode([
            'uuid' => '8f8aad04-1dae-40b3-89d0-9dc3e2ea1b55',

        ]);
        $response = $client->request('POST', $URI, $params);
        echo "DONE!";


        dd(444);


        /*   $client = new Client([ 'base_uri' => 'https://appts.redf.gov.sa:7443/', 'verify' => false ]);

           $response = $client->post('Aqarz/api/Aqarz/Approvaloffers', [
               'curl' => [
                   CURLOPT_HTTPHEADER =>  [
                       'Content-Type'  => 'application/json',
                       'Authorization' => 'Basic QXFhcno6QUBxUjNaeiM4MSQ='
                   ],
                   CURLOPT_POSTFIELDS => json_encode([
                       'uuid' => '8f8aad04-1dae-40b3-89d0-9dc3e2ea1b55',

                   ])
               ],
               'version'=>1.0
           ]);

           print $response->getBody()->getContents();
   */
        $client = new Client();
        $client->request('POST', 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/Approvaloffers', [
            'version' => 1.0,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic QXFhcno6QUBxUjNaeiM4MSQ='
            ]
        ]);


        dd(444);

        /*  Server::enqueue([
              new Response(200, ['Content-Length' => 0])
          ]);*/

        //  $client = new Client(['base_uri' => 'https://appts.redf.gov.sa:7443']);
        //   echo $client->request('POST', '/Aqarz/api/Aqarz/Approvaloffers')->getStatusCode();


        // dd(444);
        $client = new Client([
            'base_uri' => 'https://appts.redf.gov.sa:7443',

        ]);
        //  $client->setDefaultOption(array('verify', false));
        $URI = 'Aqarz/api/Aqarz/Approvaloffers';
        $params['headers'] = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic QXFhcno6QUBxUjNaeiM4MSQ='
        ];
        $params['row'] = json_encode([
            'uuid' => '8f8aad04-1dae-40b3-89d0-9dc3e2ea1b55',

        ]);
        $response = $client->post($URI, $params);
        echo "DONE!";


        dd($client);


        $client = new Client([
            'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/Approvaloffers',
            [
                'version' => 'v1.1',
                'request.options' => [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization Basic QXFhcno6QUBxUjNaeiM4MSQ='],
                    'auth' => ['Aqarz', 'A@qR3Zz#81$', 'Basic|Digest|NTLM|Any'],
                    'proxy' => 'tcp://localhost:80',

                ],

            ]
        ]);

        // $this->client = new GuzzleClient(['base_uri' => 'https://api.example.com/', 'verify' => false ]);

        //  $client->setDefaultOption(array('verify', false));
        //    $client->request('POST', 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/Approvaloffers', ['verify' => false]);
        // $response = $client->post($client);
        dd($client);

        /* $client = new Client([
             'curl'            => array( CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false ),
             'allow_redirects' => false,
             'cookies'         => true,
             'verify'          => false
         ]);
    //     $response = $client->request('POST', 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/Approvaloffers',['headers' => $headers, 'body' => $body]);

      //  $client->setDefaultOption(array('verify', false));
         $request = $client->request("POST", 'https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/Approvaloffers', array(
             'Content-Type: application/json',
             'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',
         ));*/
        // $client = new Client;


        //  $response = $client->post($request);


        dd(444);
        //  $client->request('GET', '/', ['verify' => false]);
// You need to parse the response body
// This will parse it into an array
        $response = json_decode($response->getBody(), true);

/////////////////////////////////////////////////////

        $endpoint = "http://my.domain.com/test.php";
        $client = new Client(['verify' => false]);
        $id = 5;
        $value = "ABC";

        $response = $client->request('POST', $endpoint, [
            'query' => [
                'key1' => $id,
                'key2' => $value,
            ]
        ]);

// url will be: http://my.domain.com/test.php?key1=5&key2=ABC;

        $statusCode = $response->getStatusCode();
        $content = $response->getBody();


        dd($content);


        /* $response='';
         $errors='';

      //   ob_start();
         $duration=  exec('curl   --insecure  -H “Content-Type: application/json”   -H “Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=”   -X POST   -d ‘{“uuid” : “8f8aad04-1dae-40b3-89d0-9dc3e2ea1b55"}’   https://appts.redf.gov.sa:7443/Aqarz/api/Aqarz/SendSMSwhenReadyOffers',$response,$errors);
       //  $duration = ob_get_contents();
       //  ob_end_clean();


         dd( $errors);

         /*    $ch = curl_init('http://whatismyip.org/');
             curl_setopt($ch, CURLOPT_INTERFACE, "162.214.152.216");
             $myIp = curl_exec($ch);
             $err = curl_error($ch);
             $code = curl_getinfo($ch);
             curl_close($ch);
     // check http code


             print_r(json_decode($myIp, true));


             dd(444);*/
        // service
        $url = 'https:/appts.redf.gov.sa:7443/Aqarz/api/Aqarz/SendSMSwhenReadyOffers';

        $dataAuth = ["username" => "Aqarz", 'password' => 'A@qR3Zz#81$']; // data u want to post
        $data_string = json_encode($dataAuth);
        $data = ['uuid' => '8f8aad04-1dae-40b3-89d0-9dc3e2ea1b55'];
        $data_json = json_encode($data);
        ob_start();
        $out = fopen('php://output', 'w');


// initialize curl resource
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_INTERFACE, "162.214.152.216");


// set curl options
        //  curl_setopt($ch, CURLOPT_URL, $url);
        //   curl_setopt($ch, CURLOPT_HEADER, true);
        //   curl_setopt($ch, CURLOPT_NOBODY, true); // remove body
        //  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3000);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_USERPWD, $data_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //      curl_setopt($ch, CURLOPT_STDERR, $out);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Content-Type: application/json',
            'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',

        ]);

// execute curl
        $response = curl_exec($ch);
        $err = curl_error($ch);

// check http code
        $code = curl_getinfo($ch);

// close curl resource
        curl_close($ch);
        $data = ob_get_clean();
        $data .= PHP_EOL . $response . PHP_EOL;
        echo $data;

        //  dd($code);


        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            if ($code == 200) {

            } //if not a 200 OK
            else {
                echo "Did Not Receive 200 OK\n";
                echo "Received Code:" . $code . "\n\n";
            }
        }


        dd(444);
        $url = 'https://aqarz.sa/api/call';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        print_r($output);
        print_r($curl_error);


        dd(444);
        http_response($url, '200',
            3); // returns true if the response takes less than 3 seconds and the response code is 200


        dd(4444, http_response($url, '200', 3));

        $status = null;
        $wait = 3;

        $time = microtime(true);
        $expire = $time + $wait;

        // we fork the process so we don't have to wait for a timeout
        $pid = pcntl_fork();
        if ($pid == -1) {

            die('could not fork');
        } else {
            if ($pid) {

                $url = 'https://aqarz.sa/api/call';
                $data = ['uuid' => '8f8aad04-1dae-40b3-89d0-9dc3e2ea1b55'];
                $data_json = json_encode($data);


                $data = ["username" => "Aqarz", 'password' => 'A@qR3Zz#81$']; // data u want to post
                $data_string = json_encode($data);
                // we are the parent
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true); // remove body
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, $data_string);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: */*',
                    'Content-Type: application/json',
                    'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',
                    'Cache-Control: no-cache',
                    'Content-Type: text/plain',
                    'Accept-Encoding: gzip, deflate, br',
                    'Connection: keep-alive',

                ]);
                $head = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


                dd($httpCode);
                curl_close($ch);


                if (!$head) {
                    return false;
                }

                if ($status === null) {
                    if ($httpCode < 400) {
                        return true;
                    } else {
                        return false;
                    }
                } elseif ($status == $httpCode) {
                    return true;
                }

                return false;
                pcntl_wait($status); //Protect against Zombie children
            } else {
                // we are the child
                while (microtime(true) < $expire) {
                    sleep(0.5);
                }
                return false;
            }
        }


        //  dd( $_SERVER['SERVER_ADDR']);
        $url = 'https://aqarz.sa/api/call';
        $data = ['uuid' => '8f8aad04-1dae-40b3-89d0-9dc3e2ea1b55'];
        $data_json = json_encode($data);


        $data = ["username" => "Aqarz", 'password' => 'A@qR3Zz#81$']; // data u want to post
        $data_string = json_encode($data);
        //$api_key = "your_api_key";
        // $password = "A@qR3Zz#81$";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $data_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: */*',
                'Content-Type: application/json',
                'Authorization: Basic QXFhcno6QUBxUjNaeiM4MSQ=',
                'Cache-Control: no-cache',
                'Content-Type: text/plain',
                'Accept-Encoding: gzip, deflate, br',
                'Connection: keep-alive',

            ]
        );


        if (curl_exec($ch) === false) {
            echo 'Curl error: ' . curl_error($ch);
            dd(curl_exec($ch));
        }
        $errors = curl_error($ch);
        $result = curl_exec($ch);
        $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo $returnCode;
        var_dump($errors);
        print_r(json_decode($result, true));


        dd(444);


        $headers = [
            'Content-Type:application/json',
            'Authorization: Basic ' . base64_encode("Aqarz:A@qR3Zz#81$") // <---
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_USERPWD, "Aqarz:A@qR3Zz#81$");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $response = file_get_contents($response);
        curl_close($ch);

        dd($response);


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, 'Aqarz' . ":" . 'A@qR3Zz#81$');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);

        dd($return);
    }

}
