<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MappingMailManager extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.mapping_email')
             ->from(env('mail_from_address'), env('MAIL_FROM_NAME'))
             ->subject($this->array['subject'])
             ->with([
                 'user_id' => $this->array['user_id'],
                 'password' => $this->array['password'],
                 'account_type' => $this->array['account_type']
             ]);
    }
}
