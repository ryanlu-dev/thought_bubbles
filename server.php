<?php
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = "Jul-1759"; // Replace with your MySQL password
$dbname = "cis4930project";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

if (isset($_GET['code']) && isset($_POST['session_code'])) {
    $sessionCode = $_GET['code'];
    $sessionName = $_POST['session_code'];
    $_SESSION['sessionCode'] = $sessionCode; // Store the session code in a session variable
    $_SESSION['session_name'] = $sessionName; // Store the session name in a session variable
    echo $sessionCode;
} else {
    echo "Session code or session name not found.";
}

// Generate a unique session ID
$sessionCode = generateSessionId();

//Generate Time Stamp;
$timeStamp = date("Y-m-d H:i:s");

// Insert the session ID into the database
$sql = "INSERT INTO sessions (Timestamp, SessionCode) VALUES ('$timeStamp', '$sessionCode')";

if ($conn->query($sql) === TRUE) {
    // Store the generated session code in a session variable
    session_start();
    $_SESSION['sessionCode'] = $sessionCode;
    $_SESSION['sessionName'] = $sessionName;
    // Close database connection
    $conn->close();

    // Redirect to the admin page
    header("Location: admin.php?code=$sessionCode&name=$sessionName");
    exit;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
    $conn->close();
}
?>
