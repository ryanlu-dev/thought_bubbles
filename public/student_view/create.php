<?php
// Start PHP session
session_start();

// Establish database connection
$config = parse_ini_file("../../../database/db_config.ini");
$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

if (isset($_SESSION['sessionCode'])) {
	$sessionCode = $_SESSION['sessionCode'];
	echo "Session Code : ".$sessionCode."<br>";
} else {
	echo "Session code not found.";
}

if (isset($_SESSION['sessionID'])) {
	$sessionID = $_SESSION['sessionID'];
	echo "Session Code : ".$sessionID."<br>";
} else {
	echo "Session ID not found ";
}


// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Check if name and nickname are provided
	if(isset($_POST['studentName']) && isset($_POST['displayName'])) {
		// Retrieve submitted data
		$studentName = $_POST['studentName'];
		$displayName = $_POST['displayName'];
		
		// Insert the student data into the database
		$sql = "INSERT INTO students (StudentName, DisplayName) VALUES ('$studentName', '$displayName')";
		
		if ($conn->query($sql) === TRUE) {
			$specificIdResult = $conn->query("SELECT StudentID FROM students WHERE StudentName = '$studentName' AND DisplayName = '$displayName'");
			$specificIdRow = $specificIdResult->fetch_assoc();
			$specificId = $specificIdRow['StudentID'];
			$_SESSION['StudentID'] = $specificId;
			$_SESSION['sessionID'] = $sessionID;
			$_SESSION['sessionCode'] = $sessionCode;
			$_SESSION['studentName'] = $studentName;
			$_SESSION['displayName'] = $displayName;
			echo "New record created successfully";
			header('Location: session.php');
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	} else {
		// Handle case where name or nickname is missing
		echo "Please enter both name and nickname.";
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous"/>
	<link rel="stylesheet" href="style.css" />
	<title>Thought Bubbles</title>
</head>
<body>
	<div class="container py-5 h-100">
		<div class="row d-flex justify-content-center align-items-center h-100">
			<div class="col-12 col-md-8 col-lg-6 col-xl-5">
				<div class="card" style="border-radius: 1rem">
					<div class="card-body p-5 text-center">
						<div class="row mb-3 justify-content-md-center">
							<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
								<path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
								<path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
							</svg>
						</div>
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
							<div class="row mb-3">
								<div class="input-group">
									<div class="input-group-text">@</div>
										<input type="text" class="form-control" placeholder="Screen Name" name="displayName"/>
									</div>
									<p><small class="text-secondary">This will show up on the screen</small></p>
								</div>
								<div class="row mb-3">
									<div class="input-group">
										<input type="text" class="form-control" placeholder="Student Name" name="studentName"/>
									</div>
								</div>	
								<button type="submit" href="waitingroom.html" class="btn btn-primary">Next</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
