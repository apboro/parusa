<?php

namespace App\Jobs;

use App\Mail\NewsMail;
use App\Models\News\News;
use App\Models\User\UserProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $email, public News $news, public UserProfile $userProfile)
    {
    }

    public function handle(): void
    {
        $email = new NewsMail($this->news, $this->userProfile);
        Mail::to($this->email)->send($email);
    }
}
