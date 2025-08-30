<?php

namespace LibrarySystem\Classes;

use LibrarySystem\Traits\Loggable;

class Library
{
    use Loggable;

    private array $books = [];
    private array $users = [];

    public function addBook(Book $book): void
    {
        $this->books[$book->getIsbn()] = $book;
        $this->log("New book added: {$book->getTitle()} by {$book->getAuthor()}");
    }

    public function removeBook(string $isbn): bool
    {
        if (isset($this->books[$isbn])) {
            $book = $this->books[$isbn];
            if ($book->isAvailable()) {
                unset($this->books[$isbn]);
                $this->log("Book deleted: {$book->getTitle()}");
                return true;
            } else {
                $this->log("Cannot delete book {$book->getTitle()} - book is currently borrowed");
                return false;
            }
        }
        $this->log("Book with ISBN {$isbn} not found");
        return false;
    }

    public function findBook(string $query, string $type = 'title'): ?Book
    {
        foreach ($this->books as $book) {
            if ($type === 'title' && stripos($book->getTitle(), $query) !== false) {
                return $book;
            } elseif ($type === 'author' && stripos($book->getAuthor(), $query) !== false) {
                return $book;
            } elseif ($type === 'isbn' && $book->getIsbn() === $query) {
                return $book;
            }
        }
        return null;
    }

    public function searchBooks(string $query, string $type = 'title'): array
    {
        $results = [];
        foreach ($this->books as $book) {
            if ($type === 'title' && stripos($book->getTitle(), $query) !== false) {
                $results[] = $book;
            } elseif ($type === 'author' && stripos($book->getAuthor(), $query) !== false) {
                $results[] = $book;
            }
        }
        return $results;
    }

    public function registerUser(User $user): void
    {
        $this->users[$user->getId()] = $user;
        $this->log("New user registered: {$user->getName()}");
    }

    public function getUser(string $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function getBooks(): array
    {
        return $this->books;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function borrowBook(string $userId, string $isbn): bool
    {
        $user = $this->getUser($userId);
        $book = $this->books[$isbn] ?? null;

        if (!$user instanceof Member) {
            $this->log("User {$userId} is not a member - cannot borrow books");
            return false;
        }

        if (!$book) {
            $this->log("Book with ISBN {$isbn} not found");
            return false;
        }

        return $user->borrowBook($book);
    }

    public function returnBook(string $userId, string $isbn): bool
    {
        $user = $this->getUser($userId);
        $book = $this->books[$isbn] ?? null;

        if (!$user instanceof Member) {
            $this->log("User {$userId} is not a member");
            return false;
        }

        if (!$book) {
            $this->log("Book with ISBN {$isbn} not found");
            return false;
        }

        return $user->returnBook($book);
    }

    public function calculateLateFee(string $isbn, int $daysLate = null): float
    {
        $book = $this->books[$isbn] ?? null;
        if (!$book) {
            return 0.0;
        }

        $daysLate = $daysLate ?? $book->getDaysLate();
        $feePerDay = 0.50; // $0.50 per day
        $totalFee = $daysLate * $feePerDay;

        if ($totalFee > 0) {
            $this->log("رسوم التأخير للكتاب {$book->getTitle()}: {$totalFee} دولار ({$daysLate} أيام تأخير)");
        }

        return $totalFee;
    }

    public function sortBooks(string $sortBy = 'title'): array
    {
        $books = $this->books;
        
        if ($sortBy === 'title') {
            uasort($books, function($a, $b) {
                return strcmp($a->getTitle(), $b->getTitle());
            });
        } elseif ($sortBy === 'author') {
            uasort($books, function($a, $b) {
                return strcmp($a->getAuthor(), $b->getAuthor());
            });
        }

        $this->log("تم ترتيب الكتب حسب: {$sortBy}");
        return $books;
    }
}

