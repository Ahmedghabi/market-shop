<?php

namespace App\Dto\Notification;

final class NotificationTemplateInput
{
    public ?string $boutiqueId = null;
    public string $eventCode;
    public string $channel = 'EMAIL';
    public ?string $subject = null;
    public string $content;
    public bool $isActive = true;
}
