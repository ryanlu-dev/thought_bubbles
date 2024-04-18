<?php
session_start();

// Establish database connection
$config = parse_ini_file("../../database/db_config.ini");

$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve submitted session code
    $submittedCode = $_POST['sessionCode'];

    // Query the database to check if the session code exists and fetch the sessionID
    $sql = "SELECT SessionID FROM sessions WHERE SessionCode = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $submittedCode);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Session code exists, fetch sessionID
                $row = $result->fetch_assoc();
                $sessionID = $row['SessionID'];

                // Redirect user to join the session
                $_SESSION['sessionCode'] = $submittedCode;
                $_SESSION['sessionID'] = $sessionID;
                header("Location: student_view/create.php");
                exit;
            } else {
                // Session code doesn't exist, show an error message
                echo "Invalid session code. Please try again.";
                header("Location: joinroomclient.php");
                exit;
            }
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous" />
    <title>Enter Session Code</title>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Enter Session Code</h5>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="form-group">
                                <label for="sessionCode">Session Code</label>
                                <input type="text" class="form-control" id="sessionCode" name="sessionCode" placeholder="Enter session code">
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
