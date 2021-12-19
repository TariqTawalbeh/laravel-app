<?php

namespace App\Helpers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class Partners
{
    public static function subscribePartner($subscription_id, $msisdn, $user_id, $user_name, $action){
        $customClaims = [
                        'iss' => 'https://api.360vuz.com',
                        'iat' => Carbon::now()->timestamp,
                        'exp' => Carbon::now()->addDays(1)->timestamp,
                        'nbf' => Carbon::now()->timestamp,
                        'sub' => env('API_ID'),
                        'aud' => env('CLIENT_ID'),
                        'jti' => (string) Str::uuid(),
                        'subscriptionid' => $subscription_id,
                        'msisdn' => $msisdn,
                        'action' => $action,
        ];

        $payload = JWTFactory::make($customClaims);
        $token = JWTAuth::encode($payload);
        $response = Http::withToken($token)->post('http://example.com/'.$action, [
            'name' => $user_name,
            'msisdn' => $msisdn,
            'subscription_id' => $subscription_id
        ]);
        // partners api should return success msg, with response code 200 OK

        if($response->ok())
            return true;
        else
            return false;
    }

}
