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
if (isset($_SESSION['sessionCode']) && isset($_SESSION['session_name']) && isset($_SESSION['sessionID'])) {
	$sessionCode = $_SESSION['sessionCode'];
	$sessionName = $_SESSION['session_name'];
	$sessionID = $_SESSION['sessionID'];
	echo "Session Code : ".$sessionCode."<br>";
	echo "Session Name : ".$sessionName."<br>";
	echo "Session ID : ".$sessionID."<br>";

} else {
	echo "Session code not found.";
}

$sql="SELECT interactions.InteractionID, interactions.ParentID, students.DisplayName, interactions.InteractionType, interactions.Content FROM interactions INNER JOIN students ON students.StudentID = interactions.StudentID WHERE interactions.SessionID=? AND interactions.InteractionType <> 'Question'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $sessionID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_all(MYSQLI_ASSOC);
$result->free_result();
$conn->close();

?>
<html>
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Summary</title>
    
    <!-- Browser Tab Icons -->
    <link rel="icon" type="image/png" rel="noopener" target="_blank" href="../resources/img/icons/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" rel="noopener" target="_blank" href="../resources/img/icons/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" rel="noopener" target="_blank" href="../resources/img/icons/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" rel="noopener" target="_blank" href="../resources/img/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" rel="noopener" target="_blank" href="../resources/img/icons/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" rel="noopener" target="_blank" href="../resources/img/icons/android-chrome-512x512.png">
</head> 
<body>
    <div class="container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">StudentID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Messages</th>
                    <th scope="col">Gave reacts</th>
                    <th scope="col">Received reacts</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php 

                ?>
            </tbody>
        </table>
    </div>
    <script>

    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>