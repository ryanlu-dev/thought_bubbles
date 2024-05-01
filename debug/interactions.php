<?php
// Establish database connection
$config = parse_ini_file("../../database/db_config.ini");

$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

session_start();

if (isset($_SESSION['sessionCode']) && isset($_SESSION['session_name']) && isset($_SESSION['PromptID']) && isset($_SESSION['SessionID'])) {
	$sessionCode = $_SESSION['sessionCode'];
	$sessionName = $_SESSION['session_name'];
	$PromptIDValue = $_SESSION['PromptID'];
	$SessionID = $_SESSION['SessionID'];
	
	echo "Session Code : ".$sessionCode."<br>";
	echo "Session Name : ".$sessionName."<br>";
	echo "Prompt ID : ".$PromptIDValue."<br>";
	echo "Session ID : ".$SessionID."<br>";
	
} else {
	echo "Session code not found.";
}
$sql = "SELECT Prompt FROM prompts WHERE PromptID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $PromptIDValue);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
	// Output data of each row
	while ($row = $result->fetch_assoc()) {
		$questionText = $row["Prompt"];
	}
} else {
	$questionText = "No questions available.";
}

$messages = array(); // Array to store messages
$sql_messages = "SELECT Content FROM interactions WHERE SessionID = ? AND InteractionType = 'message' ORDER BY Timestamp DESC";
$stmt_messages = $conn->prepare($sql_messages);
$stmt_messages->bind_param("i", $SessionID);
$stmt_messages->execute();
$result_messages = $stmt_messages->get_result();

if ($result_messages->num_rows > 0) {
	while ($row_messages = $result_messages->fetch_assoc()) {
		$messages[] = $row_messages['Content']; // Store each message in the array
	}
}

// Close the database connection
$stmt->close();
$stmt_messages->close();
$conn->close();

if (!empty($messages)) {
	foreach ($messages as $message) {
		echo "<li>$message</li>";
	}
} else {
	echo "<p>No messages available for this session.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Question and Interactions</title>
</head>
<body>
<h1>Question</h1>
<p><?php echo $questionText; ?></p>

<h2>Messages</h2>
<ul id="messagesList">
<?php foreach ($messages as $message) { ?>
	<li><?php echo $message; ?></li>
	<?php } ?>
	</ul>
	</body>
	</html>
	