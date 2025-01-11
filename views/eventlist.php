<?php
require_once '../library/config.php'; // Include the config file for database connection

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

// Check user type
$type = $_SESSION['calendar_fd_user']['type'] ?? '';

// Establish the database connection
$dbConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Check the database connection
if (!$dbConn) {
    die("MySQL connection failed: " . mysqli_connect_error());
}

// Initialize variables
$allConferences = [];
$selectedConferenceId = null;
$showAddForm = ($type == 'teacher'); // Show conference form for teachers only

// Fetch all conferences
$sql = "SELECT * FROM conferences";
$result = mysqli_query($dbConn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $allConferences[] = $row;
    }
    mysqli_free_result($result);
}

// Handle adding a conference (teacher only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_conference']) && $type == 'teacher') {
    $name = mysqli_real_escape_string($dbConn, $_POST['name']);
    $description = mysqli_real_escape_string($dbConn, $_POST['description']);
    $date = mysqli_real_escape_string($dbConn, $_POST['date']);
    $number_of_people = (int)$_POST['number_of_people'];
    $location = mysqli_real_escape_string($dbConn, $_POST['location']);
    $addedBy = mysqli_real_escape_string($dbConn, $_SESSION['calendar_fd_user_name']); 

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'views/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); 
        }
        $imagePath = $uploadDir . basename($_FILES['image']['name']);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            echo "<p style='color: red;'>Error: Unable to upload image.</p>";
            $imagePath = null;
        }
    }

    $sql = "INSERT INTO conferences (name, description, date, number_of_people, location, added_by, image) 
            VALUES ('$name', '$description', '$date', $number_of_people, '$location', '$addedBy', '$imagePath')";

    if (mysqli_query($dbConn, $sql)) {
        echo "<script>alert('Conference added successfully!');</script>";
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($dbConn) . "</p>";
    }
}


// Handle participation form submission (student only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conference_id']) && $type == 'student') {
  if (isset($_POST['submit_participation'])) {
      $conferenceId = (int)$_POST['conference_id'];
      $name = mysqli_real_escape_string($dbConn, $_POST['name']);
      $education = mysqli_real_escape_string($dbConn, $_POST['education']);
      $age = (int)$_POST['age'];
      $job = mysqli_real_escape_string($dbConn, $_POST['job']);
      $paperFileName = null;

      // Handle file upload
      if (isset($_FILES['paper']) && $_FILES['paper']['error'] === UPLOAD_ERR_OK) {
          $uploadDir = 'uploads/';
          if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
          }
          $paperFileName = $uploadDir . basename($_FILES['paper']['name']);
          if (!move_uploaded_file($_FILES['paper']['tmp_name'], $paperFileName)) {
              echo "<p style='color: red;'>Error: Unable to upload file.</p>";
          }
      }

      // Fetch the organizer (added_by) of the selected conference
      $organizerQuery = "SELECT added_by FROM conferences WHERE id = $conferenceId";
      $organizerResult = mysqli_query($dbConn, $organizerQuery);
      $organizer = '';
      if ($organizerResult && $row = mysqli_fetch_assoc($organizerResult)) {
          $organizer = mysqli_real_escape_string($dbConn, $row['added_by']);
      }

      // Insert participant data into the database
      $sql = "INSERT INTO participants (conference_id, name, education, age, job, paper, added_by)
              VALUES ($conferenceId, '$name', '$education', $age, '$job', '$paperFileName', '$organizer')";

      if (mysqli_query($dbConn, $sql)) {
          echo "<script>alert('Participation submitted successfully!');</script>";
      } else {
          echo "<p style='color: red;'>Error: " . mysqli_error($dbConn) . "</p>";
      }
  } else {
      $selectedConferenceId = (int)$_POST['conference_id'];
  }
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
    <style>
