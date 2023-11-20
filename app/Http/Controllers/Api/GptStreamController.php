<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Conversation;
use App\Models\UserInput;
use App\Models\ConversationInfo;
use OpenAI\Laravel\Facades\OpenAI;

class GptStreamController extends Controller
{
    public function index(Request $request)
    {
        if (env('APP_DISABLE_BUFFERING')) {
            ini_set('output_buffering', 'off'); 
            
            header('Transfer-Encoding: chunked');
            // fastcgi_finish_request();
        }
        // Get POST parameter
        $message = $request->message;
        $isSystem = $request->isSystem;
        $conversationToken = $request->conversationToken;
        $functionId = $request->input('functionId', 0);
        $model = 'gpt-4-1106-preview';
        if (mb_strtolower($request->model) === 'gpt-3.5-turbo') {
            $model = 'gpt-3.5-turbo';
        } else if (mb_strtolower($request->model) === 'gpt-4') {
            $model = 'gpt-4';
        }

        //\Log::info('index method called');

        // Check conversationId
        if (!$conversationToken) {
            $conversationToken = (string)Str::uuid();
            //\Log::info('Generated new conversation token: ' . $conversationToken);
        } else {
            //\Log::info('Received existing conversation token: ' . $conversationToken);
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

        $data = [
            'model' => $model,
            'messages' => $prompt,
        ];

        return response()->stream(function () use ($data, $user_input, $conversationToken, $conversationInfo, $functionId, $model, $prompt, $message) {
            $stream = OpenAI::chat()->createStreamed($data);
            $fullResponseText = "";

            foreach ($stream as $response) {
                $text = $response->choices[0]->delta->content;
                if (connection_aborted()) {
                    break;
                }
                $fullResponseText .= $text;

                echo $text;
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            $user_input->response_length = mb_strlen($fullResponseText);
            $user_input->save();
            // Save conversation data to DB with the full response text
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
            Conversation::create([
                'conversation_token' => $conversationToken,
                'conversation_system_id' => 1,      // ChatGPT
                'user_id' => Auth::user()->id,
                'role' => end($prompt)['role'],
                'message' => $message,
                'prompt' => end($prompt)['content'],
                'response' => $fullResponseText,
                'function_id' => $functionId,
                'model' => $model
            ]);

            // flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
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
            }else if ($conversation->role === 'system') {
                //only display response not request
                $conversationHash[] = [
                    'role' => 'assistant',
                    'content' => $conversation->response
                ];
            } else if ($conversation->role === 'function') {
                // 最後の会話のデータを置き換える
                $conversationHash[count($conversationHash) - 1]['content'] = $conversation->response;
            }
        }

        // Add new message to conversation hash table
        $role = $isSystem ? "system" : "user";
        $conversationHash[] = ["role" => $role, "content" => $message];

        return $conversationHash;
    }

}
