<?php

namespace App\Http\Controllers\DashBoard;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\CitiesResource;
use App\Models\v4\Cities;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CitiesController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page_number', 15);

        $cities = Cities::orderBy('id', 'desc')->paginate($page);
        return response()->success(__("views.Done"), CitiesResource::collection($cities)->response()->getData(true));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'region_id' => 'required|exists:regions_final,id',
            'name_ar' => 'required',
            'name_en' => 'required',
            'lat' => 'required',
            'lan' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }
        $city = Cities::create($request->all());
        $city->center = \DB::raw("POINT({$request->lat}, {$request->lan})");
        $city->save();

        return response()->success(__("views.Done"), $city);
    }

    public function show($id)
    {
        $city = Cities::find($id);
        if (!$city)
            return JsonResponse::fail(__('views.not found'));

        return response()->success(__("views.Done"), $city);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'region_id' => 'sometimes|exists:regions_final,id',
            'name_ar' => 'sometimes',
            'name_en' => 'sometimes',
            'lat' => 'sometimes',
            'lan' => 'sometimes',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }
        $city = Cities::find($id);
        if (!$city)
            return JsonResponse::fail(__('views.not found'));

        $city->update($request->all());
        if ($request->has('lat') && $request->has('lan')) {
            $city->center = \DB::raw("POINT({$request->lat}, {$request->lan})");
            $city->save();
        }

        return response()->success(__("views.Done"), $city);
    }

    public function destroy($id)
    {
        $city = Cities::find($id);
        if (!$city)
            return JsonResponse::fail(__('views.not found'));

        $city->delete();
        return response()->success(__("views.Done"));
    }
}
