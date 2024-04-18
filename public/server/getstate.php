<?php
// Establish database connection
$config = parse_ini_file("../../../database/db_config.ini");
$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

session_start();

// Retrieve session code from session variable
if (isset($_SESSION['sessionCode']) && isset($_SESSION['sessionID'])) {
	$sessionCode = $_SESSION['sessionCode'];
	$sessionID = $_SESSION['sessionID'];
} else {
	echo "Session code not found.";
}

// Get array of all interactions within current session
$sql="SELECT interactions.InteractionID, interactions.ParentID, students.DisplayName, interactions.InteractionType, interactions.Content FROM interactions INNER JOIN students ON students.StudentID = interactions.StudentID WHERE interactions.SessionID=? AND interactions.InteractionType <> 'Question'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $sessionID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_all(MYSQLI_ASSOC);
$result->free_result();
$conn->close();
echo json_encode($row);

?>