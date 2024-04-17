<?php
// Establish database connection
$config = parse_ini_file("../../database/db_config.ini");

$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Retrieve session code from session variable
if (isset($_SESSION['sessionCode']) && isset($_SESSION['session_name']) && isset($_SESSION['SessionID'])) {
    $sessionCode = $_SESSION['sessionCode'];
    $sessionName = $_SESSION['session_name'];
    $sessionID = $_SESSION['SessionID'];
    echo "Session Code : ".$sessionCode."<br>";
    echo "Session Name : ".$sessionName."<br>";
    echo "Session ID : ".$sessionID."<br>";

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
        $sql = "INSERT INTO prompts (Prompt) VALUES (?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters and execute statement
            $stmt->bind_param("s", $questionText);
            if ($stmt->execute()) {
                $lastInsertedId = $stmt->insert_id;
                $_SESSION['PromptID'] = $lastInsertedId;
                $timeStamp = date("Y-m-d H:i:s");
                $sql2 = "INSERT INTO interactions (SessionID, PromptID, StudentID, InteractionType, Content, Timestamp) VALUES (?, ?, -1, 'Question', ?, ?)";
                $stmt2 = $conn->prepare($sql2);
                if ($stmt2) {
                    $stmt2->bind_param("iiss", $sessionID, $lastInsertedId, $questionText, $timeStamp);
                    if ($stmt2->execute()) {
                        echo "Question added successfully.";
                        header("Location: interactions.php");
                        exit; // Make sure to exit after redirection
                    } else {
                        echo "Error: " . $sql2 . "<br>" . $conn->error;
                    }
                } else {
                    echo "Error: " . $sql2 . "<br>" . $conn->error;
                }
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Error: Unable to prepare statement.";
        }

        // Close statement
        $stmt->close();
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
