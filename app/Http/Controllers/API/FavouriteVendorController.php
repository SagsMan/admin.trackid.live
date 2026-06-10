<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouriteVendorController extends Controller
{

    public function index(Request $request)
    {
        $favourites = Favourite::with('vendor')
            ->where('user_id', Auth::id())
            ->whereNotNull('vendor_id')
            ->get();
        return $favourites;
    }

    public function store(Request $request)
    {

        try {

            $model = Favourite::where('user_id', Auth::id())->where('vendor_id', $request->vendor_id)->first();
            if (!empty($model)) {
                return response()->json([
                    "message" => __("Vendor already Favourite")
                ], 400);
            }

            $model = new Favourite();
            $model->user_id = Auth::id();
            $model->vendor_id = $request->vendor_id;
            $model->save();

            return response()->json([
                "message" => __("Favourite added successfully")
            ], 200);
        } catch (\Exception $ex) {

            return response()->json([
                "message" => __("No Favourite Found")
            ], 400);
        }
    }

    public function destroy(Request $request, $id)
    {

        try {

            Favourite::where('user_id',  Auth::id())->where('vendor_id', $id)
                ->firstorfail()
                ->delete();
            return response()->json([
                "message" => __("Favourite deleted successfully")
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => __("No Favourite Found")
            ], 400);
        }
    }
}
