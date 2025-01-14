<?php
require_once '../library/config.php'; // Include the config file for database connection

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

// Check if user is logged in
if (!isset($_SESSION['calendar_fd_user'])) {
    header('Location: login.php');
    exit;
}

// Get user details
$userId = $_SESSION['calendar_fd_user']['id'];
$userName = $_SESSION['calendar_fd_user_name'] ?? '';

// Establish database connection
$dbConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Check database connection
if (!$dbConn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch user's signed-up conferences
$myConferences = [];
$query = "
    SELECT p.*, c.name AS conference_name, c.date, c.location, c.added_by AS organizer
    FROM participants p
    JOIN conferences c ON p.conference_id = c.id
    WHERE p.name = '$userName'
";
$result = mysqli_query($dbConn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $myConferences[] = $row;
    }
    mysqli_free_result($result);
}

// Close the database connection
mysqli_close($dbConn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <title>My Conferences</title>
    <style>
        body {
            background-color: #f8f9fa; /* Light background for the page */
        }
        .card {
            background-color: #d6d8db; /* Darker gray background for the conference box */
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s ease-in-out;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-title {
            color:rgb(59, 138, 181); /* Darker text color for the title */
            font-weight: bold;
            font-size: 2.5rem;
        }
        .container h2 {
            color: #343a40;
            margin-bottom: 30px;
        }
        .no-conferences {
            color: #6c757d;
            font-style: italic;
            font-size: 1.2rem;
        }
        .badge {
            font-size: 0.9rem;
            padding: 0.5em 0.75em;
            border-radius: 8px;
        }
        .badge-accepted {
            background-color: #28a745; /* Green for Accepted */
            font-size: 1.5rem;
            color: white;
        }
        .badge-declined {
            background-color: #dc3545; /* Red for Declined */
            font-size: 1.5rem;
            color: white;
        }
        .row.g-4 {
            gap: 1.5rem;
        }
    </style>
</head>
<body>
<div class="container my-4">
    <h2 class="text-center">My Conferences</h2>

    <?php if (!empty($myConferences)) { ?>
        <div class="row g-4">
            <?php foreach ($myConferences as $conference) { ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($conference['conference_name']); ?></h5>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($conference['date']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($conference['location']); ?></p>
                            <p><strong>Organizer:</strong> <?php echo htmlspecialchars($conference['organizer']); ?></p>
                            <p><strong>Status:</strong> 
                                <?php if (strtolower($conference['status']) == 'accepted') { ?>
                                    <span class="badge badge-accepted">Accepted</span>
                                <?php } elseif (strtolower($conference['status']) == 'declined') { ?>
                                    <span class="badge badge-declined">Declined</span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($conference['status']); ?></span>
                                <?php } ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <h3 class="text-center no-conferences mt-4">You haven't signed up for any conferences yet.</h3>
    <?php } ?>
</div>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
