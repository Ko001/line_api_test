<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
     /**
     * @param Request $request
     * @throws \LINE\LINEBot\Exception\CurlExecutionException
     */
    public function webhook(Request $request){
        $events = $request->get("events", []);
        $userId = array_get($events, "0.source.userId");
        $text = array_get($events, "0.message.text");
        $replayToken = array_get($events, "0.replyToken");
        // $user = User::query()->where("uuid",$userId)->first();
        $user = User::where("uuid",$userId)->first();
        
        //メッセージを送ったLineユーザーが連携していない場合
        if(is_null($user) || is_null($user->nonce)){
            $this->accountLink($userId, $replayToken);
            return;
        }
    }
    
    /**
     * @param string $userId
     * @param string $replayToken
     * @throws \LINE\LINEBot\Exception\CurlExeception
     * /
    */
    
    private function accountLink(string $userId, string $replayToken){
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(config("auth.line-api-key"));
        $response = $httpClient->post("https://api.line.me/v2/bot/user/{$userId}/linkToken",[]);
        
        $rowBody = $response->getRawBody();
        $responseObject = json_decode($rowBody);
        $linkToken = object_get($responseObject, "linkToken");
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => config("auth.channel-secret")]);
        $templateMessage = new TemplateMessageBuilder("account link", new ButtonTemplateBuilder("アカウント連携します。", "アカウント連携します。", null, [
            new UriTemplateActionBuilder("OK", route("api.login",["linkToken" => $linkToken]))
        ]));
        
        $response = $bot->replyMessage($replayToken, $templateMessage);
        $lineResponse = $response->getHTTPStatus() . ' ' . $response->getRawBody();
        \Log::info($lineResponse);
    }
    
}
