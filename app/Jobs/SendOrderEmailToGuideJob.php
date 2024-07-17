<?php

namespace App\Jobs;

use App\Mail\NewsMail;
use App\Mail\OrderMailToGuideMail;
use App\Models\News\News;
use App\Models\Order\Order;
use App\Models\User\UserProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderEmailToGuideJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        $email = new OrderMailToGuideMail($this->order);
        Mail::to(['zakaz-tours@yandex.ru', 'borodachev@gmail.com'])->send($email);
    }

    public function uniqueId(): string
    {
        return $this->order->id;
    }
}
