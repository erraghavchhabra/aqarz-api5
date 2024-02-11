<?php

validation();
function validation()
{

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://integration-gw.housingapps.sa/nhc/dev/v1/brokerage/AdvertisementValidator?adLicenseNumber='.$_POST['adLicenseNumber'].'&advertiserId='.$_POST['advertiserId'].'&idType=1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'X-IBM-Client-Id: 70d40e16c4d8856eea52957e9ba03d94',
            'X-IBM-Client-Secret: dcf5e41ff06d7c4a6dec0f2afda68b7c',
            'Cookie: .AspNetCore.Culture=c%3Dar%7Cuic%3Dar'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;


}

