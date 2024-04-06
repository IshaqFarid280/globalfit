<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        try{
            $users = User::all(['id', 'name', 'created_at', 'updated_at']);

            if($users){
                return successResponse($users, 'Successfully retrieved.');
            }else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
