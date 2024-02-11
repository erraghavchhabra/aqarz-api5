<?php

namespace App\Http\Controllers\v3\Api;


use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;

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
    public function openTicket(Request $request)
    {
        $rules = Validator::make($request->all(), [


            'body' => 'required',
            //   'estate_id' => 'required',
            'priority' => 'required',
            'type' => 'required',
            'estate_id' => 'required|exists:estates,id',


        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $user = \Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $Ticket = Ticket::create([
            'body' => $request->get('body'),
            'user_id' => $user->id,
            'estate_id' => $request->get('estate_id'),
            'priority' => $request->get('priority'),
            'type' => $request->get('priority'),

        ]);
        $TicketFirstChat = TicketChat::create([
            'message' => $request->get('body'),
            'user_id' => $user->id,
            'ticket_id' => $Ticket->id,
            'from_type' => 'user',

        ]);

        if ($request->hasFile('attachment')) {

            $path = $request->file('attachment')->store('public/ticket/photo', 's3');
            //    $estate->instrument_file = 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path;


            $Ticket->update(['attachment' => 'https://aqarz.s3.me-south-1.amazonaws.com/' . $path]);
        }

        return response()->success(__("views.Done"), $Ticket);


    }

    public function myTicket(Request $request)
    {

        $user = \Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = User::find($user->id);
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $tickets = $user->tickets()->orderBy('id', 'desc')->get();


        return response()->success(__("views.Done"), $tickets);


    }

    public function TicketShow(Request $request, $id)
    {

        $user = \Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $user = User::find($user->id);
        if (!$user) {
            return response()->error(__('views.not authorized'));
        }
        $ticket = $user->tickets()->with('ticket_chat')->find($id);


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
        $user = \Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $ticket = $user->tickets()->find($id);


        if (!$ticket) {
            return response()->error(__('لايوجد تذكرة'));
        }
        $TicketFirstChat = TicketChat::create([
            'message' => $request->get('message'),
            'user_id' => $user->id,
            'ticket_id' => $id,
            'from_type' => 'user',

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

        $user = \Auth::user();
        if ($user == null) {
            return response()->error(__('views.not authorized'));
        }
        $ticket = $user->tickets()->find($id);


        if (!$ticket) {
            return response()->error(__('لايوجد تذكرة'));
        }
        $TicketFirstChat = TicketChat::where('ticket_id', $id)->delete();
        $ticket->delete();


        return response()->success(__("views.Done"), $TicketFirstChat);


    }
}
