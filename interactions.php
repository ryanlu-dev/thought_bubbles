<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "Jul-1759";
$dbname = "cis4930project";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

if (isset($_SESSION['sessionCode']) && isset($_SESSION['session_name']) && isset($_SESSION['PromptID'])) {
    $sessionCode = $_SESSION['sessionCode'];
    $sessionName = $_SESSION['session_name'];
    $PromptIDValue = $_SESSION['PromptID'];

    echo "Session Code : ".$sessionCode."<br>";
    echo "Session Name : ".$sessionName."<br>";
    echo "Session Name : ".$PromptIDValue."<br>";

} else {
    echo "Session code not found.";
}

// Retrieve a question from the database
$sql = "SELECT Prompt FROM prompts WHERE PromptID = '$PromptIDValue'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $questionText = $row["Prompt"];
    }
} else {
    $questionText = "No questions available.";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question</title>
</head>
<body>
    <h1>Question</h1>
    <p><?php echo $questionText; ?></p>
</body>
</html>
