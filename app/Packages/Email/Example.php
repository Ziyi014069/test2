<?php

namespace App\Packages\Email;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Example extends Mailable
{
    use Queueable, SerializesModels;

    private $title = null;
    private $data = null;
    private $type = null;

    public function __construct($data, $title, $type){
        $this->title = $title;
        $this->data = $data;
        $this->type = $type;
    }

    public function build()
    {
        if ($this->type == 'test') {
            return $this->subject('test')->view('emails.template')->with($this->data);
        }
    }
}
