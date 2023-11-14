<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Models\Conversation;
use App\Models\UserInput;
use App\Models\ConversationInfo;
use App\Models\Company;

class DocsbotController extends Controller
{
    public function index(Request $request)
    {
        // Get POST parameter
        $message = $request->message;
        $isSystem = $request->isSystem;
        $conversationToken = $request->conversationToken;

        // Get Company info
        $company = Auth::user()->company;

        // Get DocsBot API URL
        $apiUrl = "https://api.docsbot.ai/teams/" . $company->docsbot_team_id . "/bots/" . $company->docsbot_bot_id . "/chat";

        // Check conversationId
        if (!$conversationToken) {
            // Create conversationId
            $conversationToken = (string)Str::uuid();
        }

        // Get prompt
        $prompt = $this->getPrompt($conversationToken);

        // Call DocsBot API
        list($status, $send_message, $response, $sources, $history) = $this->callDocsBot($apiUrl, $message, $prompt);
        if ($status !== "OK") {
            return response()->json([
                'status' => $status,
                'response' => $response,
                'conversationToken' => $conversationToken
            ]);
        }

        // Save conversation info
        $conversationInfo = ConversationInfo::where([
            ['user_id', Auth::user()->id],
            ['conversation_system_id', 2],  // DocsBot
            ['conversation_token', $conversationToken],
        ])->count();
        if ($conversationInfo === 0) {
            $conversationInfo = ConversationInfo::create([
                'user_id' => Auth::user()->id,
                'conversation_system_id' => 2,  // DocsBot
                'conversation_token' => $conversationToken,
                'is_visible' => true
            ]);
        }

        // ソースは別で保存する
        $source_info = "";
        foreach ($sources as $source) {
            if ($source['url']) {
                $source_info .= "\n> [" . $source['title'] . "](" . $source['url'] . ")";
            } else {
                $source_info .= "\n> " . $source['title'];
            }
        }
        $role = $isSystem ? "system" : "user";

        // Save conversation data to DB
        $conversation = Conversation::create([
            'conversation_token' => $conversationToken,
            'conversation_system_id' => 2,      // DocsBot
            'user_id' => Auth::user()->id,
            'role' => $role,
            'message' => $message,
            'prompt' => $send_message,
            'response' => $response,
            'source' => $source_info,
            'history' => json_encode($history)
        ]);

        return response()->json([
            'status' => 'success',
            'response' => $response . $source_info,
            'conversationToken' => $conversationToken
        ]);
    }

    private function getPrompt($conversationToken) {
        // Get conversation data from DB
        $conversations = Conversation::where([
            ['conversation_system_id', 2],  // DocsBot
            ['conversation_token', $conversationToken],
            ['user_id', Auth::user()->id],
        ])->orderBy('id', 'desc')->limit(15)->get();
        $conversations = $conversations->sortBy('id');

        // Make conversation hash table
        $conversationHash = [];
        if (isset($conversations[0]) && isset($conversations[0]->history)) {
            $conversationHash = json_decode($conversations[0]->history);
        } else {
            foreach ($conversations as $conversation) {
                $conversationHash[] = [
                    str_replace("\n", "", $conversation->prompt),
                    str_replace("\n", "", $conversation->response)
                ];
            }
        }

        return $conversationHash;
    }

