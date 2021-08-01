<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    
    public function showLoginForm(Request $request)
    {
        if(Auth::check()){
            //ログイン状態だった場合強制ログアウトする
            Auth::logout();
        }
        $linkToken = $request->get('linkToken');
        
        return view("auth.login", [
            "linkToken" => $linkToken]);
    }
    
    public function login(Request $request)
    {
        $this->validateLogin($request);
        if ($this->attemptLogin($request)){
            return $this->externalLine($request);
        }
        
        $this->incrementLoginAttempts($request);
        
        return $this->sendFailedLoginResponse($request);
    }
    private function externalLine(Request $request)
    {
        $linkToken = $request->get('linkToken');
        $nonce = Hash::make(random_bytes(32));
        $email = $request->get("email");
        $user = User::query()->where("email", $email)->first();
        $user->update([
            "nonce" => $nonce,
            ]);
            
        return Redirect("https://access.line.me/dialog/bot/accountLink?linkToken={$linkToken}&nonce={$nonce}");
    }
}
