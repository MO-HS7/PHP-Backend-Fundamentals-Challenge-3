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

