<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $name;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $token, $first_name, $last_name)
    {
        $this->name = trim($first_name . ' '. $last_name);
        $this->url = 'http://localhost:4200/#/reset-password/'.$email.'/'.$token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('email.reset-password')->with([
            'name' => $this->name,
            'url' => $this->url
        ]);
    }
}
