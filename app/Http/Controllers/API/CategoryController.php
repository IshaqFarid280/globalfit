<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::orderBy('id', 'DESC')->get();
        return successResponse($categories, 'Successfully retrieved.');
    }

    public function store(Request $request){
        $validator = Validator::make($request->post(), [
            'name' => 'required | string | regex:/(^([a-zA-Z]+))/u'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $result = Category::create([
                'name' => $request->name
            ]);

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
        try {
            $result = Category::where('id', $id)->first();

            if($result){
                return successResponse($result, 'Successfully retrieved.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->post(), [
            'name' => 'required | string | regex:/(^([a-zA-Z]+))/u'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $result = Category::where('id', $id)->update([
                'name' => $request->name
            ]);

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
            $result = Category::where('id', $id)->delete();

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

    public function categoryExercises($id){
        try{
            $category = Category::with('exercises')->where('id', $id)->first();
            foreach ($category->exercises as $exercise){
                $exercise['exercise_gif'] = asset('files'). "/" . $exercise->exercise_gif;
            }

            if($category){
                return successResponse($category, 'Successfully retrieved.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
