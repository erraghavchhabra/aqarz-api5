<?php

namespace App\Http\Controllers\DashBoard\Admin;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

use App\Http\Resources\ReportResource;
use App\Models\dashboard\Admin;
use App\Models\v3\Report;
use App\Models\v3\Ticket;
use App\Models\v3\TicketChat;
use App\User;
use Auth;

use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class TicketController extends Controller
{


    public function AllTicket(Request $request)
    {

        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = Admin::find($user->id);
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $tickets = Ticket::with('user')
            ->with('estate')
            ->orderBy('id', 'desc')->get();


        return response()->success(__("views.Done"), $tickets);


    }

    public function AllReports(Request $request)
    {

        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = Admin::find($user->id);
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $tickets = Report::with('user')
            ->with('estate.user')
            ->orderBy('id', 'desc');

        if ($request->page_number && $request->page_number > 0) {
            $tickets = $tickets->paginate($request->page_number);
        } else {
            $tickets = $tickets->paginate();
        }

        //  $tickets = ReportResource::collection($tickets)->response()->getData(true);

        return response()->success(__("views.Done"), $tickets);


    }


    public function TicketShow(Request $request, $id)
    {

        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = Admin::find($user->id);
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $ticket = Ticket::with('ticket_chat')
            ->with('user')
            ->with('estate')
            ->find($id);


        if ($ticket) {
            return response()->success(__("views.Done"), $ticket);
        }

        return response()->error(__('لايوجد تذكرة'));

    }


    public function ReplayTicket(Request $request, $id)
    {


        $rules = Validator::make($request->all(), [


            'message' => 'required',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = Admin::find($user->id);
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $ticket = Ticket::find($id);


        if (!$ticket) {
            return response()->error(__('لايوجد تذكرة'));
        }
        $TicketFirstChat = TicketChat::create([
            'message' => $request->get('message'),
            'replay_admin_id' => $user->id,
            'ticket_id' => $id,
            'from_type' => 'admin',
            'admin_note' => $request->get('admin_note'),

        ]);

        if ($request->hasFile('attachment')) {

            $path = $request->file('attachment')->store('public/ticket/photo', 's3');
            //    $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


            $TicketFirstChat->update(['attachment' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);
        }

        return response()->success(__("views.Done"), $TicketFirstChat);


    }

    public function DeleteTicket(Request $request, $id)
    {

        $user = auth()->guard('Admin')->user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = Admin::find($user->id);
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $ticket = Ticket::find($id);


        if (!$ticket) {
            return response()->error(__('لايوجد تذكرة'));
        }


        $TicketFirstChat = TicketChat::where('ticket_id', $id)->delete();
        $ticket->delete();


        return response()->success(__("views.Done"), $TicketFirstChat);


    }
}
