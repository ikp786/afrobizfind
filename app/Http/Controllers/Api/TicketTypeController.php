<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketTypeController extends Controller
{
    public function store(Request $request)
    {

        
        $request->data = json_decode($request->data);
        $request->deleted_types = json_decode($request->deleted_types);
        // dd(($request->deleted_types));

        $validator = Validator::make($request->all(), [
            'data.*.id' => 'nullable',
            'data.*.type' => 'required',
            'data.*.details' => 'required',
            'data.*.price' => 'required',
            'data.*.event_id' => 'required',
            'deleted_types' => '',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error", 'errors' => $validator->errors()->messages()]);
        }
        try {

            if (isset($request->deleted_types) && count($request->deleted_types) > 0) {
                TicketType::destroy($request->deleted_types);
            }

            foreach ($request->data as $row) {
                $ticketType = TicketType::where('id', $row->id)->first();
                $ticketType = $ticketType ? $ticketType : new TicketType();

                $ticketType->type = $row->type;
                $ticketType->details = $row->details;
                $ticketType->price = $row->price;
                $ticketType->event_id = $row->event_id;

                $ticketType->save();
            }
            return response()->json([
                'result' => 1,
                'message' => 'Ticket types saved successfully.'
            ], 200);
        } catch (\Throwable $th) {
            logger($th);
            return response()->json([
                'result' => 0,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function eventWise($eventId)
    {
        try {

            $ticketTypes = TicketType::where('event_id', $eventId)
                ->select(
                    'id',
                    'type',
                    'details',
                    'price',
                    'event_id'
                )
                ->get();

            return response()->json([
                'result' => count($ticketTypes) > 0 ? 1 : 0,
                'message' => count($ticketTypes) > 0 ?  'Ticket types Found' : 'Ticket types not Found.',
                'ticket_types' => $ticketTypes,
            ], 200);
        } catch (\Throwable $th) {
            logger($th->getMessage());
            return response()->json([
                'result' => 0,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function oneType($typeId)
    {
        try {

            $ticketType = TicketType::where('id', $typeId)
                ->select(
                    'id',
                    'type',
                    'details',
                    'price',
                    'event_id'
                )
                ->first();

            return response()->json([
                'result' => $ticketType ? 1 : 0,
                'message' => $ticketType ?  'Ticket type Found' : 'Ticket type not Found.',
                'ticket_type' => $ticketType,
            ], 200);
        } catch (\Throwable $th) {
            logger($th->getMessage());
            return response()->json([
                'result' => 0,
                'message' => 'Internal server error'
            ], 500);
        }
    }
}
