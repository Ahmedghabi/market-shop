<?php

namespace App\Dto\Notification;

final class NotificationProviderInput
{
    public string $code;
    public string $name;
    public string $type = 'EMAIL';
    public bool $isActive = true;
}
