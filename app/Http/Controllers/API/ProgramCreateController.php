<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProgramWorkout;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramCreateController extends Controller
{
    public function index(){
        try {
            $userInformations = ProgramWorkout::with(['programs', 'days'])->orderBy('id', 'DESC')->get();

            foreach ($userInformations as $key => $userInformation) {
                $setIDs = json_decode($userInformation->set_id);

                // Check if set_id is not empty or null
                if (!empty($setIDs)) {
                    $setsData = [];

                    foreach ($setIDs as $setID) {
                        $set = Set::where('id', $setID)->first();

                        if ($set) {
                            $setsData[] = $set->toArray();
                        }
                    }

                    // Create a new array and assign it to the model attribute
                    $userInformations[$key]['sets'] = $setsData;
                    unset($userInformations[$key]->set_id);
                } else {
                    // Handle the case where set_id is empty or null
                    $userInformations[$key]['sets'] = [];
                    unset($userInformations[$key]->set_id);
                }
            }

            if ($userInformations) {
                return successResponse($userInformations, 'Successfully retrieved.');
            } else {
                return errorResponse('Something went wrong.');
            }
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'program_id' => 'required | numeric',
            'day_id' => 'required | numeric',
            'exercise_id' => 'required | numeric',
            'set_id' => 'required | array',
            'set_id.*' => 'required | string',
        ]);

        try{
            if ($validator->fails()) {
                return validationResponse($validator);
            }
            $postData = $request->except('set_id');
            $postData['set_id'] = json_encode($request->set_id);
            $result = ProgramWorkout::create($postData);

            if($result){
                return successResponse(null, 'Successfully created.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
