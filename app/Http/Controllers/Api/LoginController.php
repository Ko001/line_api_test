<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;  
    
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm(Request $request){
        if(\Auth::check()){
            //ログイン状態だった場合強制ログアウトする
            \Auth::logout();
        }
        $linkToken = $request->get("linkToken");
        dd($linkToken);

        return view("auth.login", [
            "linkToken" => $linkToken
        ]);
    }
    
    
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            return $this->externalLine($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    private function externalLine(Request $request){

        $linkToken = $request->get("linkToken");
        $nonce = \Hash::make(random_bytes(32));
        $email = $request->get("email");
        $user = User::query()->where("email", $email)->first();
        $user->update([
            "nonce" => $nonce,
        ]);

        return Redirect("https://access.line.me/dialog/bot/accountLink?linkToken={$linkToken}&nonce={$nonce}");

    }
}
