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
use App\Models\GPTFunction;
use App\Models\ProhibitedWord;
use App\Models\ConversationInfo;

class GptController extends Controller
{
    public function index(Request $request)
    {
        // Get POST parameter
        $message = $request->message;
        $isSystem = $request->isSystem;
        $conversationToken = $request->conversationToken;
        $functionId = $request->input('functionId', 0);
        $model = 'gpt-4-1106-preview';
        if (mb_strtolower($request->model) === 'gpt-3.5-turbo') {
            $model = 'gpt-3.5-turbo-1106';
        } else if (mb_strtolower($request->model) === 'gpt-4') {
            $model = 'gpt-4';
        }

        // Get ChatGPT API's key from env
        $apiKey = env('CHATGPT_API_KEY');

        // Check conversationId
        if (!$conversationToken) {
            // Create conversationId
            $conversationToken = (string)Str::uuid();
        }
        #\Log::info('index method called');

        // Check conversationId
        if (!$conversationToken) {
            // Create conversationId
            $conversationToken = (string)Str::uuid();
            #\Log::info('Generated new conversation token: ' . $conversationToken);
        } else {
            #\Log::info('Received existing conversation token: ' . $conversationToken);
        }

        // Conversation info
        $conversationInfo = ConversationInfo::where([
            ['user_id', Auth::user()->id],
            ['conversation_system_id', 1],  // ChatGPT
            ['conversation_token', $conversationToken],
        ])->get();
        if (!$conversationInfo->isEmpty()) {
            $model = $conversationInfo->first()->model;
        }

        // Get prompt
        $prompt = $this->getPrompt($message, $conversationToken, $isSystem);
        // Call ChatGPT API
        list($status, $response, $function, $used_model) = $this->callChatGPT($apiKey, $prompt, $functionId, $model);
        if ($status !== "OK") {
            return response()->json([
                'status' => $status,
                'response' => $response,
                'conversationToken' => $conversationToken
            ]);
        }

        // Save conversation info
        if ($conversationInfo->isEmpty()) {
            $conversationInfo = ConversationInfo::create([
                'user_id' => Auth::user()->id,
                'conversation_system_id' => 1,  // ChatGPT
                'conversation_token' => $conversationToken,
                'is_visible' => true,
                'function_id' => $functionId,
                'model' => $model
            ]);
        }

        // Save conversation data to DB
        $conversation = Conversation::create([
            'conversation_token' => $conversationToken,
            'conversation_system_id' => 1,      // ChatGPT
            'user_id' => Auth::user()->id,
            'role' => end($prompt)['role'],
            'message' => $message,
            'prompt' => end($prompt)['content'],
            'response' => $response,
            'function_id' => $functionId,
            'model' => $used_model
        ]);

        if ($function) {
            $function_conversation = Conversation::create([
                'conversation_token' => $conversationToken,
                'conversation_system_id' => 1,      // ChatGPT
                'user_id' => Auth::user()->id,
                'role' => 'function',
                'message' => '',
                'prompt' => $function['prompt'],
                'response' => $function['response'],
                'function_id' => $functionId,
                'model' => $used_model
            ]);
            $response = $function['response'];
        }

        return response()->json([
            'status' => 'success',
            'response' => $response,
            'conversationToken' => $conversationToken
        ]);
    }

    private function getPrompt($message, $conversationToken, $isSystem = false) {
        // Get conversation data from DB
        $conversations = Conversation::where([
            ['conversation_system_id', 1],  // ChatGPT
            ['conversation_token', $conversationToken],
            ['user_id', Auth::user()->id],
        ])->orderBy('id', 'desc')->limit(15)->get();
        $conversations = $conversations->sortBy('id');

        // Make conversation hash table
        $conversationHash = [[
            "role" => "system",
            "content" => "返答は常に500文字以内でお願いします。"
        ]];
        foreach ($conversations as $conversation) {
            if ($conversation->role === 'user') {
                $conversationHash[] = ["role" => $conversation->role, "content" => $conversation->prompt];
                $conversationHash[] = ["role" => 'assistant', "content" => $conversation->response];
            } else if ($conversation->role === 'system') {
                //only display response not request
                $conversationHash[] = [
                    'role' => 'assistant',
                    'content' => $conversation->response
                ];
            }else if ($conversation->role === 'function') {
                // 最後の会話のデータを置き換える
                $conversationHash[count($conversationHash) - 1]['content'] = $conversation->response;
            }
        }

        $role = $isSystem ? "system" : "user";
        // Add new message to conversation hash table
        $conversationHash[] = ["role" => $role, "content" => $message];

        return $conversationHash;
    }