    private function callDocsBot($apiUrl, $message, $prompt) {
        /*
        if ($prompt && is_numeric($message)) {
            $message = $message . "について教えてください。";
        } elseif (mb_strlen($message) <= 4) {
           $message = "「" . $message . "」について考えられる質問を、登録されているsourceの情報をもとに５つ考えて示してください。";
        } else {
            $nodes = $this->goku_sep($message);
            if (count($nodes) < 4) {
                $message = "「" . $message . "」について考えられる質問を、登録されているsourceの情報をもとに５つ考えて示してください。";
            } elseif (!$prompt) {
                //$message = "登録されているsourceの情報をもとに答えてください。" . $message;
            }
        }
        */

        // Calculate length of all content of prompt array
        $promptLength = mb_strlen($message);
        foreach ($prompt as $items) {
            foreach ($items as $item) {
                $promptLength += mb_strlen($item);
            }
        }

        $user_input = UserInput::create([
            'user_id' => Auth::user()->id,
            'input_length' => $promptLength,
            'response_length' => 0,
            'conversation_system_id' => 2,  // DocsBot
        ]);

        $headers = [
            'Content-Type' => 'application/json',
        ];
        $data = [
            'question' => $message,
            'full_source' => false
        ];
        if ($prompt) {
            $data['history'] = $prompt;
        }
        try {
            $response = Http::withHeaders($headers)->timeout(240)->post($apiUrl, $data);
            if ($response->successful()) {
                $user_input->response_length = mb_strlen($response->json('answer'));
                $user_input->save();
                $res = $response->json('answer');
                return ["OK", $message, $res, $response->json('sources'), $response->json('history')];
            } else {
                $user_input->save();
//                \Log::error($message);
//                \Log::error($response->json('error'));
//                \Log::error((string)$response->getBody());
                return ["error", $message, $response->json('error'), [], []];
            }
        } catch (ConnectionException $e) {
            $user_input->save();
//            \Log::error($e->getMessage());
            return ["connection_error", $message, $e->getMessage(), [], []];
        }
    }

    private function goku_sep($str)
    {
        $res = [];
        $temp_str = $str;
        while(1){
            $pos = -1;
            $chr_n = 0;
            if($n = mb_ereg("[一-龠]+", $temp_str, $match_array)){
                // 漢字
                $pos = strpos($temp_str, $match_array[0]);
                $match = $match_array[0];
                $chr_n = $n;
            }
            if($n = mb_ereg("[ぁ-ん]+", $temp_str, $match_array)){
                // かな
                $p = strpos($temp_str, $match_array[0]);
                if($pos > $p || $pos < 0){
                    $match = $match_array[0];
                    $pos = $p; $chr_n = $n;
            }
            }
            if($n = mb_ereg("[ァ-ヴー]+", $temp_str, $match_array)){
                // カタカナ
                $p = strpos($temp_str, $match_array[0]);
                if($pos > $p || $pos < 0){
                    $match = $match_array[0];
                    $pos = $p;
                    $chr_n = $n;
                }
            }
            if($n = mb_ereg("[a-zA-Z0-9.-]+", $temp_str, $match_array)){
                // 半角英数字
                $p = strpos($temp_str, $match_array[0]);
                if($pos > $p || $pos < 0){
                    $match = $match_array[0];
                    $pos = $p; $chr_n = $n;
                }
            }
            if($n = mb_ereg("[ａ-ｚＡ-Ｚ０-９]+", $temp_str, $match_array)){
                // 全角英数字
                $p = strpos($temp_str, $match_array[0]);
                if($pos > $p || $pos < 0){
                    $match = $match_array[0];
                    $pos = $p;
                    $chr_n = $n;
                }
            }
            if($chr_n == 0){
                // なし
                break;
            }
            $res[]    = $match;
            $temp_str = substr($temp_str, $pos + $chr_n);
        }

        return $res;
    }
   // Docbotの設定状態を確認する
   public function statusdoc()
   {
       $id = Auth::user()->company_id;

       //指定したidのレコードを取得する
       $company = Company::where('id', $id)->first();
       if ($company) {
            $isValid = $company->docsbot_team_id && $company->docsbot_bot_id && $company->docsbot_api_key;
       } else {
           $isValid = false;
       }

       return response()->json(['is_valid' => $isValid]);
   }
   // docsBotの設定済みかをチェック
   public function isResourceRegistered()
   {
        $company = Auth::user()->company;
        $baseUrl = 'https://docsbot.ai/api/teams/:teamId/bots/:botId/';
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $company->docsbot_api_key,
        ];
        $resources = [];

        try {
            $url = str_replace([':teamId', ':botId'], [$company->docsbot_team_id, $company->docsbot_bot_id], $baseUrl . "sources");
            $response = Http::withHeaders($headers)->timeout(20)->get($url);
            $resources = $response->json();
            if (!$resources) {
                $resources = [];
            }
        } catch (ConnectionException $e) {
//            Log::error($e->getMessage());
        }
        return response()->json($resources);

   }
}
