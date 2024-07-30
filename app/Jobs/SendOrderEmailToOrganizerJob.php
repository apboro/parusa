<?php

namespace App\Jobs;

use App\Mail\NewsMail;
use App\Mail\OrderMailToOrganizerMail;
use App\Models\Dictionaries\ExcursionType;
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

class SendOrderEmailToOrganizerJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        $hasLegExcursion = $this->order->tickets->filter(function($ticket){
            return $ticket->trip->excursion->type_id === ExcursionType::legs;
        })->isNotEmpty();

        $hasStandUpExcursion = $this->order->tickets->filter(function($ticket){
            return $ticket->trip->excursion->type_id === ExcursionType::standUp;
        })->isNotEmpty();

        $mailBox = [];
        if ($hasLegExcursion){
            $mailBox[] = 'zakaz-tours@yandex.ru';
        }
        if ($hasStandUpExcursion){
            $mailBox[] = 'standupwalk1@gmail.com';
        }

        $email = new OrderMailToOrganizerMail($this->order);
        Mail::to($mailBox)->send($email);
    }

    public function uniqueId(): string
    {
        return $this->order->id;
    }
}
