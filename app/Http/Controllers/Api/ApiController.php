<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\User;


class ApiController extends Controller
{
     /**
     * @param Request $request
     * @throws \LINE\LINEBot\Exception\CurlExecutionException
     */
    public function webhook(Request $request){
        \Log::info(print_r($request->all(), true));
        $events = $request->get("events", []);
        $userId = \Arr::get($events, "0.source.userId");
        $text = \Arr::get($events, "0.message.text");
        $replayToken = \Arr::get($events, "0.replyToken");
        if(\Arr::get($events, "0.link.result") === "ok"){
            $this->saveAccount($events);

            $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(config("auth.line-api-key"));
            $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => config("auth.channel-secret")]);

            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("アカウントの連携に成功しました。");
            $response = $bot->replyMessage($replayToken, $textMessageBuilder);

            $lineResponse = $response->getHTTPStatus() . ' ' . $response->getRawBody();
            \Log::info($lineResponse);
            return;
        }
        //メッセージを送ったユーザーがアカウント連携をしていない場合
        $user = User::query()->where("uuid",$userId)->first();
        if(is_null($user) || is_null($user->nonce)){
            $this->accountLink($userId, $replayToken);
            return;
        }
        if($text === "link account"){

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
    
    /**
     * アカウント連携解除
     * @param string $uuid
     * @param string $replayToken
     */
    private function unlinkAccount(string $uuid, string $replayToken){
        /** @var User $user */
        $user = User::query()->where("uuid",$uuid)->first();

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
    
    /**
     * アカウント保存
     * @param array $events
     */
    private function saveAccount(array $events){
        $uuid = \Arr::get($events, "0.source.userId");
        $nonce = \Arr::get($events, "0.link.nonce");
        $user = User::query()->where("nonce", $nonce)->first();
        if(is_null($user)){
            return;
        }
        $user->update([
            "uuid" => $uuid
        ]);
    }
    
    
    
}
