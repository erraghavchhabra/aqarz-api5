<?php

namespace App\Http\Controllers\DashBoard;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\CitiesResource;
use App\Http\Resources\Platform\DistrictAllDataResource;
use App\Http\Resources\Platform\DistrictResource;
use App\Models\v4\Cities;
use App\Models\v4\District;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page_number', 15);

        $district = District::orderBy('district_id', 'desc')->paginate($page);
        return response()->success(__("views.Done"), DistrictResource::collection($district)->response()->getData(true));
    }

    public function show($id)
    {
        $district = District::find($id);
        if (!$district)
            return JsonResponse::fail(__('views.not found'));
        return response()->success(__("views.Done"), new DistrictAllDataResource($district));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|exists:cities_final,id',
            'name_ar' => 'required',
            'name_en' => 'required',
            'boundaries' => 'required|json',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }
        $district_id = District::max('district_id');
        $district_id = $district_id + 1;
        $request->merge(['district_id' => $district_id]);
        $city = Cities::find($request->city_id);
        $request->merge(['region_id' => $city->region_id]);
        $district = District::create($request->except('boundaries'));

        if ($request->boundaries) {
            $boundaries = json_decode($request->boundaries);
            $polygons = [];
            foreach ($boundaries as $boundary) {
                if (count($boundary) == 2) {
                    $polygons[] = new Point($boundary[0], $boundary[1]);
                } else {
                    return response()->error(__("views.boundaries error"));
                }
            }
            $polygons = implode(',', $polygons);
            $district = District::find($district_id);
            $district->boundaries = \DB::raw("ST_GeomFromText('POLYGON(($polygons))')");
            $district->save();

//            if ($district->boundaries != null)
//            {
//                dd($district->boundaries[0]);
//                $polygon = $district->boundaries[0]->jsonSerialize()->getCoordinates();
//                $NumPoints = count($polygon);
//                if ($polygon[$NumPoints - 1] == $polygon[0]) {
//                    $NumPoints--;
//                } else {
//                    $polygon[$NumPoints] = $polygon[0];
//                }
//
//                $x = 0;
//                $y = 0;
//
//                $lastPoint = $polygon[$NumPoints - 1];
//                for ($i = 0; $i <= $NumPoints - 1; $i++) {
//                    $point = $polygon[$i];
//                    $x += ($lastPoint[0] + $point[0]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
//                    $y += ($lastPoint[1] + $point[1]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
//                    $lastPoint = $point;
//                }
//                $path = ComputeArea($polygon);
//                $x /= -6 * $path;
//                $y /= -6 * $path;
//                $locationForSave = $y . ',' . $x;
//                $district->center = $locationForSave;
//                $district->save();
//
//            }
        }



        return response()->success(__("views.Done"), $district);
    }

    public function update(Request $request, $id)
    {
        $district = District::find($id);
        if (!$district)
            return JsonResponse::fail(__('views.not found'));

        if ($request->city_id)
        {
            $city = Cities::find($request->city_id);
            $request->merge(['region_id' => $city->region_id]);
        }
        $district->update($request->except('boundaries'));
        if ($request->boundaries) {
            $boundaries = json_decode($request->boundaries);
            $polygons = [];
            foreach ($boundaries as $boundary) {
                if (count($boundary) == 2) {
                    $polygons[] = new Point($boundary[0], $boundary[1]);
                } else {
                    return response()->error(__("views.boundaries error"));
                }
            }
            $polygons = implode(',', $polygons);
            $district->boundaries = \DB::raw("ST_GeomFromText('POLYGON(($polygons))')");


            $district->save();

//            if ($district->boundaries != null)
//            {
//                dd($district->boundaries);
//                $polygon = $district->boundaries[0]->jsonSerialize()->getCoordinates();
//                $NumPoints = count($polygon);
//                if ($polygon[$NumPoints - 1] == $polygon[0]) {
//                    $NumPoints--;
//                } else {
//                    $polygon[$NumPoints] = $polygon[0];
//                }
//
//                $x = 0;
//                $y = 0;
//
//                $lastPoint = $polygon[$NumPoints - 1];
//                for ($i = 0; $i <= $NumPoints - 1; $i++) {
//                    $point = $polygon[$i];
//                    $x += ($lastPoint[0] + $point[0]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
//                    $y += ($lastPoint[1] + $point[1]) * ($lastPoint[0] * $point[1] - $point[0] * $lastPoint[1]);
//                    $lastPoint = $point;
//                }
//                $path = ComputeArea($polygon);
//                $x /= -6 * $path;
//                $y /= -6 * $path;
//                $locationForSave = $y . ',' . $x;
//                $district->center = $locationForSave;
//                $district->save();
//
//            }
        }
        return response()->success(__("views.Done"), $district);
    }

    public function destroy($id)
    {
        $district = District::find($id);
        if (!$district)
            return JsonResponse::fail(__('views.not found'));

        $district->delete();
        return response()->success(__("views.Done"));
    }
}
