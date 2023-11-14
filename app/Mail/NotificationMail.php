<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $user;
    public $plainPassword;
    public $admins;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $plainPassword, $admins)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
        $this->admins = $admins;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('【GiGMON】ユーザー登録のお知らせ')
                    ->view('email.notification');
    }
}
