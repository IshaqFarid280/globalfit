<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RoutinesController extends Controller
{
    public function index(){
        try{
            $users = User::select(['id', 'name'])->orderBy('id', 'DESC')->get();

            if($users){
                return successResponse($users, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function routineDetails($id){
        try{
            $user = User::with('userDetails')->first();
            $user['userDetails']['focus'] = json_decode($user->userDetails->focus);

            if($user){
                return successResponse($user, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
