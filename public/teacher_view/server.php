<?php
session_start();

// Create connection
$config = parse_ini_file("../../../database/db_config.ini");

$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['session_name'])) {
	$sessionName = $_POST['session_name'];
} else {
	$sessionName = "";
}

// Function to generate a random session ID (code)
function generateSessionCode($length = 8) {
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

// Generate a unique session ID
$sessionCode = generateSessionCode();

// Insert the session ID into the database
$sql = "INSERT INTO sessions (Timestamp, SessionCode) VALUES (DEFAULT, '$sessionCode')";

if ($conn->query($sql) === TRUE) {
	// Store the generated session code in a session variable
	$_SESSION['sessionCode'] = $sessionCode;
	$_SESSION['session_name'] = $sessionName;
	$lastInsertedId = $conn->insert_id;
	$_SESSION['sessionID'] = $lastInsertedId;
	// Close database connection
	$conn->close();

	// Redirect to the admin page
	header("Location: admin.php");
	exit;
} else {
	echo "Error: " . $sql . "<br>" . $conn->error;
	$conn->close();
}
?>
