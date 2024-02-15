<?php

namespace App\Mail;

use App\Models\News\News;
use App\Models\Partner\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsMail extends Mailable
{
    use SerializesModels;

    public function __construct(public News $news, public Partner $partner)
    {

    }

    public $subject = "Новости компании Алые Паруса";

    public function build(): self
    {
        return $this->view('emails.news');
    }
}
