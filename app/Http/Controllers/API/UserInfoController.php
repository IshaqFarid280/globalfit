<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\UserInformation;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserInfoController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    public function index(){
        try{
            $userInformations = UserInformation::orderBy('id', 'DESC')->get();

            foreach ($userInformations as $userInformation){
                $userInformation['name'] = $userInformation->user->name;
                $userInformation['experience'] = str_replace('_', ' ', $userInformation->experience);
                $userInformation['focus'] = json_decode($userInformation->focus);
                $userInformation['is_first'] = ($userInformation['is_first'] == '1') ? 'true' : 'false';
                unset($userInformation['user']);
            }

            if($userInformations){
                return successResponse($userInformations, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|unique:user_informations,user_id',
            'gender' => 'required | string',
            'height' => ['required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'weight' => ['required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'goal' => 'required | string',
            'focus' => 'required | array',
            'focus.*' => 'required | string',
            'experience' => 'required | string',
            'equipment' => 'required | string',
            'interest' => 'required | string',
            'device_token' => 'required | string',
        ]);

        try{
            if ($validator->fails()) {
                return validationResponse($validator);
            }
            $postData = $request->except('focus');
            $postData['focus'] = json_encode($request->focus);
            $result = UserInformation::create($postData);

//update subscription is_first
            Subscription::where('user_id', $request->user_id)->update(['is_first' => '1']);

//  notification for user
            $deviceToken = $request->device_token;
            $message = 'New user has been registered';

            $response = $this->firebaseService->sendNotification($deviceToken, $message);
            $responseArray = json_decode($response, true);

//            $notification = Notification::create([
//               'user_id' => $result->user_id,
//               'message' => 'New user has been registered',
//            ]);

            $result['name'] = $result->user->name;
            $result['focus'] = json_decode($result->focus);
            unset($result['user']);

            if($result){
                return successResponse($responseArray, 'Successfully created.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function edit($id){
        try{
            $userInformation = UserInformation::where('id', $id)->first();
            $userInformation['name'] = $userInformation->user->name;
            $userInformation['experience'] = str_replace('_', ' ', $userInformation->experience);
            $userInformation['focus'] = json_decode($userInformation->focus);
            unset($userInformation['user']);

            if($userInformation){
                return successResponse($userInformation, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'gender' => 'required | string',
            'height' => ['required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'weight' => ['required', 'numeric', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'goal' => 'required | string',
            'focus' => 'required | array',
            'focus.*' => 'required | string',
            'experience' => 'required | string',
            'equipment' => 'required | string',
            'interest' => 'required | string'
        ]);

        try{
            if ($validator->fails()) {
                return validationResponse($validator);
            }
            $postData = $request->except('focus');
            $postData['focus'] = json_encode($request->focus);
            $result = UserInformation::where('id', $id)->update($postData);

            if($result == '1'){
                return successResponse(null, 'Successfully updated.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function destroy($id){
        try {
            $result = UserInformation::where('id', $id)->delete();

            if($result){
                return successResponse(null, 'Successfully deleted.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function singleUserInfo($id){

        try{
            $userInformation = UserInformation::where('user_id', $id)->first();
            $userInformation['name'] = $userInformation->user->name;
            $userInformation['experience'] = str_replace('_', ' ', $userInformation->experience);
            $userInformation['focus'] = json_decode($userInformation->focus);
            unset($userInformation['user']);

            if($userInformation){
                return successResponse($userInformation, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
