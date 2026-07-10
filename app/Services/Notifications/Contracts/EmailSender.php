<?php

namespace App\Services\Notifications\Contracts;

use App\Services\Notifications\Data\EmailMessage;
use App\Services\Notifications\Data\NotificationResult;

interface EmailSender
{
    public function send(EmailMessage $message): NotificationResult;
}
