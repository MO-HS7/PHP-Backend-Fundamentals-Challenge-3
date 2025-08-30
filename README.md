The following is a digest of the repository "library-system".
This digest is designed to be easily parsed by Large Language Models.

--- SUMMARY ---
Repository: library-system
Files Analyzed: 16
Total Text Size: 43.51 KB
Estimated Tokens (text only): ~11,812

--- DIRECTORY STRUCTURE ---
library-system/
├── src/
│   └── LibrarySystem/
│       ├── Classes/
│       │   ├── Book.php
│       │   ├── EmailNotifier.php
│       │   ├── Librarian.php
│       │   ├── Library.php
│       │   ├── Member.php
│       │   ├── NotificationService.php
│       │   ├── SMSNotifier.php
│       │   └── User.php
│       ├── Interfaces/
│       │   └── Notifier.php
│       └── Traits/
│           ├── HasId.php
│           └── Loggable.php
├── composer.json
├── index.php
├── library_log.txt
├── README.md
└── style.css


--- FILE CONTENTS ---
============================================================
FILE: src/LibrarySystem/Classes/Book.php
============================================================
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



============================================================
FILE: src/LibrarySystem/Classes/EmailNotifier.php
============================================================
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



============================================================
FILE: src/LibrarySystem/Classes/Librarian.php
============================================================
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



============================================================
FILE: src/LibrarySystem/Classes/Library.php
============================================================
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



============================================================
FILE: src/LibrarySystem/Classes/Member.php
============================================================
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



============================================================
FILE: src/LibrarySystem/Classes/NotificationService.php
============================================================
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



============================================================
FILE: src/LibrarySystem/Classes/SMSNotifier.php
============================================================
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



============================================================
FILE: src/LibrarySystem/Classes/User.php
============================================================
<?php

namespace LibrarySystem\Classes;

use LibrarySystem\Traits\HasId;
use LibrarySystem\Traits\Loggable;

abstract class User
{
    use HasId, Loggable;

    private string $name;

    public function __construct(string $id, string $name)
    {
        $this->setId($id);
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function interactWithLibrary(Library $library): void;
}



============================================================
FILE: src/LibrarySystem/Interfaces/Notifier.php
============================================================
<?php

namespace LibrarySystem\Interfaces;

interface Notifier
{
    public function send(string $message): bool;
}



============================================================
FILE: src/LibrarySystem/Traits/HasId.php
============================================================
<?php

namespace LibrarySystem\Traits;

trait HasId
{
    private string $id;

    public function generateUniqueId(): string
    {
        return uniqid('id_', true);
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}



============================================================
FILE: src/LibrarySystem/Traits/Loggable.php
============================================================
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



============================================================
FILE: composer.json
============================================================
{
    "name": "library-system/library-management",
    "description": "A simple library management system using PHP OOP principles",
    "type": "project",
    "autoload": {
        "psr-4": {
            "LibrarySystem\\": "src/LibrarySystem/"
        }
    },
    "require": {
        "php": ">=7.4"
    }
}



============================================================
FILE: index.php
============================================================
<?php
session_start();
require_once 'vendor/autoload.php';

use LibrarySystem\Classes\Library;
use LibrarySystem\Classes\Book;
use LibrarySystem\Classes\Member;
use LibrarySystem\Classes\Librarian;
use LibrarySystem\Classes\NotificationService;
use LibrarySystem\Classes\EmailNotifier;
use LibrarySystem\Classes\SMSNotifier;
use LibrarySystem\Traits\Loggable;

// Initialize the library system
$library = new Library();
$notificationService = new NotificationService();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_book':
                $book = new Book($_POST['title'], $_POST['author'], $_POST['isbn']);
                $library->addBook($book);
                break;
                
            case 'add_member':
                $member = new Member($_POST['user_id'], $_POST['name'], $_POST['expiry_date']);
                $library->registerUser($member);
                break;
                
            case 'add_librarian':
                $librarian = new Librarian($_POST['user_id'], $_POST['name']);
                $library->registerUser($librarian);
                break;
                
            case 'borrow_book':
                $library->borrowBook($_POST['user_id'], $_POST['isbn']);
                // Send notification
                $emailNotifier = new EmailNotifier('member@library.com');
                $notificationService->sendNotification($emailNotifier, "Book borrowed successfully!");
                break;
                
