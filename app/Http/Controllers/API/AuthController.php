<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\SendEmail;
use App\Models\DeviceToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signUp(Request $request){
        $validator = Validator::make($request->all(), [
//            'name' => 'required | string | max:30 | regex:/(^([a-zA-Z]+)(\d+)?$)/u',
            'name' => 'required | string | max:30',
            'email' => 'required | string | unique:users,email',
            'password' => 'required | string | confirmed',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $postData = $request->except(['password', 'password_confirmation']);
            $postData['password'] = password_hash($request->password, PASSWORD_BCRYPT);
            $user = User::create($postData);

            if($user){
                return successResponse($user, 'Successfully registered.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required | string | exists:users',
            'password' => 'required | string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $user = User::where('email', $request->email)->first();

            if(Hash::check($request->password, $user->password)){
                $token = md5(time());
                DeviceToken::create([
                    'token' => $token
                ]);

                $user['device_token'] = $token;

                return successResponse($user, 'Successfully login.');
            }
            else{
                return errorResponse('Password do not match.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function logout(Request $request){
        $deviceToken = DeviceToken::where('token', $request->bearerToken())->first();

        if($deviceToken){
            $result = $deviceToken->delete();
            if($result){
                return successResponse(null, 'Successfully logout.');
            }else{
                return errorResponse('Something went wrong.');
            }
        }
        else{
            return errorResponse('Device not found.');
        }
    }
    public function forget(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required | string | exists:users',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $verify_code = mt_rand(10000,99999);

            $mailData = [
                'verifyCode' => $verify_code
            ];

            Mail::to($request->email)->send(new SendEmail($mailData));

            $user = User::where('email', $request->email)->update(['verification_code' => $verify_code, 'code_expire_at' => date('Y-m-d H:i:s')]);

            if($user){
                return successResponse(null, 'Verification code has been sent to your registered email.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function codeVerifiy(Request $request){
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required | numeric',
            'email' => 'required | email',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $user = User::where('email', $request->email)->where('verification_code', $request->verification_code)->first();

            if (!$user) {
                return errorResponse('Invalid code.');
            }

            $expiryTime = $user->code_expire_at;
            $expiryTimeWithBuffer = Carbon::parse($expiryTime)->addMinutes(30);

            if (Carbon::now()->greaterThan($expiryTimeWithBuffer)) {
                return errorResponse('Verification code expired.');
            } else {
                User::where('id', $user->id)->update([
                    'verification_code' => null,
                    'code_expire_at' => null,
                ]);

                return successResponse(null, 'Successfully verified.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function newPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => 'required | string | min:8 | confirmed',
            'email' => 'required | email',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $user = User::where('email', $request->email)->update(['password' => password_hash($request->password, PASSWORD_BCRYPT)]);

            if($user){
                return successResponse(null, 'Password has been reset successfully.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
