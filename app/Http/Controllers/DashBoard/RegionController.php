<?php

namespace App\Http\Controllers\DashBoard;


use App\Http\Controllers\Controller;
use App\Models\v4\Region;

class RegionController extends Controller
{
    public function index()
    {
        $region = Region::orderBy('id', 'desc')->get();
        $array = [];
        foreach ($region as $item) {
            $array[] = ['id' => $item->id, 'value' => $item->name];
        }
        return response()->success(__("views.Done"), $array);
    }

}
