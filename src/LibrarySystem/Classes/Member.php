<?php

namespace LibrarySystem\Classes;

class Member extends User
{
    private array $borrowedBooks = [];
    private string $membershipExpiryDate;

    public function __construct(string $id, string $name, string $membershipExpiryDate)
    {
        parent::__construct($id, $name);
        $this->membershipExpiryDate = $membershipExpiryDate;
    }

    public function borrowBook(Book $book): bool
    {
        if ($this->isMembershipExpired()) {
            $this->log("Membership for {$this->getName()} has expired. Cannot borrow books.");
            return false;
        }

        if ($book->borrow($this->getId())) {
            $this->borrowedBooks[] = $book->getIsbn();
            $this->log("Member {$this->getName()} borrowed book: {$book->getTitle()}");
            return true;
        }
        
        $this->log("Failed to borrow book {$book->getTitle()} for member {$this->getName()}");
        return false;
    }

    public function returnBook(Book $book): bool
    {
        $isbn = $book->getIsbn();
        $key = array_search($isbn, $this->borrowedBooks);
        
        if ($key !== false && $book->returnBook()) {
            unset($this->borrowedBooks[$key]);
            $this->borrowedBooks = array_values($this->borrowedBooks); // Re-index array
            $this->log("Member {$this->getName()} returned book: {$book->getTitle()}");
            return true;
        }
        
        $this->log("Failed to return book {$book->getTitle()} for member {$this->getName()}");
        return false;
    }

    public function getBorrowedBooks(): array
    {
        return $this->borrowedBooks;
    }

    public function isMembershipExpired(): bool
    {
        $expiryDate = new \DateTime($this->membershipExpiryDate);
        $currentDate = new \DateTime();
        return $currentDate > $expiryDate;
    }

    public function getMembershipExpiryDate(): string
    {
        return $this->membershipExpiryDate;
    }

    public function interactWithLibrary(Library $library): void
    {
        $this->log("Member {$this->getName()} interacts with library - can borrow and return books");
    }
}