            case 'return_book':
                $library->returnBook($_POST['user_id'], $_POST['isbn']);
                // Send SMS notification
                $smsNotifier = new SMSNotifier('+1234567890');
                $notificationService->sendNotification($smsNotifier, "Book returned successfully!");
                break;
        }
    }
}

// Sample data for demonstration
if (empty($library->getBooks())) {
    // Add sample books
    $book1 = new Book("Object-Oriented Programming in PHP", "Ahmed Mohammed", "978-1234567890");
    $book2 = new Book("Modern Web Development", "Fatima Ali", "978-0987654321");
    $book3 = new Book("Advanced Database Systems", "Mohammed Hassan", "978-1122334455");
    
    $library->addBook($book1);
    $library->addBook($book2);
    $library->addBook($book3);
    
    // Add sample users
    $member1 = new Member("M001", "Sarah Ahmed", "2024-12-31");
    $member2 = new Member("M002", "Ali Mohammed", "2024-06-30");
    $librarian1 = new Librarian("L001", "Dr. Khalid Al-Ali");
    
    $library->registerUser($member1);
    $library->registerUser($member2);
    $library->registerUser($librarian1);
}

// Search functionality
$searchResults = [];
if (isset($_GET['search']) && !empty($_GET['search_query'])) {
    $searchResults = $library->searchBooks($_GET['search_query'], $_GET['search_type'] ?? 'title');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Library Management System</h1>
            <p>Comprehensive library management system using PHP OOP principles</p>
        </header>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?= count($library->getBooks()) ?></div>
                <div class="stat-label">Total Books</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= count($library->getUsers()) ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">
                    <?php
                    $availableBooks = 0;
                    foreach ($library->getBooks() as $book) {
                        if ($book->isAvailable()) $availableBooks++;
                    }
                    echo $availableBooks;
                    ?>
                </div>
                <div class="stat-label">Available Books</div>
            </div>
        </div>

        <div class="grid">
            <!-- Add Book Form -->
            <div class="card">
                <h2>Add New Book</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_book">
                    <div class="form-group">
                        <label for="title">Book Title:</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author:</label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="isbn">ISBN Number:</label>
                        <input type="text" id="isbn" name="isbn" required>
                    </div>
                    <button type="submit">Add Book</button>
                </form>
            </div>

            <!-- Add Member Form -->
            <div class="card">
                <h2>Add New Member</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_member">
                    <div class="form-group">
                        <label for="user_id">Member ID:</label>
                        <input type="text" id="user_id" name="user_id" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Member Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="expiry_date">Membership Expiry Date:</label>
                        <input type="date" id="expiry_date" name="expiry_date" required>
                    </div>
                    <button type="submit">Add Member</button>
                </form>
            </div>
        </div>

        <div class="grid">
            <!-- Borrow Book Form -->
            <div class="card">
                <h2>Borrow Book</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="borrow_book">
                    <div class="form-group">
                        <label for="borrow_user_id">Member ID:</label>
                        <select id="borrow_user_id" name="user_id" required>
                            <option value="">Select Member</option>
                            <?php foreach ($library->getUsers() as $user): ?>
                                <?php if ($user instanceof Member): ?>
                                    <option value="<?= $user->getId() ?>"><?= $user->getName() ?> (<?= $user->getId() ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="borrow_isbn">Book:</label>
                        <select id="borrow_isbn" name="isbn" required>
                            <option value="">Select Book</option>
                            <?php foreach ($library->getBooks() as $book): ?>
                                <?php if ($book->isAvailable()): ?>
                                    <option value="<?= $book->getIsbn() ?>"><?= $book->getTitle() ?> - <?= $book->getAuthor() ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">Borrow Book</button>
                </form>
            </div>

            <!-- Return Book Form -->
            <div class="card">
                <h2>Return Book</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="return_book">
                    <div class="form-group">
                        <label for="return_user_id">Member ID:</label>
                        <select id="return_user_id" name="user_id" required>
                            <option value="">Select Member</option>
                            <?php foreach ($library->getUsers() as $user): ?>
                                <?php if ($user instanceof Member): ?>
                                    <option value="<?= $user->getId() ?>"><?= $user->getName() ?> (<?= $user->getId() ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="return_isbn">Book:</label>
                        <select id="return_isbn" name="isbn" required>
                            <option value="">Select Book</option>
                            <?php foreach ($library->getBooks() as $book): ?>
                                <?php if (!$book->isAvailable()): ?>
                                    <option value="<?= $book->getIsbn() ?>"><?= $book->getTitle() ?> - <?= $book->getAuthor() ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit">Return Book</button>
                </form>
            </div>
        </div>

        <!-- Search Section -->
        <div class="section">
            <h2>Search Books</h2>
            <form method="GET">
                <div class="form-group">
                    <label for="search_query">Search Term:</label>
                    <input type="text" id="search_query" name="search_query" value="<?= $_GET['search_query'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label for="search_type">Search In:</label>
                    <select id="search_type" name="search_type">
                        <option value="title" <?= ($_GET['search_type'] ?? '') === 'title' ? 'selected' : '' ?>>Title</option>
                        <option value="author" <?= ($_GET['search_type'] ?? '') === 'author' ? 'selected' : '' ?>>Author</option>
                    </select>
                </div>
                <button type="submit" name="search" value="1">Search</button>
            </form>

            <?php if (!empty($searchResults)): ?>
                <h3>Search Results:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $book): ?>
                            <tr>
                                <td><?= htmlspecialchars($book->getTitle()) ?></td>
                                <td><?= htmlspecialchars($book->getAuthor()) ?></td>
                                <td><?= htmlspecialchars($book->getIsbn()) ?></td>
                                <td class="<?= $book->isAvailable() ? 'status-available' : 'status-borrowed' ?>">
                                    <?= $book->isAvailable() ? 'Available' : 'Borrowed' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Books List -->
        <div class="section">
            <h2>Books List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Status</th>
                        <th>Borrowed By</th>
                        <th>Late Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($library->getBooks() as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book->getTitle()) ?></td>
                            <td><?= htmlspecialchars($book->getAuthor()) ?></td>
                            <td><?= htmlspecialchars($book->getIsbn()) ?></td>
                            <td class="<?= $book->isAvailable() ? 'status-available' : 'status-borrowed' ?>">
                                <?= $book->isAvailable() ? 'Available' : 'Borrowed' ?>
                            </td>
                            <td><?= $book->getBorrowedBy() ?? '-' ?></td>
                            <td>
                                <?php 
                                $lateFee = $library->calculateLateFee($book->getIsbn());
                                echo $lateFee > 0 ? '$' . number_format($lateFee, 2) : '-';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Users List -->
        <div class="section">
            <h2>Users List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Additional Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($library->getUsers() as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user->getId()) ?></td>
                            <td><?= htmlspecialchars($user->getName()) ?></td>
                            <td><?= $user instanceof Member ? 'Member' : 'Librarian' ?></td>
                            <td>
                                <?php if ($user instanceof Member): ?>
                                    Membership expires: <?= $user->getMembershipExpiryDate() ?>
                                    <?php if ($user->isMembershipExpired()): ?>
                                        <span style="color: red;">(Expired)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Demonstration Section -->
        <div class="section">
            <h2>Feature Demonstration</h2>
            <div class="actions">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="demo_polymorphism">
                    <button type="submit" class="btn-small">Show Polymorphism</button>
                </form>
            </div>

            <?php if (isset($_POST['action']) && $_POST['action'] === 'demo_polymorphism'): ?>
                <h3>Polymorphism Demonstration:</h3>
                <?php
                // Demonstrate polymorphism with different notifiers
                $emailNotifier = new EmailNotifier('admin@library.com');
                $smsNotifier = new SMSNotifier('+966501234567');
                
                $notificationService->sendNotification($emailNotifier, "Welcome to the Library Management System!");
                $notificationService->sendNotification($smsNotifier, "Reminder: You have a book due soon.");
                ?>
            <?php endif; ?>

            <h3>Additional Features:</h3>
            <div class="actions">
                <button onclick="showSortedBooks('title')" class="btn-small">Sort by Title</button>
                <button onclick="showSortedBooks('author')" class="btn-small">Sort by Author</button>
            </div>
            
            <div id="sorted-books" style="margin-top: 1rem;"></div>
        </div>

        <!-- Log Section -->
        <div class="log-section">
            <h2>System Logs</h2>
            <?php 
            $logs = \LibrarySystem\Traits\Loggable::getSessionLogs();
            if (!empty($logs)): 
            ?>
                <?php foreach ($logs as $log): ?>
                    <div class="log-entry"><?= htmlspecialchars($log) ?></div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No recent activity logs.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showSortedBooks(sortBy) {
            // This would normally be an AJAX call, but for simplicity, we'll use a form submission
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="sort_books">
                             <input type="hidden" name="sort_by" value="${sortBy}">`;
            document.body.appendChild(form);
            form.submit();
        }
    </script>

    <?php
    // Handle sorting demonstration
    if (isset($_POST['action']) && $_POST['action'] === 'sort_books') {
        echo "<script>document.getElementById('sorted-books').innerHTML = '<h4>Books sorted by " . 
             ($_POST['sort_by'] === 'title' ? 'Title' : 'Author') . ":</h4>';";
        
        $sortedBooks = $library->sortBooks($_POST['sort_by']);
        echo "document.getElementById('sorted-books').innerHTML += '<ul>';";
        foreach ($sortedBooks as $book) {
            echo "document.getElementById('sorted-books').innerHTML += '<li>" . 
                 htmlspecialchars($book->getTitle()) . " - " . 
                 htmlspecialchars($book->getAuthor()) . "</li>';";
        }
        echo "document.getElementById('sorted-books').innerHTML += '</ul>';</script>";
    }
    ?>
</body>
</html>



============================================================
FILE: library_log.txt
============================================================
[2025-08-30 22:43:24] New book added: Object-Oriented Programming in PHP by Ahmed Mohammed
[2025-08-30 22:43:24] New book added: Modern Web Development by Fatima Ali
[2025-08-30 22:43:24] New book added: Advanced Database Systems by Mohammed Hassan
[2025-08-30 22:43:24] New user registered: Sarah Ahmed
[2025-08-30 22:43:24] New user registered: Ali Mohammed
[2025-08-30 22:43:24] New user registered: Dr. Khalid Al-Ali
[2025-08-30 22:43:46] New book added: m by m
[2025-08-30 22:43:55] New book added: m by m
[2025-08-30 22:44:09] New user registered: 1
[2025-08-30 22:44:09] New book added: Object-Oriented Programming in PHP by Ahmed Mohammed
[2025-08-30 22:44:09] New book added: Modern Web Development by Fatima Ali
[2025-08-30 22:44:09] New book added: Advanced Database Systems by Mohammed Hassan
[2025-08-30 22:44:09] New user registered: Sarah Ahmed
[2025-08-30 22:44:09] New user registered: Ali Mohammed
[2025-08-30 22:44:09] New user registered: Dr. Khalid Al-Ali
[2025-08-30 22:46:12] New user registered: 1
[2025-08-30 22:46:12] New book added: Object-Oriented Programming in PHP by Ahmed Mohammed
[2025-08-30 22:46:12] New book added: Modern Web Development by Fatima Ali
[2025-08-30 22:46:12] New book added: Advanced Database Systems by Mohammed Hassan
[2025-08-30 22:46:12] New user registered: Sarah Ahmed
[2025-08-30 22:46:12] New user registered: Ali Mohammed
[2025-08-30 22:46:12] New user registered: Dr. Khalid Al-Ali
[2025-08-30 22:46:22] New book added: m by m
[2025-08-30 22:46:27] New book added: Object-Oriented Programming in PHP by Ahmed Mohammed
[2025-08-30 22:46:27] New book added: Modern Web Development by Fatima Ali
[2025-08-30 22:46:27] New book added: Advanced Database Systems by Mohammed Hassan
[2025-08-30 22:46:27] New user registered: Sarah Ahmed
[2025-08-30 22:46:27] New user registered: Ali Mohammed
[2025-08-30 22:46:27] New user registered: Dr. Khalid Al-Ali
[2025-08-30 22:46:27] تم ترتيب الكتب حسب: author


============================================================
FILE: README.md
============================================================
# نظام إدارة المكتبة - Library Management System

نظام شامل لإدارة المكتبات مطور باستخدام مبادئ البرمجة الكائنية في PHP مع تطبيق معايير PSR-4 للتحميل التلقائي.

## المتطلبات المطبقة ✅

### 1. PSR-4 Autoloading
- ✅ تم تنظيم الكود في مجلد `src` تحت namespace `LibrarySystem`
- ✅ تم إعداد `composer.json` مع PSR-4 autoloading
- ✅ تم استخدام `composer dump-autoload` لتوليد ملفات التحميل التلقائي

### 2. الفئات الأساسية (4+ فئات)
- ✅ **Book**: فئة الكتب مع التغليف الكامل للبيانات
- ✅ **User**: فئة مجردة للمستخدمين
- ✅ **Member**: فئة الأعضاء (ترث من User)
- ✅ **Librarian**: فئة أمناء المكتبة (ترث من User)
- ✅ **Library**: فئة إدارة المكتبة
- ✅ **NotificationService**: فئة خدمة الإشعارات
- ✅ **EmailNotifier** و **SMSNotifier**: فئات الإشعارات

### 3. مبادئ البرمجة الكائنية

#### التغليف (Encapsulation)
- ✅ جميع الخصائص الحساسة محمية بـ `private`
- ✅ الوصول للبيانات عبر getters/setters
- ✅ حماية البيانات من التعديل المباشر

#### التعدد الشكلي (Polymorphism)
- ✅ واجهة `Notifier` للإشعارات
- ✅ تطبيقات مختلفة: `EmailNotifier` و `SMSNotifier`
- ✅ استخدام موحد عبر `NotificationService`

#### الوراثة (Inheritance)
- ✅ فئة `User` مجردة
- ✅ `Member` و `Librarian` ترثان من `User`
- ✅ سلوك مختلف لكل نوع مستخدم

### 4. الخصائص (Traits)
- ✅ **Loggable**: لتسجيل الأحداث والعمليات
- ✅ **HasId**: لإدارة المعرفات الفريدة

### 5. الميزات الوظيفية

#### إدارة الكتب
- ✅ إضافة كتب جديدة
- ✅ حذف الكتب (مع التحقق من حالة الاستعارة)
- ✅ البحث في الكتب (بالعنوان أو المؤلف)
- ✅ ترتيب الكتب (بالعنوان أو المؤلف)

#### إدارة المستخدمين
- ✅ تسجيل أعضاء جدد
- ✅ تسجيل أمناء مكتبة
- ✅ أدوار مختلفة لكل نوع مستخدم

#### نظام الاستعارة
- ✅ استعارة الكتب (للأعضاء فقط)
- ✅ إرجاع الكتب
- ✅ تتبع حالة الكتب (متاح/مستعار)

#### الإشعارات
- ✅ إشعارات البريد الإلكتروني
- ✅ إشعارات الرسائل النصية
- ✅ تطبيق مبدأ التعدد الشكلي

#### التسجيل
- ✅ تسجيل جميع العمليات مع الطوابع الزمنية
- ✅ حفظ السجلات في ملف `library_log.txt`

### 6. الميزات الإضافية الفريدة

#### حساب رسوم التأخير
- ✅ حساب تلقائي لرسوم التأخير (0.50$ لكل يوم)
- ✅ فترة استعارة قياسية: 14 يوم
- ✅ عرض الرسوم في جدول الكتب

#### انتهاء صلاحية العضوية
- ✅ تتبع تواريخ انتهاء العضوية
- ✅ منع الاستعارة للعضويات المنتهية
- ✅ تمييز بصري للعضويات المنتهية

#### ترتيب الكتب
- ✅ ترتيب حسب العنوان
- ✅ ترتيب حسب المؤلف
- ✅ عرض تفاعلي للنتائج

## هيكل المشروع

```
library-system/
├── src/
│   └── LibrarySystem/
│       ├── Classes/
│       │   ├── Book.php
│       │   ├── Library.php
│       │   ├── User.php (Abstract)
│       │   ├── Member.php
│       │   ├── Librarian.php
│       │   ├── NotificationService.php
│       │   ├── EmailNotifier.php
│       │   └── SMSNotifier.php
│       ├── Interfaces/
│       │   └── Notifier.php
│       └── Traits/
│           ├── Loggable.php
│           └── HasId.php
├── vendor/ (Composer autoload)
├── composer.json
├── index.php (واجهة المستخدم)
├── style.css (التصميم)
├── library_log.txt (ملف السجلات)
└── README.md
```

## التشغيل

### المتطلبات
- PHP 7.4 أو أحدث
- Composer

### خطوات التشغيل
1. استنساخ المشروع
2. تشغيل `composer dump-autoload`
3. بدء خادم PHP: `php -S localhost:8000`
4. فتح المتصفح على `http://localhost:8000`

## الاستخدام

### إضافة كتاب جديد
1. ملء نموذج "إضافة كتاب جديد"
2. إدخال العنوان، المؤلف، ورقم ISBN
3. النقر على "إضافة الكتاب"

### تسجيل عضو جديد
1. ملء نموذج "إضافة عضو جديد"
2. إدخال رقم العضو، الاسم، وتاريخ انتهاء العضوية
3. النقر على "إضافة العضو"

### استعارة كتاب
1. اختيار العضو من القائمة المنسدلة
2. اختيار الكتاب المتاح
3. النقر على "استعارة الكتاب"
4. سيتم إرسال إشعار تأكيد

### البحث عن كتاب
1. إدخال كلمة البحث
2. اختيار نوع البحث (عنوان أو مؤلف)
3. النقر على "بحث"

## الميزات التقنية

### التصميم
- واجهة مستخدم بسيطة وعصرية
- تصميم متجاوب (Responsive)
- دعم اللغة العربية (RTL)
- ألوان متدرجة وتأثيرات بصرية

### الأمان
- تغليف البيانات الحساسة
- التحقق من صحة المدخلات
- حماية من التعديل المباشر للبيانات

### الأداء
- تحميل تلقائي للفئات (PSR-4)
- تنظيم الكود في namespaces
- استخدام الخصائص (Traits) لتجنب التكرار

## المطور

تم تطوير هذا النظام كمشروع تعليمي لتطبيق مبادئ البرمجة الكائنية في PHP، مع التركيز على:
- التطبيق العملي لمعايير PSR-4
- استخدام الواجهات والخصائص
- تطبيق مبادئ SOLID
- إنشاء واجهة مستخدم بسيطة وفعالة

## الترخيص

هذا المشروع مطور لأغراض تعليمية.

# PHP-Backend-Fundamentals-Challenge-3


============================================================
FILE: style.css
============================================================
/* Modern and simple styling for Library Management System */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f4f4f4;
    direction: ltr;
    text-align: left;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

h2 {
    color: #667eea;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #667eea;
}

.section {
    background: white;
    margin-bottom: 2rem;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
    margin-bottom: 1rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #555;
}

input[type="text"],
input[type="date"],
select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="date"]:focus,
select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

button:active {
    transform: translateY(0);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

th, td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f8f9fa;
    font-weight: bold;
    color: #555;
}

tr:hover {
    background-color: #f8f9fa;
}

.status-available {
    color: #28a745;
    font-weight: bold;
}

.status-borrowed {
    color: #dc3545;
    font-weight: bold;
}

.log-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.log-entry {
    background-color: #e9ecef;
    padding: 0.5rem;
    margin: 0.25rem 0;
    border-radius: 3px;
    font-family: monospace;
    font-size: 0.9rem;
    border-left: 3px solid #667eea;
}

.notification {
    padding: 1rem;
    margin: 0.5rem 0;
    border-radius: 5px;
    border-left: 4px solid;
}

.email-notification {
    background-color: #d4edda;
    border-left-color: #28a745;
}

.sms-notification {
    background-color: #d1ecf1;
    border-left-color: #17a2b8;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.alert {
    padding: 1rem;
    margin: 1rem 0;
    border-radius: 5px;
    border: 1px solid;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.stats {
    display: flex;
    justify-content: space-around;
    text-align: center;
    margin: 2rem 0;
}

.stat-item {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    min-width: 150px;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #667eea;
}

.stat-label {
    color: #666;
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .grid {
        grid-template-columns: 1fr;
    }
    
    .stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .actions {
        justify-content: center;
    }
}
