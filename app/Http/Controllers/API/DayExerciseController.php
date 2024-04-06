<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DayExercise;
use App\Models\DayExerciseComplete;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DayExerciseController extends Controller
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
                $dayExercises = DayExercise::where('day_id', $id)->where('user_id', null)->get();

                if($dayExercises){
                    return successResponse($dayExercises, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->has('request_type') && $request->request_type == 'admin' && $request->has('person_id')){
                $userId = $request->person_id;
                $dayExercises = DayExercise::with(['dayExerciseCompleted' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }])
                    ->where('day_id', $id)
                    ->whereNull('user_id')
                    ->get();

//                $dayExercises = DayExercise::with('dayExerciseCompleted')->where('day_id', $id)->where('user_id', null)->get();
                $userDayExercises = DayExercise::where('day_id', $id)->where('user_id', $request->person_id)->get();
                $alluserDayExercises = $dayExercises->merge($userDayExercises);

                foreach ($alluserDayExercises as $dayExercise){
                    if(!empty($dayExercise->dayExerciseCompleted)){
                        $dayExercise['is_completed'] = $dayExercise->dayExerciseCompleted->is_completed;
                    }
                    else{
                        $dayExercise['is_completed'] = 'false';
                    }
                    unset($dayExercise->dayExerciseCompleted);
                }

                if($alluserDayExercises){
                    return successResponse($alluserDayExercises, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->has('request_type') && $request->request_type == 'user'){
                $dayExercises = DayExercise::where('day_id', $id)->where('user_id', null)->get();
                $userDayExercises = DayExercise::where('day_id', $id)->where('user_id', $request->user_id)->get();
                $alluserDayExercises = $dayExercises->merge($userDayExercises);

                if($alluserDayExercises){
                    return successResponse($alluserDayExercises, 'Successfully retrieved.');
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
            'day_id' => 'required | numeric',
            'exercise_name' => 'required | string',
            'exercise_image' => 'required | string',
            'exercise_description' => 'required | string',
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

            $result = DayExercise::create($postData);
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
            $day_exercise = DayExercise::where('id', $id)->first();

            if($day_exercise){
                return successResponse($day_exercise, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'exercise_name' => 'required | string',
            'request_type' => 'required'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            if($request->user_id == 'null'){
                $postData = $request->except(['user_id', 'request_type']);
            }else{
                $postData = $request->except('request_type');
            }

            if($request->request_type == 'user' && DayExercise::where('id', $id)->where('user_id', $request->user_id)->exists()){
                $result = DayExercise::where('id', $id)->update($postData);

                if($result == '1'){
                    return successResponse(null, 'Successfully updated.');
                }
                else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->request_type == 'admin'){
                $result = DayExercise::where('id', $id)->update($postData);

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
                $dayExercise = DayExercise::where('id', $id)
                    ->where('user_id', $request->user_id)
                    ->first();

                if ($dayExercise) {
                    $dayExerciseId = $dayExercise->id;

                    $deletedDayExercise = DayExercise::where('id', $id)
                        ->where('user_id', $request->user_id)
                        ->delete();
                    Set::where('day_exercise_id', $dayExerciseId)
                        ->where('user_id', $request->user_id)
                        ->delete();

                    if ($deletedDayExercise) {
                        return successResponse(null, 'Successfully deleted.');
                    } else {
                        return errorResponse('Something went wrong.');
                    }
                } else {
                    return errorResponse('Day exercise not found.');
                }
            }

            if($request->request_type == 'admin'){
                $result = DayExercise::where('id', $id)->delete();

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
            'day_id' => 'required | numeric',
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
                $postData['day_exercise_id'] = $id;

                $result = DayExerciseComplete::create($postData);

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
