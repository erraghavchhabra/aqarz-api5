<?php
if (!function_exists('setting')) {
    function setting($key)
    {
        $setting = \App\Models\Setting::where('key', $key)->first();
        if ($setting)
            return $setting->value;
        throw new \App\Exceptions\GeneralException($key . ' setting not found');
    }
}
