<?php

namespace App\Http\Controllers\Platform;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\UserResource;

use App\Models\v3\Bank;
use App\Models\v3\Comfort;
use App\Models\v3\EstateType;
use App\Models\v3\OprationType;
use App\Unifonic\Client as UnifonicClient;
use App\Unifonic\UnifonicMessage;
use App\User;
use Auth;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use QL\QueryList;


class UserController extends Controller
{

    public function AllTentPayUsers(Request $request)
    {
        $OprationType = OprationType::get();
        return response()->success("OprationType List", $OprationType);
    }

}
