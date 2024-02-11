<?php

namespace App\Http\Controllers\DashBoard;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\v4\Region;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OfficesController extends Controller
{
    public function update(Request $request , $id)
    {
        $user = User::find($id);

        if (!$user) {
            return JsonResponse::fail(__('views.not found'));
        }

        $rules = Validator::make($request->all(), [
            'email' => 'unique:users,email,' . $user->id . ',id,deleted_at,NULL',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request->merge([
            'from_app' => false,
            'status' => 0,
            'user_name' => $request->get('user_name') != null ? $request->get('user_name') : $user->user_name,
            'is_edit_username' => $request->get('user_name') ? '1' : '0',
        ]);

        $user->update($request->only([
            'name',
            'email',
            'advertiser_number',
            'type',
            'device_token',
            'device_type',
            'license_number',
            'services_id',
            'members_id',
            'experiences_id',
            'courses_id',
            'lat',
            'lan',
            'address',
            'user_name',
            'is_edit_username',
            'onwer_name',
            'office_staff',
            'experience',
            'bio',
            'account_type',
            'advertiser_number',
            'show_rate_request',
            'fal_license_expiry',
            'fal_license_number',
        ]));


        $user = User::find($user->id);

        if ($request->company_logo) {
            $path = $request->file('company_logo')->store('public/users/photo', 's3');
            $user->update(['company_logo' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);
        }


        return response()->success(__("views.update Profile success"), $user);
    }

    public function show($id)
    {
        $user = User::with('Iam_information')->find($id);
        if (!$user)
            return JsonResponse::fail(__('views.not found'));
        return response()->success(__("views.Done"), $user);
    }

}