/* Container adjustments for overall spacing */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Card design for a bigger, cleaner, professional look */
.card {
    margin: 20px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: #fff;
    width: 100%; /* Allow flexible width */
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

/* Image adjustments for large, consistent visuals */
.card img {
    height: 250px; /* Larger images */
    object-fit: cover;
    width: 100%;
    border-bottom: 1px solid #ddd;
}

/* Card body styling */
.card-body {
    padding: 20px;
    text-align: center;
    font-family: "Arial", sans-serif;
}

/* Title styling for better readability and impact */
.card-title {
    font-size: 1.5rem; /* Larger text for titles */
    font-weight: 700;
    color: #222;
    margin-bottom: 12px;
    text-transform: capitalize;
}

/* Text and paragraph improvements */
.card-text {
    font-size: 1.2rem; /* Bigger descriptive text */
    color: #444;
    line-height: 1.6;
    margin-bottom: 15px;
}

/* Smaller text details */
.card p {
    margin: 5px 0;
    font-size: 1rem; /* Slightly larger details */
    color: #555;
}

/* Action button design */
.btn-custom {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 12px 25px; /* Larger button */
    font-size: 1rem; /* Bigger text */
    font-weight: 600;
    display: inline-block;
    width: 100%; /* Full-width button */
    text-align: center;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.btn-custom:hover {
    background-color: #0056b3;
    transform: translateY(-2px); /* Subtle hover effect */
}

/* Flexbox for better layout */
.row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-evenly; /* Space cards evenly */
}

/* Column adjustments for larger cards */
.col-md-4 {
    flex: 0 0 45%; /* Two cards per row for larger display */
    max-width: 45%;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .col-md-4 {
        flex: 0 0 100%; /* Full width on smaller screens */
        max-width: 100%;
    }
}

/* Consistent font scaling for smaller screens */
@media (max-width: 576px) {
    .card-title {
        font-size: 1.3rem;
    }

    .card-text {
        font-size: 1rem;
    }

    .btn-custom {
        font-size: 0.9rem;
        padding: 10px 20px;
    }
}

    </style>
</head>
<body>
<div class="container my-4">
    

    <?php if ($showAddForm) { ?>
        <h3 class="text-center">Add a New Conference</h3>
        <form action="?v=LIST" method="POST" enctype="multipart/form-data" class="mb-4">
    <input type="hidden" name="add_conference" value="1">
    <div class="mb-3">
        <label for="name" class="form-label">Conference Name:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description:</label>
        <textarea id="description" name="description" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
        <label for="date" class="form-label">Date:</label>
        <input type="date" id="date" name="date" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="number_of_people" class="form-label">Number of People:</label>
        <input type="number" id="number_of_people" name="number_of_people" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="location" class="form-label">Location:</label>
        <input type="text" id="location" name="location" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Upload Image:</label>
        <input type="file" id="image" name="image" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-success w-100">Add Conference</button>
</form>

    <?php } ?>

    <?php if (!empty($allConferences)) { ?>
        <div class="row">
            <?php foreach ($allConferences as $conference) { ?>
                <div class="col-md-4">
                <div class="card">
    <?php if (!empty($conference['image'])) { ?>
        <img src="<?php echo htmlspecialchars($conference['image']); ?>" alt="Conference Image" class="card-img-top">
    <?php } else { ?>
        <img src="default-image.jpg" alt="Default Image" class="card-img-top">
    <?php } ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($conference['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($conference['description']); ?></p>
                            <p><strong>Date:</strong> <?php echo $conference['date']; ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($conference['location']); ?></p>
                            <p><strong>Added By:</strong> <?php echo htmlspecialchars($conference['added_by']); ?></p>
                            <?php if ($type == 'student') { ?>
                                <form action="?v=LIST" method="POST">
                                    <input type="hidden" name="conference_id" value="<?php echo $conference['id']; ?>">
                                    <button type="submit" class="btn btn-custom w-100">Participate</button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ($selectedConferenceId !== null) { ?>
        <h3 class="text-center">Participate in Conference</h3>
        <form action="?v=LIST" method="POST" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" name="conference_id" value="<?php echo $selectedConferenceId; ?>">
            <input type="hidden" name="submit_participation" value="1">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="education" class="form-label">Education:</label>
                <input type="text" id="education" name="education" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="age" class="form-label">Age:</label>
                <input type="number" id="age" name="age" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="job" class="form-label">Job:</label>
                <input type="text" id="job" name="job" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="paper" class="form-label">Upload Paper:</label>
                <input type="file" id="paper" name="paper" class="form-control" accept=".pdf,.doc,.docx">
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit Participation</button>
        </form>
    <?php } ?>
</div>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
