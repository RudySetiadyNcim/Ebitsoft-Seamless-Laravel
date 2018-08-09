<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordDoneMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name, $last_name)
    {
        $this->name = trim($first_name . ' '. $last_name);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('email.reset-password-done')->with([
            'name' => $this->name,
        ]);
    }
}
