<?php
require_once('mail.php');
require_once('config.php');
session_start(); // Start the session

// Database connection
$dbConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
if (!$dbConn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Generate a random string
function random_string($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return strtoupper($randomString);
}
// Fetch user records
function getUserRecords() {
    global $dbConn;
    $sql = "SELECT * FROM tbl_users ORDER BY id ASC";
    $result = mysqli_query($dbConn, $sql);

    $records = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }

    return $records;
}

// Check if user session exists
function checkFDUser() {
    if (!isset($_SESSION['calendar_fd_user'])) {
        header('Location: login.php');
        exit;
    }
    if (isset($_GET['logout'])) {
        doLogout();
    }
}

// Login function
function doLogin() {
    global $dbConn;
    $name = $_POST['name'];
    $pwd = $_POST['pwd'];
    $errorMessage = '';

    $stmt = $dbConn->prepare("SELECT * FROM tbl_users WHERE name = ? AND status = 'active'");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($pwd, $row['pwd'])) {
            $_SESSION['calendar_fd_user'] = $row;
            $_SESSION['calendar_fd_user_name'] = $row['name'];
            header('Location: index.php');
            exit();
        } else {
            $errorMessage = 'Invalid username or password.';
        }
    } else {
        $errorMessage = 'Invalid username or user is not active.';
    }

    return $errorMessage;
}

// Logout function
function doLogout() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

// User registration
function registerUser() {
    global $dbConn;
    $name = $_POST['name'];
    $pwd = password_hash($_POST['pwd'], PASSWORD_DEFAULT); // Secure password hashing
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $type = $_POST['type'];

    // Check if user already exists
    $stmt = $dbConn->prepare("SELECT * FROM tbl_users WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errorMessage = 'User with the same name already exists.';
        header('Location: register.php?err=' . urlencode($errorMessage));
        exit();
    }

    // Insert new user
    $stmt = $dbConn->prepare("INSERT INTO tbl_users (name, pwd, address, phone, email, type, status, bdate) 
                              VALUES (?, ?, ?, ?, ?, ?, 'inactive', NOW())");
    $stmt->bind_param("ssssss", $name, $pwd, $address, $phone, $email, $type);
    $stmt->execute();

    // Send confirmation email
    $bodymsg = "User $name has been registered and is currently INACTIVE. Contact admin for activation.";
    $subject = "New User Registration";
    $headers = "From: admin@example.com\r\nContent-Type: text/html; charset=UTF-8\r\n";

    mail($email, $subject, $bodymsg, $headers);

    header('Location: register.php?msg=' . urlencode('User successfully registered.'));
    exit();
}

// Fetch booking records
function getBookingRecords() {
    global $dbConn;
    $per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $per_page;

    $sql = "SELECT u.id AS uid, u.name, u.phone, u.email,
                   r.ucount, r.rdate, r.status, r.comments   
            FROM tbl_users u
            JOIN tbl_reservations r ON u.id = r.uid  
            ORDER BY r.id DESC LIMIT ?, ?";
    $stmt = $dbConn->prepare($sql);
    $stmt->bind_param("ii", $start, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    return $records;
}

// Generate pagination
function generatePagination($tableName, $per_page = 10) {
    global $dbConn;
    $sql = "SELECT COUNT(*) AS count FROM $tableName";
    $result = dbQuery($sql);
    $row = dbFetchAssoc($result);
    $count = $row['count'];
    $pages = ceil($count / $per_page);

    $pagination = '<ul class="pagination pagination-sm no-margin pull-right">';
    for ($i = 1; $i <= $pages; $i++) {
        $pagination .= "<li><a href=\"?v=" . strtoupper($tableName) . "&page=$i\">$i</a></li>";
    }
    $pagination .= "</ul>";
    return $pagination;
}
?>
