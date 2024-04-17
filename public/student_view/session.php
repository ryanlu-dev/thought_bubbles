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
if (isset($_SESSION['StudentID']) && isset($_SESSION['SessionID'])) {
    $StudentID = $_SESSION['StudentID'];
    $sessionID = $_SESSION['SessionID'];
    echo "Student ID : " . $StudentID . "<br>";
    echo "Session ID : " . $sessionID . "<br>";

    // Retrieve the question for the session from the database
    $sql = "SELECT Content FROM interactions WHERE SessionID = ? AND InteractionType = 'Question' ORDER BY Timestamp DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $sessionID);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $question = $row['Content'];
                echo "<h2>Question:</h2>";
                echo "<p>" . $question . "</p>";
            } else {
                echo "No question available for this session.";
            }
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Session code not found.";
}

$sql2 = "SELECT PromptID FROM interactions WHERE SessionID = ? AND InteractionType = 'Question' ORDER BY Timestamp DESC LIMIT 1";
$stmt2 = $conn->prepare($sql2);
if ($stmt2) {
    $stmt2->bind_param("i", $sessionID);
    if ($stmt2->execute()) {
        $result2 = $stmt2->get_result();
        if ($result2->num_rows > 0) {
            $row2 = $result2->fetch_assoc();
            $PromptID = $row2['PromptID'];
        } else {
            echo "No PromptID available for this session.";
        }
    } else {
        echo "Error executing query: " . $stmt2->error;
    }
    $stmt2->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $interactionType = "message"; // Fixed interaction type for answering the question
    $answer = $_POST['answer'];

    // Insert the answer into the interactions table
    $sql = "INSERT INTO interactions (SessionID, PromptID, StudentID, InteractionType, Content, Timestamp) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiiss",$sessionID, $PromptID, $StudentID, $interactionType, $answer);
        if ($stmt->execute()) {
            echo "Answer submitted successfully.";
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

<!-- Form for answering the question -->
<form method="post">
        <input type="hidden" name="sessionID" value="<?php echo $sessionID; ?>">
        <input type="hidden" name="interactionType" value="message">
        <label for="answer">Your Answer:</label><br>
        <textarea id="answer" name="answer" rows="4" cols="50" required></textarea><br>
        <button type="submit">Submit Answer</button>
</form>
