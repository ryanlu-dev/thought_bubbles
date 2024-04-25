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
	// Retrieve the (latest) question for the session from the database	
} else {
	echo "Session code not found.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Retrieve form data
	$interactionType = "message"; // Fixed interaction type for answering the question
	$answer = $_POST['answer'];
	$pre = "SELECT InteractionID FROM interactions WHERE SessionID = ? AND InteractionType = 'Question' ORDER BY Timestamp DESC LIMIT 1";
	$prestmt = $conn->prepare($pre);
	$PromptID = -1;
	if($prestmt) {
		$prestmt->bind_param("i", $sessionID);
		if($prestmt->execute()) {
			$res = $prestmt->get_result();
			if($res->num_rows > 0) {
				$row = $res->fetch_assoc();
				$PromptID = $row['InteractionID'];
			} else {
				echo "Cannot find interaction";
			}
		} else {
			echo "Error executing query: " . $stmt->error;
		}
		$prestmt->close();
	} else {
		echo "Error preparing statement: " . $conn->error;
	}
	// Insert the answer into the interactions table
	$sql = "INSERT INTO interactions (SessionID, ParentID, StudentID, InteractionType, Content, Timestamp) VALUES (?, ?, ?, ?, ?, DEFAULT)";
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

<html>
<div id="questionSection">
	
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
<!-- Form for answering the question -->
<div id="mainContent">
<form method="post">
<input type="hidden" name="sessionID" value="<?php echo $sessionID; ?>">
<input type="hidden" name="interactionType" value="message">
<label for="answer">Your Answer:</label><br>
<textarea id="answer" name="answer" rows="4" cols="50" required></textarea><br>
<button type="submit">Submit Answer</button>
</form>

<div id="responseArea">
	
</div>
</div>
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
function getLatestQuestion() {
    $.ajax({
        type: "GET",
        url: "../server/getLatestQuestion.php",
        success: function (response) {
            $("#questionSection").html(response);
        }
    });
}

getMsg();
getLatestQuestion();

var intervalID = window.setInterval(getMsg, 2500);
var intervalID = window.setInterval(getLatestQuestion, 2500);

</script>
</html>