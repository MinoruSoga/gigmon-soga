<?php 
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotWidgetController extends Controller{
    public function chatbotIndex(){
        return view('chatbot.index');
    }
    // public function __construct()
    // {
    //     // ...
    // }

    // public function onOpen($conn)
    // {
    //     // Handle the connection
    // }

    // public function onMessage($conn, $msg)
    // {
    //     // Forward the message to the docsbot API and handle the response
    //     $client = new \GuzzleHttp\Client();
    //     $response = $client->request('POST', 'https://api.docsbot.ai/teams/{teamId}/bots/{botId}/chat', [
    //         'json' => json_decode($msg, true)
    //     ]);

    //     $conn->send($response->getBody());
    // }

    // public function onClose($conn)
    // {
    //     // Handle the connection closing
    // }
}

?>