<?php

namespace App\Http\Controllers\v4\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CheckPointResource;
use App\Http\Resources\v4\NewsCommentResource;
use App\Http\Resources\v4\NewsResource;
use App\Http\Resources\v4\NewsShowResource;
use App\Http\Resources\v4\RealEstateAdviceResources;
use App\Models\v3\AreaEstate;
use App\Models\v3\Bank;
use App\Models\v3\City;
use App\Models\v3\City3;
use App\Models\v3\Comfort;
use App\Models\v3\Contact;
use App\Models\v3\Content;
use App\Models\v3\CourseType;
use App\Models\v3\District;
use App\Models\v3\Employee;
use App\Models\v3\Estate;
use App\Models\v3\EstatePrice;
use App\Models\v3\EstateRequest;
use App\Models\v3\EstateType;
use App\Models\v3\ExperienceType;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\MemberType;
use App\Models\v3\Neighborhood;
use App\Models\v3\NotificationUser;
use App\Models\v3\OprationType;
use App\Models\v3\Region;
use App\Models\v3\Report;
use App\Models\v3\RequestFund;
use App\Models\v3\RequestOffer;
use App\Models\v3\ServiceType;
use App\Models\v3\StreetView;
use App\Models\v3\Ticket;
use App\Models\v3\TicketChat;
use App\Models\v3\Video;
use App\Models\v4\Cities;
use App\Models\v4\News;
use App\Models\v4\NewsComment;
use App\User;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class NewsController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('per_page', 15);
        $news = News::query();
        if ($request->search)
        {
            $news = $news->where('title_ar', 'like', '%' . $request->search . '%')->orWhere('title_en', 'like', '%' . $request->search . '%');
        }
        $news = $news->orderBy('id', 'desc')->paginate($page);
        $data = NewsResource::collection($news)->response()->getData(true);
        return response()->success(__("views.Done"), $data);
    }

    public function show($id)
    {
        $news = News::query()->find($id);
        if (!$news) {
            return response()->error(__('views.not found'));
        }
        $news->view = $news->view + 1;
        $news->save();
        $data = new NewsShowResource($news);
        return response()->success(__("views.Done"), $data);
    }

    //add comment
    public function addComment(Request $request , $id)
    {
        $user = auth()->user();


        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'comment' => 'required|min:3',
            'comment_id' => 'sometimes|exists:news_comments,id',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }
        $request->merge([
            'user_id' => @$user->id,
            'news_id' => $id,
        ]);
        $comment = NewsComment::create($request->all());
        return response()->success(__("views.Done"), new NewsCommentResource($comment));
    }
}
