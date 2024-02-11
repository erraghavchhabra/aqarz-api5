<?php

namespace App\Http\Controllers\DashBoard;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\CitiesResource;
use App\Http\Resources\Dashboard\NewsResource;
use App\Models\v4\Cities;
use App\Models\v4\News;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }

        $page = $request->get('page_number', 15);

        $news = News::orderBy('id', 'desc')->paginate($page);
        return response()->success(__("views.Done"), NewsResource::collection($news)->response()->getData(true));
    }

    public function store(Request $request)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }

        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|min:3',
            'title_en' => 'required|min:3',
            'description_ar' => 'required|min:3',
            'description_en' => 'required|min:3',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }


        $request->merge([
            'user_id' => $user->id,
        ]);

        $news = News::create($request->all());

        $image = $request->file('image');
        $path = $image->store('images/news', 's3');
        $news->image = 'https://aqarz.s3.me-south-1.amazonaws.com/' .$path;
        $news->save();

        return response()->success(__("views.Done"), new NewsResource($news));
    }

    public function show($id)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }

        $news = News::find($id);
        if (!$news)
            return JsonResponse::fail(__('views.not found'));

        return response()->success(__("views.Done"), new NewsResource($news));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }

        $validator = Validator::make($request->all(), [
            'title_ar' => 'sometimes|required|min:3',
            'title_en' => 'sometimes|required|min:3',
            'description_ar' => 'sometimes|required|min:3',
            'description_en' => 'sometimes|required|min:3',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        if ($validator->fails()) {
            return response()->error($validator->errors()->first());
        }
        $news = News::find($id);
        if (!$news)
            return JsonResponse::fail(__('views.not found'));

        $news->update($request->all());

        if ($request->image) {
            $image = $request->file('image');
            $path = $image->store('images/news', 's3');
            $news->image = 'https://aqarz.s3.me-south-1.amazonaws.com/' .$path;
            $news->save();
        }

        return response()->success(__("views.Done"), new NewsResource($news));
    }

    public function destroy($id)
    {
        $user = auth()->guard('Admin')->user();
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }

        $news = News::find($id);
        if (!$news)
            return JsonResponse::fail(__('views.not found'));

        $news->delete();
        return response()->success(__("views.Done"));
    }
}
