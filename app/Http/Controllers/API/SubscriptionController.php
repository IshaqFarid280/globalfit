<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function index(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required | numeric',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $subscription = Subscription::where('user_id', $request->user_id)->first();
            $carbonDate = Carbon::parse($subscription->purchase_date);
            $expiry = $carbonDate->addDays(30)->toDateString();
            $subscription['expiry_date'] = $expiry;

            if($subscription){
                return successResponse($subscription, 'Successfully retrieved.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required | numeric',
            'subscription_plan' => 'required | string',
            'purchase_date' => 'required | string',
        ]);

        if ($validator->fails()) {
            return validationResponse($validator);
        }

        try{
            $userId = $request->user_id;
            $checkSubscrip = Subscription::where('user_id', $userId)->whereNotNull('is_first')->first();

            if ($checkSubscrip) {
                $purchaseDate = Carbon::parse($checkSubscrip->purchase_date)->addDays(30);
                if ($purchaseDate->isPast()) {
                    $subscription = Subscription::updateOrCreate(
                        [
                            'user_id' => $request->user_id
                        ],
                        [
                            'subscription_plan' => $request->subscription_plan,
                            'purchase_date' => $request->purchase_date,
                        ]
                    );
                }
                else{
                    return errorResponse('You are not allowed to repurchase subscription before expiry.');
                }
            }
            else{
                $subscription = Subscription::updateOrCreate(
                    [
                        'user_id' => $request->user_id
                    ],
                    [
                        'subscription_plan' => $request->subscription_plan,
                        'purchase_date' => $request->purchase_date,
                    ]
                );
            }

            if($subscription){
                return successResponse(null, 'Successfully purchased subscription.');
            }
            else{
                return errorResponse('Something went wrong.');
            }
        } catch(\Exception $ex){
            return errorResponse($ex->getMessage());
        }
    }
}
