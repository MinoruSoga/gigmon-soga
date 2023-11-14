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
use OpenAI\Laravel\Facades\OpenAI;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocsbotStreamController extends Controller
{
    public function index(Request $request)
    {
        // Get POST parameter
        $message = $request->message;
        $isSystem = $request->isSystem;
        $conversationToken = $request->conversationToken;

        // Get Company info
        $company = Auth::user()->company;

        // Get DocsBot Stream API URL
        $apiUrl = "wss://api.docsbot.ai/teams/" . $company->docsbot_team_id . "/bots/" . $company->docsbot_bot_id . "/chat";


        // Check conversationId
        if (!$conversationToken) {
            // Create conversationId
            $conversationToken = (string)Str::uuid();
        }

        // Get prompt
        $prompt = $this->getPrompt($conversationToken);

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
        $data = [
            'question' => $message,
            'full_source' => false
        ];
        if ($prompt) {
            $data['history'] = $prompt;
        }

        $loop = Factory::create();
        $connector = new Connector($loop);
        try{
            return response()->stream(function () use ($loop, $connector, $apiUrl, $data, $user_input, $conversationToken, $message, $isSystem) {
                $fullMessage = "";  // この変数でメッセージの全文を保持します
                $sources = "";
                $history = [];
                $connector($apiUrl)->then(function($conn) use ($loop, $data, &$fullMessage, &$sources, &$history, $user_input, $conversationToken, $message, $isSystem){
                    $conn->on('message', function($msg) use ($conn, $loop, &$fullMessage, &$sources, &$history) {
                        // JSONを連想配列に変換
                        $msg = json_decode($msg, true);
                        if($msg['type'] == 'stream'){
                            $text = $msg['message'];
                            $fullMessage .= $text;
                            echo $text;
                            ob_flush();
                            flush();
                        }else if($msg['type'] == 'end'){
                            $msg = json_decode($msg['message'], true);
                            $sources = $msg['sources'];
                            $history = $msg['history'];
                        }
                    });

                    $conn->send(json_encode($data));

                    $conn->on('close', function($code = null, $reason = null) use($loop, &$fullMessage, &$sources, &$history, $user_input, $conversationToken, $message, $isSystem) {
                        Log::info("Connection closed");
                        // ob_flush();
                        // flush();
                        $loop->stop();
                        $user_input->response_length = mb_strlen($fullMessage);
                        $user_input->save();

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
                            // 'prompt' => $send_message,
                            'prompt' => $message,
                            // 'response' => $response,
                            'response' => $fullMessage,
                            'source' => $source_info,
                            'history' => json_encode($history)
                        ]);
                        exit;  // ストリームを終了
                    });
                }, function($e) use ($loop) {
                    Log::info("Could not connect: {$e->getMessage()}");
                    $loop->stop();
                });

                // 60秒後に処理を終了
                $loop->addTimer(60, function() use ($loop) {
                    Log::info("Connection closed 60seconds");
                    $loop->stop();
                });

                $loop->run();
            }, 200, [
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Content-Type' => 'text/event-stream',
            ]);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
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
}
