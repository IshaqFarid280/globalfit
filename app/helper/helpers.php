<?php

use Illuminate\Support\Facades\Storage;

if( !function_exists( 'successResponse' ) ) {
    function successResponse($data, $message){
        return response()->json([
            'message' => [
                'success' => $message
            ],
            'data' => $data,
        ], 200);
    }
}

if( !function_exists( 'errorResponse' ) ) {
    function errorResponse($message){
        return response()->json([
            'message' => [
                'error' => $message
            ],
            'data' => null,
        ], 500);
    }
}

if( !function_exists( 'validationResponse' ) ) {
    function validationResponse($validator)
    {
        $errors = $validator->errors();
        $messages = $errors->getMessages();
        $valdidationMessages = array();

        foreach ($messages as $key=>$val){
            $valdidationMessages[$key] = $val[0];
            return response()->json([
                'message' => $valdidationMessages,
                'data' => null,
            ], 400);
        }
    }
}

if( !function_exists( 'storeImage' ) ) {
    function storeImage($request, $key){
        $image = $request->file($key);
        $fileName = $image->hashName();
        $image->move(public_path('files/'), $fileName);

        return $fileName;
    }
}
