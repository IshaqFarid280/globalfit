<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExerciseController extends Controller
{
    public function index(){
        try{
            $exercises = Exercise::with('category')->orderBy('id', 'DESC')->get();

            foreach ($exercises as $exercise){
                $exercise['exercise_gif'] = asset('files'). "/" . $exercise->exercise_gif;
            }

            if($exercises){
                return successResponse($exercises, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required | numeric',
            'exercise_name' => 'required | string',
            'exercise_description' => 'required | string',
            'exercise_gif' => 'required',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $postData = $request->except('exercise_gif');

            if($request->hasFile('exercise_gif')){
                $fileName = storeImage($request, 'exercise_gif');
                $postData['exercise_gif'] = $fileName;
            }

            $result = Exercise::create($postData);
            $result['exercise_gif'] = asset('files'). "/" . $result->exercise_gif;

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
            $exercise = Exercise::with('category')->where('id', $id)->first();

            if($exercise){
                $exercise['exercise_gif'] = asset('files'). "/" . $exercise->exercise_gif;
                return successResponse($exercise, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required | numeric',
            'exercise_name' => 'required | string',
            'exercise_description' => 'required | string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $postData = $request->except('exercise_gif');

            if($request->hasFile('exercise_gif')){
                $fileName = storeImage($request, 'exercise_gif');
                $postData['exercise_gif'] = $fileName;
            }

            $result = Exercise::where('id', $id)->update($postData);

            if($result == '1'){
                return successResponse(null, 'Successfully updated.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function destroy($id){
        try {
            $result = Exercise::where('id', $id)->delete();

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
}
