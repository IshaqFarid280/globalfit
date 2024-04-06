<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EditSet;
use App\Models\Set;
use App\Models\SetComplete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SetController extends Controller
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
                $sets = Set::where('day_exercise_id', $id)->where('user_id', null)->get();

                if($sets){
                    return successResponse($sets, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if ($request->has('request_type') && $request->request_type == 'admin' && $request->has('person_id')) {
                $userId = $request->person_id;

                $sets = Set::where('day_exercise_id', $id)
                    ->whereNull('user_id')
                    ->with(['setCompleted' => function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    }])
                    ->get();

                $userSets = Set::where('day_exercise_id', $id)
                    ->where('user_id', $userId)
                    ->get();

                $allusersets = $sets->merge($userSets);

                $setIds = $allusersets->pluck('id')->toArray();
                $setEdit = EditSet::where('day_exercise_id', $id)
                    ->where('user_id', $userId)
                    ->whereIn('set_id', $setIds)
                    ->get();

                foreach ($setEdit as $v){
                    $v['set_id'] = strval($v['set_id']);
                    $v['user_id'] = strval($v['user_id']);
                    $v['kg'] = strval($v['kg']);
                    $v['reps'] = strval($v['reps']);
                    $v['day_exercise_id'] = strval($v['day_exercise_id']);
                }

                $exerciseSets = $allusersets->merge($setEdit);
                $uniqueSetIds = $exerciseSets->pluck('set_id')->unique();

                foreach ($exerciseSets as $val){
                    $isCompleted = SetComplete::where('set_id', $val->id)
                        ->orWhere('set_id', $val->set_id)
                        ->where('day_exercise_id', $id)->first();
                    $val['is_completed'] = $isCompleted ? $isCompleted->is_completed : false;
                    unset($val['setCompleted']);
                }

                $exerciseSets = $exerciseSets->reject(function ($set) use ($uniqueSetIds) {
                    return $uniqueSetIds->contains($set->id);
                })->values();

                $editSetIds = EditSet::whereIn('set_id', $setIds)->pluck('set_id')->toArray();

                foreach ($exerciseSets as $set) {
                    if (property_exists($set, 'id')) {
                        $set['day_exercise_id'] = strval($set['day_exercise_id']);
                        $set['is_edit'] = in_array($set->id, $editSetIds) ? 'true' : 'false';

                    } else {
                        $set['day_exercise_id'] = strval($set['day_exercise_id']);
                        $set['is_edit'] = in_array($set->set_id, $editSetIds) ? 'true' : 'false';
                    }
                }

                if($exerciseSets){
                    return successResponse($exerciseSets, 'Successfully retrieved.');
                }else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->has('request_type') && $request->request_type == 'user'){

                $sets = Set::where('day_exercise_id', $id)
                    ->where('user_id', null)
                    ->get();

                $usersets = Set::where('day_exercise_id', $id)
                    ->where('user_id', $request->user_id)
                    ->get();

                $allusersets = $sets->merge($usersets);

                $setIds = $allusersets->pluck('id')->toArray();

                $editedSet = EditSet::where('day_exercise_id', $id)
                    ->where('user_id', $request->user_id)
                    ->whereIn('set_id', $setIds)
                    ->get();

                foreach ($editedSet as $value){
                    $value['set_id'] = strval($value['set_id']);
                    $value['user_id'] = strval($value['user_id']);
                    $value['kg'] = strval($value['kg']);
                    $value['reps'] = strval($value['reps']);
                    $value['day_exercise_id'] = strval($value['day_exercise_id']);
                }

                $exerciseSets = $allusersets->merge($editedSet);

                $uniqueSetIds = $exerciseSets->pluck('set_id')->unique();

                $exerciseSets = $exerciseSets->reject(function ($set) use ($uniqueSetIds) {
                    return $uniqueSetIds->contains($set->id);
                })->values();

                $editSetIds = EditSet::whereIn('set_id', $setIds)->pluck('set_id')->toArray();

                foreach ($exerciseSets as $set) {
                    if (property_exists($set, 'id')) {
                        $set['day_exercise_id'] = strval($set['day_exercise_id']);
                        $set['is_edit'] = in_array($set->id, $editSetIds) ? 'true' : 'false';
                    } else {
                        $set['day_exercise_id'] = strval($set['day_exercise_id']);
                        $set['is_edit'] = in_array($set->set_id, $editSetIds) ? 'true' : 'false';
                    }
                }

                if($exerciseSets){
                    return successResponse($exerciseSets, 'Successfully retrieved.');
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
            'kg' => 'required',
            'reps' => 'required',
            'day_exercise_id' => 'required | numeric',
            'request_type' => 'required | string'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            if($request->user_id == 'null'){
                foreach ($request->kg as $key=>$kg){
                    $result = Set::create([
                        'kg' => $kg,
                        'reps' => $request->reps[$key],
                        'day_exercise_id' => $request->day_exercise_id,
                    ]);
                }
            }else{
                foreach ($request->kg as $key=>$kg){
                    $result = Set::create([
                        'kg' => $kg,
                        'reps' => $request->reps[$key],
                        'day_exercise_id' => $request->day_exercise_id,
                        'user_id' => $request->user_id
                    ]);
                }
            }

            if($result){
                return successResponse(null, 'Successfully created.');
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
            $set = Set::where('id', $id)->first();

            if($set){
                return successResponse($set, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'kg' => 'required',
            'reps' => 'required'
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try {
            if($request->request_type == 'user' && Set::where('id', $id)->where('user_id', $request->user_id)->exists()){
                foreach ($request->kg as $key=>$kg){
                    $result = Set::where('id', $id)->update([
                        'kg' => $kg,
                        'reps' => $request->reps[$key],
                    ]);
                }

                if($result == '1'){
                    return successResponse(null, 'Successfully updated.');
                }
                else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->request_type == 'edit_set'){
                foreach ($request->kg as $key=>$kg){
                    $result = EditSet::updateOrCreate(
                        [
                            'day_exercise_id' => $request->day_exercise_id,
                            'user_id' => $request->user_id,
                            'set_id' => $id
                        ],
                        [
                            'kg' => $kg,
                            'reps' => $request->reps[$key],
                        ]);
                }

                if($result){
                    return successResponse(null, 'Successfully added.');
                }
                else{
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->request_type == 'admin'){
                foreach ($request->kg as $key=>$kg){
                    $result = Set::where('id', $id)->update([
                        'kg' => $kg,
                        'reps' => $request->reps[$key],
                    ]);
                }

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
                $deletedSet = Set::where('id', $id)
                    ->where('user_id', $request->user_id)
                    ->delete();

                if ($deletedSet) {
                    return successResponse(null, 'Successfully deleted.');
                } else {
                    return errorResponse('Something went wrong.');
                }
            }

            if($request->request_type == 'admin'){
                $result = Set::where('id', $id)->delete();

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
            'day_exercise_id' => 'required | numeric',
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
                $postData['set_id'] = $id;

                $result = SetComplete::create($postData);

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
