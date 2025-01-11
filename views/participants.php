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

// Handle Accept/Decline actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['participant_id'])) {
    $participantId = (int)$_POST['participant_id'];
    $action = $_POST['action'] === 'accept' ? 'Accepted' : 'Declined';

    $updateSql = "UPDATE participants SET status = '$action' WHERE id = $participantId";
    if (mysqli_query($dbConn, $updateSql)) {
        echo "<script>alert('Participant status updated successfully.');</script>";
    } else {
        echo "<p style='color: red;'>Error updating status: " . mysqli_error($dbConn) . "</p>";
    }
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
        p.added_by AS organizer,
        p.status
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
        <h3 class="box-title">Requests for Participating in the Conferences Organized by You</h3>
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
                        <th>Status</th>
                        <th>Actions</th>
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
                            <td><?php echo $participant['status']; ?></td>
                            <td>
                                <?php if ($participant['status'] === 'Pending') { ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="participant_id" value="<?php echo $participant['participant_id']; ?>">
                                        <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="participant_id" value="<?php echo $participant['participant_id']; ?>">
                                        <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm">Decline</button>
                                    </form>
                                <?php } else {
                                    echo $participant['status'];
                                } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No participants found for conferences organized by you.</p>
        <?php } ?>
    </div>
</div>
