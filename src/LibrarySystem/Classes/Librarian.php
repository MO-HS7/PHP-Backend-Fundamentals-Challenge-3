<?php

namespace LibrarySystem\Classes;

class Librarian extends User
{
    public function addBook(Library $library, Book $book): void
    {
        $library->addBook($book);
        $this->log("Librarian {$this->getName()} added book: {$book->getTitle()}");
    }

    public function removeBook(Library $library, string $isbn): bool
    {
        $result = $library->removeBook($isbn);
        if ($result) {
            $this->log("Librarian {$this->getName()} deleted book with ISBN: {$isbn}");
        } else {
            $this->log("Librarian {$this->getName()} failed to delete book with ISBN: {$isbn}");
        }
        return $result;
    }

    public function interactWithLibrary(Library $library): void
    {
        $this->log("Librarian {$this->getName()} interacts with library - can add and remove books");
    }
}

