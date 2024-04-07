<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Day;
use App\Models\DayComplete;
use App\Models\DayExercise;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DayController extends Controller
{
    public function index(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'request_type' => 'required',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            if($request->has('request_type') && $request->request_type == 'admin' && !$request->has('person_id')){
                $days = Day::where('program_id', $id)->where('user_id', null)->get();

                if($days){
                    return successResponse($days, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->has('request_type') && $request->request_type == 'admin' && $request->has('person_id')){
                $userId = $request->person_id;
                $days = Day::with(['dayCompleted' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }])
                    ->where('program_id', $id)
                    ->whereNull('user_id')
                    ->get();

                $userDay = Day::where('program_id', $id)->where('user_id', $request->person_id)->get();
                $allUserDays = $days->merge($userDay);

                foreach ($allUserDays as $day){
                    if(!empty($day->dayCompleted)){
                        $day['is_completed'] = $day->dayCompleted->is_completed;
                    }
                    else{
                        $day['is_completed'] = 'false';
                    }
                    unset($day->dayCompleted);
                }

                if($allUserDays){
                    return successResponse($allUserDays, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->has('request_type') && $request->request_type == 'user'){
                $days = Day::where('program_id', $id)->where('user_id', null)->get();
                $userDay = Day::where('program_id', $id)->where('user_id', $request->user_id)->get();
                $allUserDays = $days->merge($userDay);

                if($allUserDays){
                    return successResponse($allUserDays, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'day_name' => 'required | string | unique:days',
            'program_id' => 'required | numeric',
            'request_type' => 'required'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            if($request->user_id == 'null'){
                $postData = $request->except('user_id');
            }else{
                $postData = $request->all();
            }

            $result = Day::create($postData);
            if($result){
                return successResponse($result, 'Successfully created.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function edit($id){
        try{
            $day = Day::where('id', $id)->first();

            if($day){
                return successResponse($day, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'day_name' => 'required | string',
            'request_type' => 'required'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            if($request->request_type == 'user' && Day::where('id', $id)->where('user_id', $request->user_id)->exists()){
                $result = Day::where('id', $id)->update(['day_name' => $request->day_name]);
                if($result == '1'){
                    return successResponse(null, 'Successfully updated.');
                }
                else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->request_type == 'admin'){
                $result = Day::where('id', $id)->update(['day_name' => $request->day_name]);
                if($result == '1'){
                    return successResponse(null, 'Successfully updated.');
                }
                else{
                    return errorResponse('Something went wrong.');
                }
            }

            return errorResponse('You are not allowed to update it.');

        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function destroy(Request $request, $id){
        try {
            if ($request->request_type == 'user') {
                $daysIds = Day::where('program_id', $id)
                    ->where('user_id', $request->user_id)
                    ->pluck('id');

                if ($daysIds->isNotEmpty()) {
                    $dayExercises = DayExercise::whereIn('day_id', $daysIds)
                        ->where('user_id', $request->user_id)
                        ->pluck('id');

                    Day::where('program_id', $id)
                        ->where('user_id', $request->user_id)
                        ->delete();

                    if ($dayExercises->isNotEmpty()) {
                        $setsIds = Set::whereIn('day_exercise_id', $dayExercises)
                            ->where('user_id', $request->user_id)
                            ->pluck('id');

                        DayExercise::whereIn('day_id', $daysIds)
                            ->where('user_id', $request->user_id)
                            ->delete();

                        if ($setsIds->isNotEmpty()) {
                            Set::whereIn('day_exercise_id', $dayExercises)
                                ->where('user_id', $request->user_id)
                                ->delete();
                        }
                    }
                    return successResponse(null, 'Successfully deleted.');
                } else {
                    return errorResponse('Day not found.');
                }
            }

            if($request->request_type == 'admin'){
                $result = Day::where('id', $id)->delete();

                if($result == '1'){
                    return successResponse(null, 'Successfully deleted.');
                }
                else{
                    return errorResponse('Something went wrong.');
                }
            }

            return errorResponse('You are not allowed to delete it.');

        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
    public function isCompeted(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'program_id' => 'required | numeric',
            'user_id' => 'required | numeric',
            'is_completed' => 'required | string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            if($request->request_type == 'user'){
                $postData = $request->except('request_type');
                $postData['day_id'] = $id;

                $result = DayComplete::create($postData);

                if($result){
                    return successResponse($result, 'Successfully completed.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
