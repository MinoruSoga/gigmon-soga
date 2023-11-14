<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_DependencyContainer;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'))
                    ->subject(__('mail.[GiGMON] We have received your inquiry'))
                    ->view('email.contact')  // Blade file for Mail
                    ->withSwiftMessage(function ($message) {
                        $message->setCharset('UTF-8');
                        $message->setEncoder(Swift_DependencyContainer::getInstance()->lookup('mime.7bitcontentencoder'));
                    })
                    ->with('data', $this->data);
    }
}
