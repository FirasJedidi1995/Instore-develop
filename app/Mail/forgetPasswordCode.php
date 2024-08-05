<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class forgetPasswordCode extends Mailable
{
    use Queueable, SerializesModels;
    public $data = [] ;
    public function __construct(Array $user)
    {
       $this->data = $user;
    }

    public function build(){
        return $this->view("Email.forgetPassword");
    }
  
}
