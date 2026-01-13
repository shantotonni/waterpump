<?php

namespace App\Http\Controllers;

use App\Models\ServiceMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PointDetails;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PointDetailsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function addPoint(Request $request){
        // $input = $request->all();
        // print_r($input);die;
        $validator = Validator::make($request->all(), [
            'StaffID' => 'required',
            'ServiceMasterID' => 'required',
            'Point' => 'required',
            'EntryBy' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $point = $request->Point;
        $feedback = '';
        if($point==5){
            $feedback = 'Satisfied';
        }elseif($point==4){
            $feedback = 'Moderately Satisfied';
        }elseif($point==3){
            $feedback = 'Dissatisfied / Others';
        }elseif($point==2){
            $feedback = 'Dissatisfied';
        }else{
            $feedback = 'Not Available';
        }

        PointDetails::create(
            array_merge(
                $validator->validated(),
                ['Feedback' => $feedback],
                ['EntryDate' => Carbon::now()],
            )
        );
        return response()->json(['status' => true, 'message' => 'Point Added Successfully'], 200)
                            ->header('Content-Type', 'application/json');
    }

    public function addRemarks(Request $request){
        $service_master = ServiceMaster::find($request->master_id);
        $service_master->Remarks = $request->Remarks;
        $service_master->save();
        return response()->json([
            'message' => 'Successfully Added'
        ],200);
    }

    protected function guard()
    {
        return Auth::guard('api');
    }
}
