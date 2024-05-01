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

function getReplies($sessionID, $parentID) {
	global $conn;

	$replySql="SELECT interactions.InteractionID, interactions.ParentID, students.DisplayName, interactions.InteractionType, interactions.Content FROM interactions INNER JOIN students ON students.StudentID = interactions.StudentID WHERE interactions.SessionID=? AND interactions.InteractionType = 'reply' AND ParentID = ?";
	$replyStmt = $conn->prepare($replySql);
    $replyStmt->bind_param('ii', $sessionID, $parentID);
    $replyStmt->execute();
    $replyResult = $replyStmt->get_result();
    $replies = $replyResult->fetch_all(MYSQLI_ASSOC);

	$replyResult->free_result();

	foreach ($replies as &$reply) {
		$reply['replies'] = getReplies($sessionID, $reply['InteractionID']);
	}

	return $replies;
}

// Get array of all interactions within current session
$sql="SELECT interactions.InteractionID, interactions.ParentID, students.DisplayName, interactions.InteractionType, interactions.Content FROM interactions INNER JOIN students ON students.StudentID = interactions.StudentID WHERE interactions.SessionID=? AND interactions.InteractionType ='message'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $sessionID);
$stmt->execute();
$result = $stmt->get_result();
$rows = $result->fetch_all(MYSQLI_ASSOC);
$result->free_result();
foreach ($rows as &$row) {
    $parentID = $row['InteractionID'];

	$row['replies'] = getReplies($sessionID, $parentID);
}

echo json_encode($rows);
$conn->close();

?>