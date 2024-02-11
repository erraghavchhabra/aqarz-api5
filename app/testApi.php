<?php




$url = 'https://eservices.redf.gov.sa/Aqarz/api/Aqarz/SendSMSwhenReadyOffers';

$data = [
    'uuid' => 'd19dded7-8543-457e-826e-2b504420b2e0',


];
$data_json = json_encode($data);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
curl_setopt($ch, CURLOPT_HEADER, 0);


//  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//      curl_setopt($ch, CURLOPT_STDERR, $out);
curl_setopt($ch, CURLOPT_HTTPHEADER, [

    'Content-Type: application/json',
    'Authorization: Basic YXFhcnpfcDpAcjNRcnojI3V5ITE3',

]);



curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_USERPWD, 'aqarz_p' . ":" . '@r3Qrz##uy!17');
curl_setopt($ch, CURLOPT_TIMEOUT, 300);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
$result = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headerstring = substr($result, 0, $header_size);
$body = substr($result, $header_size);
$err = curl_error($ch);
curl_close($ch);


print_r($err);

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

//return [json_decode($body)];
return [
    'code'   => (json_decode($body)->code),
    'msg'    => (json_decode($body)->message),
    'status' => json_decode($body)->status
];
