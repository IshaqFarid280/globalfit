<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTokenController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required | string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $existingToken = DeviceToken::find(1);

            if ($existingToken) {
                $result = $existingToken->update(['token' => $request->token]);
            } else {
                $result = DeviceToken::create(['token' => $request->token]);
            }

            if ($result) {
                return successResponse(null, 'Successfully stored.');
            } else {
                return errorResponse('An error occurred while storing the token.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function index(){
        $result = DeviceToken::all();

        if($result){
            return successResponse($result, 'Successfully retrieved.');
        } else{
            return errorResponse('Something went wrong.');
        }
    }
}
