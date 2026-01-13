<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ForgotPassword;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['forgotPassword', 'verifyOtp', 'resetPassword']]);
    }

    public function forgotPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'staffid' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($validator->validated()) {
            $user = DB::table('users')
                ->select('staffid', 'mobile')
                ->where('staffid', $request->staffid)
                ->where('active', 'Y')
                ->where('usertype', 'U')
                ->first();
            if ($user) {
                $four_digit_random_number = mt_rand(1000, 9999);     //uncomment before deploy
                // $four_digit_random_number = 1234;
                $storeOtp = new ForgotPassword();
                $storeOtp->StaffID     = $user->staffid;
                $storeOtp->MobileNo     = $user->mobile;
                $storeOtp->OTP          = $four_digit_random_number;
                $storeOtp->OTPTime      = Carbon::now();
                $storeOtp->OTPConfirm   = 'N';
                $storeOtp->save();

                $smscontent = "Your OTP for password reset is " . $four_digit_random_number . ". It will expire after 180 seconds. Do not share this OTP with anyone as it is confidential.";
                // $respons = $this->sendsms($ip = env('sms_ip'), $userid = env('sms_userid'), $password = env('sms_password'), $smstext = urlencode($smscontent), $receipient = urlencode($user->mobile));
                // print_r($respons); exit();
                return response([
                    'status' => true,
                    'message' => 'A four digit code has been sent to your registered mobile number',
                    'mobile' => $user->mobile,
                    'staffid' => $user->staffid,
                    'OTP'   =>  $four_digit_random_number
                ], 200)->header('Content-Type', 'application/json');
            } else {
                return response(['status' => false, 'message' => 'Invalid User'], 200)->header('Content-Type', 'application/json');
            }
        }
        return false;
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffid' => 'required',
            'mobile' => 'required|min:11',
            'otp'   => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($validator->validated()) {
            $info = DB::select(DB::raw("SELECT TOP 1 StaffID, MobileNo, DATEDIFF(MINUTE,OTPTime,GETDATE()) AS OTPTime
                                        FROM OTPGeneration WHERE MobileNo = '$request->mobile'
                                        AND StaffID = '$request->staffid'
                                        AND DATEDIFF(MINUTE,OTPTime,GETDATE()) <= 3
                                        AND OTP = '$request->otp'
                                        AND OTPConfirm = 'N'
                                        ORDER BY 1 DESC"));

            if ($info) {
                DB::table('OTPGeneration')
                    ->where('OTP', $request->otp)
                    ->where('MobileNo', $info[0]->MobileNo)
                    ->where('StaffID', $info[0]->StaffID)
                    ->update(['OTPConfirm' => 'Y']);

                return response()->json([
                    'status' => true,
                    'message' => 'Confirm..',
                    'mobile' => $info[0]->MobileNo,
                    'staffid' => $info[0]->StaffID
                ], 200)->header('Content-Type', 'application/json');
            } else {
                return response()->json(['status' => false, 'message' => 'Invalid!'], 400)->header('Content-Type', 'application/json');
            }
        }
        return false;
    }

    public function resetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'staffid' => 'required',
            'newpassword'   => 'required|min:6',
            'confirmpassword' => ['same:newpassword']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($validator->validated()) {
            User::where('staffid', $request->staffid)
                ->where('active', 'Y')
                ->update(['password' => bcrypt($request->newpassword), 'updated_at'=>Carbon::now(), 'updated_by'=>$request->staffid]);

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully'
            ], 200)
                ->header('Content-Type', 'application/json');
        } else {
            return response()->json(['status' => false, 'message' => 'Something went wrong!'], 400)->header('Content-Type', 'application/json');
        }
        return false;
    }

    private function sendsms($ip, $userid, $password, $smstext, $receipient)
    {
        $smsUrl = "http://{$ip}/httpapi/sendsms?userId={$userid}&password={$password}&smsText=" . $smstext . "&commaSeperatedReceiverNumbers=" . $receipient;
        //echo $smsUrl; exit();
        $response = file_get_contents($smsUrl);
        //print_r($response); exit();
        return json_decode($response);
    }
}
