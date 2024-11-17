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
    $addedBy = mysqli_real_escape_string($dbConn, $_SESSION['calendar_fd_user_name']); // Fetch the username

    $sql = "INSERT INTO conferences (name, description, date, number_of_people, location, added_by) 
            VALUES ('$name', '$description', '$date', $number_of_people, '$location', '$addedBy')";

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
    <title>Event Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #007bff;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
        }
        form div {
            margin-bottom: 15px;
        }
        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        form input, form textarea, form button {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        form button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Event Management</h2>

    <?php if ($showAddForm) { ?>
        <h3>Add a New Conference</h3>
        <form action="eventlist.php" method="POST">
            <input type="hidden" name="add_conference" value="1">
            <div>
                <label for="name">Conference Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div>
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div>
                <label for="number_of_people">Number of People:</label>
                <input type="number" id="number_of_people" name="number_of_people" required>
            </div>
            <div>
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>
            <button type="submit">Add Conference</button>
        </form>
    <?php } ?>

    <?php if (!empty($allConferences)) { ?>
        <h3>All Conferences</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Number of People</th>
                    <th>Location</th>
                    <th>Added By</th> <!-- New Column -->
                    <?php if ($type == 'student') { ?>
                        <th>Action</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allConferences as $conference) { ?>
                    <tr>
                        <td><?php echo $conference['id']; ?></td>
                        <td><?php echo htmlspecialchars($conference['name']); ?></td>
                        <td><?php echo htmlspecialchars($conference['description']); ?></td>
                        <td><?php echo $conference['date']; ?></td>
                        <td><?php echo $conference['number_of_people']; ?></td>
                        <td><?php echo htmlspecialchars($conference['location']); ?></td>
                        <td><?php echo htmlspecialchars($conference['added_by']); ?></td> <!-- Display Username -->
                        <?php if ($type == 'student') { ?>
                            <td>
                                <form action="eventlist.php" method="POST">
                                    <input type="hidden" name="conference_id" value="<?php echo $conference['id']; ?>">
                                    <button type="submit">Participate</button>
                                </form>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <?php if ($selectedConferenceId !== null) { ?>
        <h3>Participate in Conference</h3>
        <form action="eventlist.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="conference_id" value="<?php echo $selectedConferenceId; ?>">
            <input type="hidden" name="submit_participation" value="1">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="education">Education:</label>
                <input type="text" id="education" name="education" required>
            </div>
            <div>
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" required>
            </div>
            <div>
                <label for="job">Job:</label>
                <input type="text" id="job" name="job" required>
            </div>
            <div>
                <label for="paper">Upload Paper:</label>
                <input type="file" id="paper" name="paper" accept=".pdf,.doc,.docx">
            </div>
            <button type="submit">Submit Participation</button>
        </form>
    <?php } ?>
</div>
</body>
</html>
