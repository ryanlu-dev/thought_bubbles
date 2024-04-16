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

// Retrieve session code from URL parameter
if (isset($_GET['code']) && isset($_GET['name'])) {
    $sessionCode = $_GET['code'];
    $sessionName = $_GET['name'];
    $_SESSION['sessionCode'] = $sessionCode; // Store the session code in a session variable
    $_SESSION['sessionName'] = $sessionName; // Store the session name in a session variable
    echo $sessionCode;
    echo $sessionName;
} else {
    echo "Session code not found.";
}


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $questionText = $_POST['question_text'];

    // Validate form data (add more validation as needed)
    if (empty($questionText)) {
        echo "All fields are required. Please fill them.";
    } else {
        // Insert the question into the database
        $sql = "INSERT INTO prompts (Prompt) VALUES ('$questionText')";

        if ($conn->query($sql) === TRUE) {
            echo "Question added successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
</head>
<body>
    <h1>Create Free Response Question</h1>
    <!-- HTML form for creating a free response question -->
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="question_type" value="free_response">
        <label for="question_text">Question Text:</label><br>
        <textarea id="question_text" name="question_text" rows="4" cols="50" required></textarea><br>
        <button type="submit">Add Free Response Question</button>
    </form>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
