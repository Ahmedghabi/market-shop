<?php

namespace App\Dto\Notification;

final class NotificationTemplateOutput
{
    public string $id;
    public ?string $boutiqueId;
    public string $eventCode;
    public string $channel;
    public ?string $subject;
    public string $content;
    public bool $isActive;
}
