<?php

namespace LibrarySystem\Classes;

use LibrarySystem\Traits\HasId;

class Book
{
    use HasId;

    private string $title;
    private string $author;
    private string $isbn;
    private bool $isAvailable = true;
    private ?string $borrowedBy = null;
    private ?string $borrowDate = null;

    public function __construct(string $title, string $author, string $isbn)
    {
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->setId($this->generateUniqueId());
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function getBorrowedBy(): ?string
    {
        return $this->borrowedBy;
    }

    public function getBorrowDate(): ?string
    {
        return $this->borrowDate;
    }

    public function borrow(string $userId): bool
    {
        if ($this->isAvailable) {
            $this->isAvailable = false;
            $this->borrowedBy = $userId;
            $this->borrowDate = date('Y-m-d');
            return true;
        }
        return false;
    }

    public function returnBook(): bool
    {
        if (!$this->isAvailable) {
            $this->isAvailable = true;
            $this->borrowedBy = null;
            $this->borrowDate = null;
            return true;
        }
        return false;
    }

    public function getDaysLate(): int
    {
        if ($this->borrowDate && !$this->isAvailable) {
            $borrowDateTime = new \DateTime($this->borrowDate);
            $currentDateTime = new \DateTime();
            $interval = $currentDateTime->diff($borrowDateTime);
            $daysBorrowed = $interval->days;
            
            // Assuming 14 days is the maximum borrow period
            return max(0, $daysBorrowed - 14);
        }
        return 0;
    }
}

