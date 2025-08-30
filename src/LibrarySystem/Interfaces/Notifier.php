<?php

namespace LibrarySystem\Interfaces;

interface Notifier
{
    public function send(string $message): bool;
}

