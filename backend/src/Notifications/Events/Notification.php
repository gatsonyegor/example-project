<?php

namespace App\Notifications\Events;

class Notification
{
    public function __construct(
        private string $type,
        private string $title,
        private string $message
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}