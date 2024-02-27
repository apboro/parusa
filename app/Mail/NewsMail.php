<?php

namespace App\Mail;

use App\Models\News\News;
use App\Models\User\User;
use App\Models\User\UserProfile;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsMail extends Mailable
{
    use SerializesModels;

    public function __construct(public News $news, public UserProfile $userProfile)
    {

    }

    public function build(): self
    {
        return $this->view('email.news')->subject($this->news->title);
    }
}