    private function callChatGPT($apiKey, $prompt, $functionId, $model) {
        // Calculate length of all content of prompt array
        $promptLength = 0;
        foreach ($prompt as $item) {
            $promptLength += mb_strlen($item['content']);
        }

        $user_input = UserInput::create([
            'user_id' => Auth::user()->id,
            'input_length' => $promptLength,
            'response_length' => 0,
            'conversation_system_id' => 1  // ChatGPT
        ]);

        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ];
        $data = [
            'model' => $model,
            'messages' => $prompt,
        ];
        if ($functionId) {
            $function = GPTFunction::find($functionId);
            $data['functions'][] = [
                'name' => $function->gpt_name,
                'description' => $function->gpt_description,
                'parameters' => $function->parameters
            ];
            $data['function_call'] = 'auto';
        }
        try {
            $response = Http::withHeaders($headers)->timeout(240)->post($url, $data);
            if ($response->successful()) {
                $user_input->response_length = mb_strlen($response->json('choices')[0]['message']['content']);
                $user_input->save();

                $function = null;
                if (isset($response->json('choices')[0]['message']['function_call'])) {
                    $function_call = $response->json('choices')[0]['message']['function_call'];
                    $function_name = $function_call['name'];
                    $function_args = json_decode($function_call['arguments'], true);
                    $ret = $this->{$function_name}($function_args);

                    $prompt[] = $response->json('choices')[0]['message'];
                    $prompt[] = [
                        'role' => 'function',
                        'name' => $function_name,
                        'content' => $ret
                    ];
                    list($status, $function_response) = $this->callChatGPT($apiKey, $prompt, 0, $model);
                    $function = [
                        'prompt' => $ret,
                        'response' => $function_response
                    ];
                }
                return ["OK", $response->json('choices')[0]['message']['content'], $function, $response->json('model')];
            } else {
                $user_input->save();
                return ["error", $response->json('error')['message'], null, null];
            }
        } catch (ConnectionException $e) {
            $user_input->save();
            return ["connection_error", $e->getMessage(), null, null];
        }
    }

    public function history($mode)
    {
        // Get user's chat history
        $conversations =
            Conversation::
            join('conversation_infos AS ci', function ($join) {
                $join
                    ->on('ci.user_id', '=', 'conversations.user_id')
                    ->on('ci.conversation_system_id', '=', 'conversations.conversation_system_id')
                    ->on('ci.conversation_token', '=', 'conversations.conversation_token');
            })
            ->select([
                'conversations.*',
                'ci.title AS title',
                'ci.is_visible AS is_visible',
                'ci.function_id AS function_id',
                'ci.model AS model'
            ])
            ->where('conversations.user_id', Auth::user()->id)
            ->where('conversations.conversation_system_id', $mode)
            ->where('is_visible', true)
            ->orderBy('id', 'asc')
            ->get();

        // Grouping with conversation_token
        $conversationHash = [];
        $response = [];
        foreach ($conversations as $conversation) {
            // If conversation_token not in conversationHash, add conversation_token to response
            if (!array_key_exists($conversation->conversation_token, $conversationHash)) {
                $response[] = [
                    'timestamp' => $conversation->created_at->toDateTimeString(),
                    'conversation_token' => $conversation->conversation_token,
                    'title' => $conversation->title,
                    'function_id' => $conversation->function_id,
                    'model' => $conversation->model
                ];
            }
            if ($conversation->role === 'user') {
                $conversationHash[$conversation->conversation_token][] = [
                    'role' => 'user',
                    'content' => $conversation->message
                ];
                $conversationHash[$conversation->conversation_token][] = [
                    'role' => 'assistant',
                    'content' => $conversation->response . $conversation->source
                ];
            } else if ($conversation->role === 'system') {
                //only display response not request
                $conversationHash[$conversation->conversation_token][] = [
                    'role' => 'assistant',
                    'content' => $conversation->response . $conversation->source
                ];
            }else if ($conversation->role === 'function') {
                $conversationHash[$conversation->conversation_token][count($conversationHash[$conversation->conversation_token]) - 1] = [
                    'role' => 'assistant',
                    'content' => $conversation->response
                ];
                }
            }

        for ($i = 0; $i < count($response); $i++) {
            $response[$i]['conversations'] = $conversationHash[$response[$i]['conversation_token']];
        }
        rsort($response);

        return $response;
    }

    // Set invisible chat history
    public function hideHistory(Request $request)
    {
        $conversationToken = $request->conversationToken;
        $mode = $request->mode;

        ConversationInfo::
            where('conversation_token', $conversationToken)
            ->where('user_id', Auth::user()->id)
            ->where('conversation_system_id', $mode)
            ->update(['is_visible' => false]);

        return response()->json([
            'status' => 'success',
        ]);
   }

    // Set visible chat history
    public function showHistory(Request $request)
    {
        $conversationToken = $request->conversationToken;
        $mode = $request->mode;

        ConversationInfo::
            where('conversation_token', $conversationToken)
            ->where('user_id', Auth::user()->id)
            ->where('conversation_system_id', $mode)
            ->update(['is_visible' => true]);

        return response()->json([
            'status' => 'success',
        ]);
   }

    // Check sensitive keyword in message by Cloud Natural Language API
    public function checkMessage(Request $request)
    {
        // Get POST parameter
        $message = $request->message;

        try {
            // NGワードのチェック
            $prohibited_rows = ProhibitedWord::where('company_id', Auth::user()->company_id)->get();
            foreach ($prohibited_rows as $row) {
            if ($row->word !== "" && str_contains($message, $row->word)) {
                    return response()->json([
                        'status' => 'NG',
                        'message' => 'NGワードが含まれているため、メッセージを送信できません。',
                    ]);
                }
            }

            // メールアドレスのチェック
            if ($ret = preg_match('/[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*/', $message)) {
                return response()->json([
                    'status' => 'Pending',
                    'message' => "個人情報と思われる文言が含まれています。\nメッセージを送信しますか？",
                ]);
            }

            // 個人情報（名前と思われる文字列）のチェック
            $apiKey = env('GCP_NATURAL_LANGUAGE_API_KEY');
            $url = 'https://language.googleapis.com/v1/documents:analyzeEntities?key=' . $apiKey;
            $headers = [
                'Content-Type' => 'application/json',
            ];
            $data = [
                'document' => [
                    'type' => 'PLAIN_TEXT',
                    'language' => 'JA',
                    'content' => $message
                ],
                "encodingType" => "UTF8",
            ];
            $response = Http::withHeaders($headers)->timeout(20)->post($url, $data);
            if ($response->successful()) {
                $entities = $response->json('entities');
                foreach ($entities as $entity) {
                    if ($entity['type'] === 'PERSON') {
                        return response()->json([
                            'status' => 'Pending',
                            'message' => "個人情報と思われる文言が含まれています。\nメッセージを送信しますか？",
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'OK',
                'message' => ''
            ]);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ChatGPTのレスポンス速度を返す
    public function speed()
    {
        // 一旦固定値
        return response()->json([
            'status' => 2,
        ]);
    }

    private function web_browsing($args)
    {
        // Set User-Agent and Content-Type
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
            ]
        ]);

        // Get the text of the content of URL
        $content = @file_get_contents($args['url'], false, $context);
        if (!$content) {
            return "Webサイトの取得に失敗しました。";
        }

        $dom = new \DOMDocument;
        @$dom->loadHTML($content);

        $body = $dom->getElementsByTagName('main');
        if ($body->length > 0) {
            $body = $body->item(0);
        } else {
            $body = $dom->getElementsByTagName('article');
            if ($body->length > 0) {
                $body = $body->item(0);
            } else {
                $body = $dom->getElementById('main');
                if ($body) {
                    //
                } else {
                    $body = $dom->getElementsByTagName('body');
                    if ($body->length > 0) {
                        $body = $body->item(0);
                    } else {
                        return "Webサイトの取得に失敗しました。";
                    }
                }
            }
        }

        while (($r = $body->getElementsByTagName("script")) && $r->length) {
            $r->item(0)->parentNode->removeChild($r->item(0));
        }
        $body_content = $dom->saveHTML($body);
        $text = strip_tags($body_content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = mb_substr($text, 0, 2000);

        return $text;
    }
}
