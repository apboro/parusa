<?php

namespace App\Classes\Mail;

class MailMessage extends \Illuminate\Notifications\Messages\MailMessage
{
    /** @var string Text disclaimer */
    public string $disclaimer;

    /**
     * Set disclaimer text.
     *
     * @param string $text
     *
     * @return  $this
     */
    public function disclaimer(string $text): MailMessage
    {
        $this->disclaimer = $text;

        return $this;
    }

    /**
     * Get an array representation of the message.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'level' => $this->level ?? null,
            'subject' => $this->subject ?? null,
            'greeting' => $this->greeting ?? null,
            'introLines' => $this->introLines ?? null,
            'actionText' => $this->actionText ?? null,
            'actionUrl' => $this->actionUrl ?? null,
            'outroLines' => $this->outroLines ?? null,
            'disclaimer' => $this->disclaimer ?? null,
            'salutation' => $this->salutation ?? null,
            'displayableActionUrl' => str_replace(['mailto:', 'tel:'], '', $this->actionUrl ?? ''),
        ];
    }
}
