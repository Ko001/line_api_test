<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


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
        
        
        if (array_get($events, "0.link.result" == "ok")){
            $this->saveAccount($events);
            
            $httpClient = new LINE\LINEBot\HTTPClient\CurlHTTPClient(config("auth.line-api-key"));
            $bot = new LINE\LINEBot($httpClient, ['channelSecret' => config("auth.channel-secret")]);
            
            $textMessageBuilder = new LINE\LINEBot\MessageBuilder\TextMessageBuilder("アカウントの連携に成功しました");
            $response = $bot->replyMessage($replayToken, $textMessageBuilder);
            
            $lineResponse = $response->getHTTPStatus() . ' ' . $response->getRawBody();
            Log::info($lineResponse)
            return;
        }
        
        if ($text === "link account")
        {
            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(config("auth.line-api-key"));
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => config("auth.channel-secret")]);

            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('右');
            $response = $bot->replyMessage($replayToken, $textMessageBuilder);

            $lineResponse = $response->getHTTPStatus() . ' ' . $response->getRawBody();
            \Log::info($lineResponse);
        }
        if($text === "disconnect account"){
            $this->unlinkAccount($userId,$replayToken);
        }
        }
        
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
        Log::info($lineResponse);
    }
    
    private function unlinkAccount(string $uuid, string $replayToken)
    {
        $user = User::quer()->where("uuid", $uuid)->first();
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(config("auth.line-api-key"));
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => config("auth.channel-secret")]);
        $replayMessage = "";
        if(is_null($user)){
            $replayMessage = "アカウントの連携はされていません。";
        }
        $user->uuid = null;
        $user->nonce = null;

        if($user->save()){
            $replayMessage = "アカウントの連携を解除しました。";
        }

        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($replayMessage);
        $response = $bot->replyMessage($replayToken, $textMessageBuilder);

        $lineResponse = $response->getHTTPStatus() . ' ' . $response->getRawBody();
        \Log::info($lineResponse);
        
    }
    
    public function saveAccount(array $events)
    {
        $uuid = array_get($events, "0.source.userId");
        $nonce = array_get($events, "0.link.nonce");
        $user = User::query()->where("nonce", $nonce)->first();
        if (is_null($user)){
            return;
        }
        $user->update([
            "uuid" => $uuid]);
    }
    
    
    
}
