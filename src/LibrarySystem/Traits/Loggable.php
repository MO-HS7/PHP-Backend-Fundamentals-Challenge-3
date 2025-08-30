<?php

namespace LibrarySystem\Traits;

trait Loggable
{
    private static $sessionLogs = [];
    
    public function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        
        // Write to log file
        file_put_contents('library_log.txt', $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // Store in session for display
        if (!isset($_SESSION['logs'])) {
            $_SESSION['logs'] = [];
        }
        $_SESSION['logs'][] = $logMessage;
        
        // Keep only last 10 log entries in session
        if (count($_SESSION['logs']) > 10) {
            $_SESSION['logs'] = array_slice($_SESSION['logs'], -10);
        }
    }
    
    public static function getSessionLogs(): array
    {
        return $_SESSION['logs'] ?? [];
    }
}

