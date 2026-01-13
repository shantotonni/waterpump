<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EngineerCSIController extends Controller
{
    public function engineerWiseCSI(Request $request)
    {
        try {
            $period = $request->period;
            $data = DB::select("EXEC doLoadEngineerMonthCSIDetailsForWaterpump '$period'");
            return response()->json([
                'status' => true,
                'data' => $data
            ], 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!'
            ],500);
        }

    }
}
