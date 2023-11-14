<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use App\Models\User;

class ContactController extends Controller
{
    public function showForm()
    {
        return view('contact');
    }

    public function submitForm(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ]);

        // 一般利用者の場合は法人管理者にメールする
        if (Auth::user()->role === 3) {
            $to_address = User::where('company_id', Auth::user()->company_id)->where('role', 2)->first()->email;
            Mail::to($to_address)->send(new ContactMail([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message
            ]));
            return redirect()->back()->with('success', '問い合わせを送信しました。');
        } else {
            $client = new Client();
            $response = $client->post(env('SLACK_WEBHOOK_CONTACT_URL'), [
                'json' => [
                    'text' =>
                        '法人：' . Auth::user()->company->name . "\n" .
                        'お名前：' . $request->name . "\n" .
                        'メールアドレス：' . $request->email . "\n" .
                        'お問い合わせ内容：' . "\n" . $request->message
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                // 送信完了のメッセージを表示
                return redirect()->back()->with('success', '問い合わせを送信しました。');
            } else {
                // 送信失敗のメッセージを表示
                return redirect()->back()->with('error', '問い合わせを送信できませんでした。');
            }
        }
    }
}
