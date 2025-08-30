<?php

namespace LibrarySystem\Classes;

use LibrarySystem\Interfaces\Notifier;

class EmailNotifier implements Notifier
{
    private string $emailAddress;

    public function __construct(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function send(string $message): bool
    {
        // Simulate sending email
        echo "<div class='notification email-notification'>";
        echo "<strong>Email notification sent to {$this->emailAddress}:</strong><br>";
        echo $message;
        echo "</div>";
        
        return true;
    }
}

