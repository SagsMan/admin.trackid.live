<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DriverLocationSyncController extends Controller
{

    //
    public function store(Request $request)
    {

        //prevent calling this if websocket option is not enabled
        //TODO: Add this for production release
        if (!isUsingWebsocket()) {
            logger("Driver location syncing but websocket system is not allowed");
            return response()->json([
                "message" => __("Service not allowed"),
            ], 400);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'rotation' => 'nullable|numeric',
            ],
        );

        if ($validator->fails()) {
            return response()->json([
                "message" => $this->readalbeError($validator),
            ], 400);
        }

        try {
            DriverLocation::updateOrCreate(
                ['driver_id' => Auth::id()],
                [
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'rotation' => $request->rotation ?? 0,
                ]
            );

            return response()->json([
                "success" => true,
            ], 200);
        } catch (Exception $ex) {
            logger("Error syncing driver location", [$ex]);
            return response()->json([
                "message" => __("Try again later"),
            ], 500);
        }
    }
}