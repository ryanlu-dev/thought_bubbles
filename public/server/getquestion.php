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

// Retrieve the (latest) question for the session from the database
	$sql = "SELECT Content, InteractionID FROM interactions WHERE SessionID = ? AND InteractionType = 'Question' ORDER BY Timestamp DESC LIMIT 1";
	$stmt = $conn->prepare($sql);
	if ($stmt) {
		$stmt->bind_param("i", $sessionID);
		if ($stmt->execute()) {
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$question = $row['Content'];
				$_SESSION['qid'] = $row['InteractionID'];
				echo json_encode($question);
			} else {
				
			}
		} else {
			echo "Error executing query: " . $stmt->error;
		}
		$stmt->close();
	} else {
		echo "Error preparing statement: " . $conn->error;
	}
?>