<?php

namespace App\Services\Notifications\Contracts;

use App\Services\Notifications\Data\NotificationResult;
use App\Services\Notifications\Data\SmsMessage;

interface SmsSender
{
    public function send(SmsMessage $message): NotificationResult;
}
