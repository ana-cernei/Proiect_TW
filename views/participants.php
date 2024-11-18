<?php
require_once '../library/config.php';

// Ensure the user is a teacher
$type = $_SESSION['calendar_fd_user']['type'] ?? '';
if ($type != 'teacher') {
    die("Access denied. Only teachers can view this page.");
}

// Get the logged-in user's name
$loggedInUser = $_SESSION['calendar_fd_user_name'] ?? '';

if (empty($loggedInUser)) {
    die("Unable to identify the logged-in user.");
}

// Establish the database connection
$dbConn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

// Check the database connection
if (!$dbConn) {
    die("MySQL connection failed: " . mysqli_connect_error());
}

// Fetch participants for conferences added by the logged-in user
$sql = "
    SELECT 
        p.id AS participant_id,
        p.name AS participant_name,
        p.education,
        p.age,
        p.job,
        p.paper,
        c.name AS conference_name,
        c.date AS conference_date,
        p.added_by AS organizer
    FROM participants p
    INNER JOIN conferences c ON p.conference_id = c.id
    WHERE c.added_by = '$loggedInUser'
";

$result = mysqli_query($dbConn, $sql);
$participants = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $participants[] = $row;
    }
    mysqli_free_result($result);
}
mysqli_close($dbConn);
?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Participants for Conferences Organized by You</h3>
    </div>
    <div class="box-body">
        <?php if (!empty($participants)) { ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Participant Name</th>
                        <th>Education</th>
                        <th>Age</th>
                        <th>Job</th>
                        <th>Uploaded Paper</th>
                        <th>Conference Name</th>
                        <th>Conference Date</th>
                        <th>Organizer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant) { ?>
                        <tr>
                            <td><?php echo $participant['participant_id']; ?></td>
                            <td><?php echo htmlspecialchars($participant['participant_name']); ?></td>
                            <td><?php echo htmlspecialchars($participant['education']); ?></td>
                            <td><?php echo $participant['age']; ?></td>
                            <td><?php echo htmlspecialchars($participant['job']); ?></td>
                            <td>
                            <?php 
                            if ($participant['paper']) { 
                                $filePath = $participant['paper'];
                                echo "<a href='$filePath' target='_blank'>View Paper</a>";
                            } else { 
                                echo "No paper uploaded"; 
                            } 
                            ?>
                            </td>
                            <td><?php echo htmlspecialchars($participant['conference_name']); ?></td>
                            <td><?php echo $participant['conference_date']; ?></td>
                            <td><?php echo htmlspecialchars($participant['organizer']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No participants found for conferences organized by you.</p>
        <?php } ?>
    </div>
</div>
