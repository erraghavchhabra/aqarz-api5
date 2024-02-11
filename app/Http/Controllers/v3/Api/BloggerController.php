<?php

namespace App\Http\Controllers\v3\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BloggerResource;
use App\Models\v3\Blogger;
use App\Models\v3\BloggerCommet;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Config;



class BloggerController extends Controller
{

    public function bloggers(Request $request)
    {
        $bloogers = Blogger::where('status', '1');
        if ($request->get('search')) {
            $search = trim($request->get('search'));
            $bloogers = $bloogers
                ->where('body', 'like', '%' . $search . '%')
                ->orwhere('title', 'like', '%' . $search . '%');

        }
        $bloogers = $bloogers->paginate();
      //  $bloogers_similer = Blogger::where('status', '1')->limit(5)->get();

       // $collection = BloggerResource::collection($bloogers);

       // $array = ['bloggers' => $collection, 'similar' => $bloogers_similer];
        return response()->success(__("views.Deleted Successfully"), $bloogers);
    }

    public function blogger_show(Request $request, $id)
    {

        $rules = Validator::make($request->all(), [


            // 'blogger_id' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $bloogers = Blogger::
        where('status', '1')
            ->where('id', $id);

        if ($request->get('search')) {
            $search = trim($request->get('search'));
            $bloogers = $bloogers
                ->where('body', 'like', '%' . $search . '%')
                ->orwhere('title', 'like', '%' . $search . '%');

        }
        $bloogers = $bloogers->with('comments.sub_comments')
            ->with('comments')
            ->first();


      //  $collection = BloggerResource::collection($bloogers);
        $bloogers_similer = Blogger::where('status', '1')->limit(5)->get();

        $array = ['bloggers' => $bloogers, 'similar' => $bloogers_similer];

        return response()->success(__("views.Deleted Successfully"), $array);
    }

    public function blogger_commet_send(Request $request)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [


            'blogger_id' => 'required',
            'parent_id' => 'sometimes|required',
            'commet' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $bloogers = Blogger::where('id', $request->get('blogger_id'))->first();

        /*  if ($bloo gers->count_commet > 2) {
              return JsonResponse::fail('لايمكنك اضافة تعليق اخر', 400);
          }*/
        $bloogers->count_commet = $bloogers->count_commet + 1;
        $bloogers->save();
        $request->merge([

            'user_id' => $user->id,

        ]);



        $BloggerCommet = BloggerCommet::create($request->only([
            'blogger_id',
            'parent_id',
            'commet',
            'user_id',
            'status',
        ]));


        $BloggerCommet = BloggerCommet::with('sub_comments')->find($BloggerCommet->id);
        return response()->success(__("views.Done"), $BloggerCommet);
    }

    public function blogger_commet_edit(Request $request,$id)
    {


        $user = auth()->user();
        if ($user == null) {
            return response()->error(__("views.not authorized"));
        }
        $rules = Validator::make($request->all(), [

            'commet_id' => 'required',
            'commet' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

      $comment=BloggerCommet::find($request->get('commet_id'));

        if(!$comment)
        {
            return response()->error(__("views.not found"));
        }

        $request->merge([

            'user_id' => $user->id,

        ]);



        $BloggerCommet = $comment->update($request->only([
            'commet',
        ]));


        $BloggerCommet = BloggerCommet::with('sub_comments')->find($request->get('commet_id'));
        return response()->success(__("views.Done"), $BloggerCommet);
    }

    public function blogger_commet_remove(Request $request)
    {

        $rules = Validator::make($request->all(), [


            'commet_id' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $BloggerCommet = BloggerCommet::where('id', $request->get('commet_id'))->first();
        if (!$BloggerCommet) {
            return JsonResponse::fail('لايمكن العثور على التعليق', 400);
        }
        $Blogger = Blogger::where('id', $BloggerCommet->blogger_id)->first();
        if (!$Blogger) {
            return JsonResponse::fail('لايمكن العثور على المدونة', 400);
        }
        $Blogger->count_commet = $Blogger->count_commet - 1;
        $Blogger->save();
        $BloggerCommet->delete();

        return response()->success(__("views.Done"), []);
    }
}
