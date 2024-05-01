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
if (isset($_SESSION['StudentID']) && isset($_SESSION['displayName']) && isset($_SESSION['studentName']) && isset($_SESSION['sessionID'])) {
    $StudentID = $_SESSION['StudentID'];
    $displayName = $_SESSION['displayName'];
    $StudentName = $_SESSION['studentName'];
    $sessionID = $_SESSION['sessionID'];

    echo "Student ID : " . $StudentID . "<br>";
    echo "Session ID : " . $sessionID . "<br>";
    echo "Session displayName : " . $displayName . "<br>";
    echo "Session StudentName : " . $StudentName . "<br>";

} else {
    echo "Session code not found.";
    return;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $interactionType = "reply"; // Fixed interaction type for replying to the response
    $reply = $_POST['reply-content'];
    $parentID = $_POST['parentID'];

    // Insert the reply into the interactions table
    $sql = "INSERT INTO interactions (SessionID, ParentID, StudentID, InteractionType, Content, Timestamp) VALUES (?, ?, ?, ?, ?, DEFAULT)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiiss",$sessionID, $parentID, $StudentID, $interactionType, $reply);
        if ($stmt->execute()) {
            header('Location: session.php');
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Close database connection
$conn->close();
?>