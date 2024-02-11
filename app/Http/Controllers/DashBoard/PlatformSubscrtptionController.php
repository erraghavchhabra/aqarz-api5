<?php

namespace App\Http\Controllers\DashBoard;


use App\Http\Controllers\Controller;
use App\Models\v4\PlatformSubscriptions;


class PlatformSubscrtptionController extends Controller
{
    public function index()
    {
        $subscription = PlatformSubscriptions::where('status','!=' , 'pending')->with('plan')->get();
        return response()->success(__("views.Done"), $subscription );
    }
}
