<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssignProgram;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignProgramController extends Controller
{
    public function index(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required | numeric',
            'request_type' => 'required',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $user_id = $request->user_id;

            if($request->has('request_type') && $request->request_type == 'admin'){
                $programs = Program::whereHas('assignPrograms', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })->get();

                if($programs){
                    return successResponse($programs, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if ($request->has('request_type') && $request->request_type == 'user') {
                $programs = Program::whereHas('assignPrograms', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                })->orWhere('user_id', $user_id)->get();

                if($programs){
                    return successResponse($programs, 'Successfully retrieved.');
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
            'user_id' => 'required | numeric',
            'program_id' => 'required | numeric',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $result = AssignProgram::create($request->post());
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

    public function userAssignedPrograms(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable',
            'person_id' => 'required | numeric',
            'request_type' => 'required | string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $programs = AssignProgram::with('program')->where('user_id', $request->person_id)->get();
            foreach ($programs as $program){
                $program['program_name'] = $program->program->program_name;

                unset($program->program);
            }

            if($programs){
                return successResponse($programs, 'Successfully retrieved.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function delete(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'request_type' => 'required',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $result = AssignProgram::where('id', $id)->delete();
            if($result == '1'){
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
