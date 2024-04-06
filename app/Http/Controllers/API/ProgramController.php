<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssignProgram;
use App\Models\Day;
use App\Models\DayExercise;
use App\Models\Program;
use App\Models\ProgramComplete;
use App\Models\Set;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    public function index(Request $request){
        try{
            if($request->has('request_type') && $request->request_type == 'admin' && !$request->has('person_id')){
                $programs = Program::with('programCompleted')
                    ->where('user_id', null)
                    ->get();

                if($programs){
                    return successResponse($programs, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->has('request_type') && $request->request_type == 'admin' && $request->has('person_id')){
                $userId = $request->person_id;

                $programsData = AssignProgram::leftJoin('program_completes', function ($join) use ($userId) {
                    $join->on('assign_programs.program_id', '=', 'program_completes.program_id')
                        ->where('program_completes.user_id', $userId);
                })
                    ->join('programs', 'assign_programs.program_id', '=', 'programs.id')
                    ->where('assign_programs.user_id', $userId)
                    ->select('programs.id', 'programs.program_name', \DB::raw('(COALESCE(program_completes.is_completed, false)) as is_completed'), 'assign_programs.user_id')
                    ->get();

                foreach ($programsData as $val){
                    if($val->is_completed == "0"){
                        $val['is_completed'] = false;
                    }
                }

                if($programsData){
                    return successResponse($programsData, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->has('request_type') && $request->request_type == 'user'){
                $userId = $request->user_id;

                $assignPrograms = AssignProgram::leftJoin('programs', 'assign_programs.program_id', '=', 'programs.id')
                    ->where('assign_programs.user_id', $userId)
                    ->select('programs.*')
                    ->get();

                $programs = Program::where('user_id', $userId)->get();

                $result = $assignPrograms->merge($programs);

                $programs = $result->values();

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
            'program_name' => 'required | string | unique:programs',
            'request_type' => 'required | string',
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

            $result = Program::create($postData);
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
            $program = Program::where('id', $id)->first();

            if($program){
                return successResponse($program, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'program_name' => 'required | string',
            'request_type' => 'required'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            if($request->request_type == 'user' && Program::where('id', $id)->where('user_id', $request->user_id)->exists()){
                $result = Program::where('id', $id)->update(['program_name' => $request->program_name]);
                if($result == '1'){
                    return successResponse(null, 'Successfully updated.');
                }
                else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->request_type == 'admin'){
                $result = Program::where('id', $id)->update(['program_name' => $request->program_name]);
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
                $program = Program::where('id', $id)
                    ->where('user_id', $request->user_id)
                    ->first();

                if ($program) {
                    $programId = $program->id;

                    $day = Day::where('program_id', $programId)
                        ->where('user_id', $request->user_id)
                        ->first();

                    if ($day) {
                        $dayId = $day->id;

                        $dayExercise = DayExercise::where('day_id', $dayId)
                            ->where('user_id', $request->user_id)
                            ->first();

                        if ($dayExercise) {
                            $dayExerciseId = $dayExercise->id;

                            $deletedProgram = Program::where('id', $programId)->delete();
                            Day::where('program_id', $programId)
                                ->where('user_id', $request->user_id)
                                ->delete();
                            DayExercise::where('day_id', $dayId)
                                ->where('user_id', $request->user_id)
                                ->delete();
                            Set::where('day_exercise_id', $dayExerciseId)
                                ->where('user_id', $request->user_id)
                                ->delete();

                            if ($deletedProgram) {
                                return successResponse(null, 'Successfully deleted.');
                            } else {
                                return errorResponse('Something went wrong.');
                            }
                        } else {
                            return errorResponse('Day exercise not found.');
                        }
                    } else {
                        return errorResponse('Day not found.');
                    }
                } else {
                    return errorResponse('Program not found.');
                }
            }

            if($request->request_type == 'admin'){
                $result = Program::where('id', $id)->delete();

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
            'user_id' => 'required | numeric',
            'is_completed' => 'required | string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            if($request->request_type == 'user'){
                $postData = $request->except('request_type');
                $postData['program_id'] = $id;

                $result = ProgramComplete::create($postData);

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


    public function programAssignUsers(Request $request){
        $validator = Validator::make($request->all(), [
            'request_type' => 'required | string'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $usersWithAssignedPrograms = User::selectRaw('users.*, true AS is_assigned')
                ->leftJoin('assign_programs', function ($join) {
                    $join->on('users.id', '=', 'assign_programs.user_id');
                })
                ->whereNotNull('assign_programs.id')
                ->distinct()
                ->get();

            if($usersWithAssignedPrograms){
                foreach ($usersWithAssignedPrograms as $user){
                    $user['is_assigned'] = ($user['is_assigned'] == '1') ? 'true' : 'false';
                }

                return successResponse($usersWithAssignedPrograms, 'Successfully retrieved.');
            }else{
                return errorResponse('No record found.');
            }
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function programUnassignUsers(Request $request){
        $validator = Validator::make($request->all(), [
            'request_type' => 'required | string'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            $users = User::selectRaw('users.*, false AS is_assigned')
                ->leftJoin('assign_programs', function ($join) {
                    $join->on('users.id', '=', 'assign_programs.user_id');
                })
                ->whereNull('assign_programs.id')
                ->get();

            if($users){
                foreach ($users as $user){
                    $user['is_assigned'] = ($user['is_assigned'] == '1') ? 'true' : 'false';
                }

                return successResponse($users, 'Successfully retrieved.');
            }else{
                return errorResponse('No record found.');
            }
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
