<?php

namespace LibrarySystem\Classes;

use LibrarySystem\Interfaces\Notifier;
use LibrarySystem\Traits\Loggable;

class NotificationService
{
    use Loggable;

    public function sendNotification(Notifier $notifier, string $message): bool
    {
        $result = $notifier->send($message);
        
        if ($result) {
            $this->log("تم إرسال الإشعار بنجاح: {$message}");
        } else {
            $this->log("فشل في إرسال الإشعار: {$message}");
        }
        
        return $result;
    }
}

