<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->query('request_type') == 'user' && $request->query('user_id') !== null){
            $userId = $request->query('user_id');
            $checkSubscrip = Subscription::where('user_id', $userId)->first();

            if ($checkSubscrip) {
                $purchaseDate = Carbon::parse($checkSubscrip->purchase_date)->addDays(30);

                if ($purchaseDate < Carbon::now()) {
                    $checkSubscrip['is_first'] = ($checkSubscrip->is_first == '0') ? true : false;

                    return successResponse($checkSubscrip, 'Subscription has expired.');
                }
            }
            else {
                return errorResponse('Subscription not found.');
            }
        }
        return $next($request);
    }
}
