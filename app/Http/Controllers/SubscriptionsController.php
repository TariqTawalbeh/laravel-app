<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SubscriptionsRequest;
use App\Http\Resources\SubscriptionsResource;
use App\Models\Subscription;
use App\Models\SubscriptionsLog;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Helpers\Partners;
use Carbon\Carbon;

class SubscriptionsController extends Controller
{
    public function subscribe(SubscriptionsRequest $request){
        /*
         * subscribe only happens on the inactive users, or users who are not subscribed yet, so, if any user want to susbcribe,
         * we check if the user is existed and if his status is inactive or not, if exist and inactive, then, the subsc.
         * request is made to the partners subsc. api and the transaction is logged and his status is updated to pending,
         * if not exist, then he will be added to our DB and log and will call the subsc. partner api as well!
         * */
        $user_id = Auth::user()->id;
        $user_name = Auth::user()->name;

        $subscriptions = Subscription::where('user_id', $user_id)->first();
        $data_exist_flag = false;
        if(!is_null($subscriptions)){
            $data_exist_flag = true;
            switch($subscriptions->status) {
                case('Pending'):
                    $msg = 'you have a pending status subscription, your data is: ';
                    return response()->json([$msg, new SubscriptionsResource($subscriptions)], Response::HTTP_BAD_REQUEST);
                    break;
                case('Active'):
                    $msg = 'you have an active account, your data is: ';
                    return response()->json([$msg, new SubscriptionsResource($subscriptions)], Response::HTTP_BAD_REQUEST);
                    break;
                default:
                    $subscriptions->update([
                        'status' => 'Pending'
                    ]);
                    $msg = 'your existed transaction is Updated: ';
                    $code = '200';
            }
        } else {
            $subscriptions = Subscription::create([
                'user_id' => $user_id,
                'msisdn_number' => $request->msisdn_number,
                'status' => 'Pending'
            ]);
            $msg = 'Created: ';
            $code = '201';
        }
        $subscriptions_logs = SubscriptionsLog::create([
            'user_id' => $subscriptions->user_id,
            'msisdn_number' => $subscriptions->msisdn_number,
            'status' => 'Pending',
            'transaction_date' => $subscriptions->updated_at
        ]);

        $subscription_status = Partners::subscribePartner($subscriptions->id, $subscriptions->msisdn_number, $user_id, $user_name, 'subscribe');
        if($subscription_status){
            return response()->json([$msg, new SubscriptionsResource($subscriptions)], $code);
        } else {
            return response()->json(['something went wrong'], 400);
        }
    }

    public function unSubscribe(Request $request){
        /* unsubscription happens only on active users, it just update the status to pending and call the unsubsc. partner api.
         * the user is determined by the access token provided to the api, so no additional parameters are needed
         * */

        $user_id = Auth::user()->id;
        $user_name = Auth::user()->name;

        $subscriptions = Subscription::where('user_id', $user_id)->first();
        if(is_null($subscriptions)){
            return response()->json(['you are not subscribed!'], Response::HTTP_BAD_REQUEST);
        }
        if($subscriptions->status != 'Active')
            return response()->json(['you are not subscribed!'], Response::HTTP_BAD_REQUEST);

        $subscriptions->update([
            'status' => 'Pending'
        ]);

        $subscriptions_logs = SubscriptionsLog::create([
            'user_id' => $subscriptions->user_id,
            'msisdn_number' => $subscriptions->msisdn_number,
            'status' => 'Pending',
            'subscription_number' => $subscriptions->subscription_number,
            'transaction_date' => $subscriptions->updated_at
        ]);
        // use same function of subscription and unsubscription
        $subscription_status = Partners::subscribePartner($subscriptions->id, $subscriptions->msisdn_number, $user_id, $user_name, 'unsubscribe');
        if($subscription_status){
            return response()->json(['unsubscribed successfully'], 200);
        } else {
            return response()->json(['something went wrong'], 400);
        }
    }

    public function subscriptionCallback(Request $request){
        /*
         * partner call should look like
         * {"subscription_id":"123", "transaction_status":"1/0", "subscription_number":"123456 or 0"}
         * if transaction status is true then the operation is done whether it's subscription or unsubscription,
         * I can know the type of action by checking the subscriptions table, if the subscription number is there, then it's
         * unsub. action, since the subscription action happens on unsubscribed users who don't have subscription number,
         * subsc. number returned from the partner can be 0 if the trans. status is 0
         * */

        $fields = $request->validate([
            'subscription_id' => 'required',
            'transaction_status' => 'required',
            'subscription_number' => 'required'
        ]);

        $subscription_id = $request->subscription_id;
        $subscriptions = Subscription::where('id', $subscription_id)->first();

        if($request->transaction_status == true && $subscriptions->status == 'Pending'){
            if(!is_null($subscriptions->subscription_number)){
                $subsc_number = '';
                $status = 'InActive';
            } else {
                $subsc_number = $request->subscription_number;
                $status = 'Active';
            }


            $subscriptions->update([
                'status' => $status,
                'subscription_number' => $subsc_number
            ]);
        } else {
            return response()->json(['something went wrong!'], 400);
        }
    // logging logging
        $subscriptions_logs = SubscriptionsLog::create([
            'user_id' => $subscriptions->user_id,
            'msisdn_number' => $subscriptions->msisdn_number,
            'status' => $status,
            'subscription_number' =>  $subsc_number,
            'transaction_date' => Carbon::now()
        ]);

        return response()->json(['success'], 200);
    }
}
