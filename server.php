<?php
session_start();
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = "temppassword"; // Replace with your MySQL password
$dbname = "cis4930project";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['session_name'])) {
    $sessionName = $_POST['session_name'];
} else {
    $sessionName = ""; // Set default value if session name is not provided
}

// Function to generate a random session ID (code)
function generateSessionId($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Generate a unique session ID
$sessionCode = generateSessionId();

//Generate Time Stamp;
$timeStamp = date("Y-m-d H:i:s");

// Insert the session ID into the database
$sql = "INSERT INTO sessions (Timestamp, SessionCode) VALUES ('$timeStamp', '$sessionCode')";

if ($conn->query($sql) === TRUE) {
    // Store the generated session code in a session variable
    $_SESSION['sessionCode'] = $sessionCode;
    $_SESSION['session_name'] = $sessionName;
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
