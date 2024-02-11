<?php

namespace App\Http\Controllers\DashBoard;


use App\Http\Controllers\Controller;
use App\Models\v4\PlatformPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlatformPlansController extends Controller
{
    public function index()
    {
       $plan = PlatformPlan::all();
       return response()->success(__("views.Done"), $plan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|unique:platform_plan|regex:/^[\p{Arabic} ]+$/u',
            'name_en' => 'required|unique:platform_plan|regex:/^[a-zA-Z ]+$/u',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'duration_type' => 'required|in:day,month,year',
            'contract_number' => 'required|numeric',
            'color' => 'required',
            'type' => 'in:static,dynamic',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }
        $plan = PlatformPlan::create($request->all());
        return response()->success(__("views.Done"), $plan);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|unique:platform_plan,name_ar,'.$id.'|regex:/^[\p{Arabic} ]+$/u',
            'name_en' => 'sometimes|unique:platform_plan,name_en,'.$id.'|regex:/^[a-zA-Z ]+$/u',
            'price' => 'numeric|sometimes',
            'duration' => 'numeric|sometimes',
            'duration_type' => 'in:day,month,year|sometimes',
            'contract_number' => 'numeric|sometimes',
            'color' => 'sometimes',
            'type' => 'in:static,dynamic',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }
        $plan = PlatformPlan::find($id);
        $plan->update($request->all());
        return response()->success(__("views.Done"), $plan);
    }

    public function destroy($id)
    {
        $plan = PlatformPlan::find($id);
        if (!$plan)
            return response()->error(__("views.not found"));

        $plan->delete();
        return response()->success(__("views.Done"), null);
    }

}
