<?php

namespace App\Http\Controllers\Lyric;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OAuth2Controller extends Controller
{
    
    public function authenticate(Request $request) {
        $provider = app('Lyric\OAuth2\Provider');
        
        $code = $request->get('code');
        
        if(empty($code)) {
            $authorizationUrl = $provider->getAuthorizationUrl();
            \Session::put('lyric.oauth2', $provider->getState());
            return redirect($authorizationUrl);
        } else {
            
            $state = $request->get('state');
            $storedState = \Session::get('lyric.oauth2');
            
            if(empty($state) || ($state != $storedState)) {
                \Session::remove('lyric.oauth2');
                throw new \Exception("Invalid State");
            }
            
            $accessToken = $provider->getAccessToken('authorization_code', compact('code'));
             
            \App\Models\KeyStoreData::set('lyric_access_token', json_encode($accessToken->jsonSerialize()));
            
            print "Authenticated with Lyric";
            return;
        }
    }
}