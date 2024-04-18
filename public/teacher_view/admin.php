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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Retrieve form data
	$questionText = $_POST['question_text'];

	// Validate form data (add more validation as needed)
	if (empty($questionText)) {
		echo "All fields are required. Please fill them.";
	} else {
		// Insert the question into the database
		$sql = "INSERT INTO interactions VALUES (DEFAULT, -1, ?, -1, 'Question', ?, DEFAULT)";
		$stmt = $conn->prepare($sql);

		if ($stmt) {
			// Bind parameters and execute statement
			$stmt->bind_param("is", $sessionID, $questionText);
			$stmt->execute();
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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
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
	<div class="container" id="responseArea">

	</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<script>
function getMsg() {
	$.ajax({
		type: "GET",
		url: "../server/getstate.php",
		success: function (response) {
			response = JSON.parse(response);
			var html = "";
			if(response.length) {
				$.each(response, function(key, value) {
					html += "<div class='col-lg-2 col-md-3 col-6' style='margin-bottom: 10px'>";
						html += "<div class='card mb-3'>";
							html += "<div class='card-body'>";
								html += "<p class='text-center'><b>" + value.DisplayName + "</b></p>";
								html += "<p class='text-center'>" + value.Content + "</p>";
								html += "<div class='row'>";
									html += "<div class='col'>";
										html += "<div class='d-grid gap-2 d-md-block justify-content-md-start'>";
											html += "<button class='btn btn-primary' type='button'>Reply</button>";
										html += "</div>";
									html += "</div>";
									html += "<div class='col'>";
										html += "<student-reaction></student-reaction>";
									html += "</div>"
								html += "</div>"
							html += "</div>"
						html += "</div>"
					html += "</div>"
				});
			} else {
				html += '<div class="alert alert-warning">';
				html += 'No records found!';
				html += '</div>';
			}
			$("#responseArea").html(html);
		}
	});
}

getMsg();

var intervalID = window.setInterval(getMsg, 2500);
</script>
</html>

<?php
// Close database connection
$conn->close();
?>
