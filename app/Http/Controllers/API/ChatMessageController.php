<?php

namespace App\Http\Controllers\API;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        $userId = $request->user_id ?? auth()->id();

        $message = ChatMessage::where('user_id', $userId)->first();

        if (!$message) {
            return errorResponse('No message found.');
        }

        $messages = ChatMessage::with('user')->where('room_id', $message->room_id)->get();

        foreach ($messages as $val){
            $val['user_name'] = ucwords($val->user->name);
            unset($val->user);
        }

        return successResponse($messages, 'Successfully retrieved.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $roomId = $request->room_id;

            DB::transaction(function () use (&$roomId, $request) {
                $room = Room::firstOrCreate(['name' => 'room'.$roomId], ['name' => 'room'.$roomId]);

                $roomId = $room->id;

                $message = new ChatMessage();
                $message->user_id = $request->user_id;
                $message->room_id = $roomId;
                $message->message = $request->message;
                $message->save();

                broadcast(new ChatMessageSent($message))->toOthers();
            });

            return successResponse(null, 'Message sent successfully.');
        } catch (\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = ChatMessage::with('user')->findOrFail($id);
        $message['user_name'] = ucwords($message->user->name);
        unset($message->user);

        if($message){
            return successResponse($message, 'Successfully retrieved.');
        } else{
            return errorResponse('Something went wrong.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $message = ChatMessage::findOrFail($id);
            $message->message = $request->message;
            $message->save();

            if($message){
                return successResponse(null, 'Message updated successfully.');
            } else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = ChatMessage::findOrFail($id);
        $message->delete();

        if($message){
            return successResponse(null, 'Message deleted successfully.');
        } else{
            return errorResponse('Something went wrong.');
        }
    }
}
