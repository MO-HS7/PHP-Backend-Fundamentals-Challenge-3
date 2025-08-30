<?php

namespace LibrarySystem\Classes;

use LibrarySystem\Interfaces\Notifier;

class SMSNotifier implements Notifier
{
    private string $phoneNumber;

    public function __construct(string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function send(string $message): bool
    {
        // Simulate sending SMS
        echo "<div class='notification sms-notification'>";
        echo "<strong>SMS notification sent to {$this->phoneNumber}:</strong><br>";
        echo $message;
        echo "</div>";
        
        return true;
    }
}

