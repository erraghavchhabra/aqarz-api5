<?php


namespace App\Helpers;


class CouponHelper
{


    static function generateCouponCode()
    {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $res = "";
        for ($i = 0; $i < 10; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $res;
    }

}

if(!function_exists('generate_coupon_code')){
    function generate_coupon_code(){
        return CouponHelper::generateCouponCode();
    }
}
