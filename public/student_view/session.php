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
} else {
	echo "Session code not found.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// Retrieve form data
	$interactionType = "message"; // Fixed interaction type for answering the question
	$answer = $_POST['answer'];
  // Retrieve the (latest) question for the session from the database	
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
			// echo "Answer submitted successfully.";
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
<link rel="stylesheet" href="../style.css">
<div class='container-fluid'>
		<div class="card text-center">
			<div class="card-header">
				Current Discussion Question
			</div>
			<div class="card-body">
				<h1 class="card-title" id="qtitle">Waiting...</h5>
				<!-- <p class="card-text">[extra clarification (optional)]</p> -->
			</div>
			<div class="card-footer text-muted">
			</div>
		</div>
	</div>
<!-- Form for answering the question -->
<div id="mainContent">
<form method="post">
<input type="hidden" name="sessionID" value="<?php echo $sessionID; ?>">
<input type="hidden" name="interactionType" value="message">
<label for="answer">Your Answer:</label><br>
<textarea id="answer" name="answer" rows="4" cols="50" required></textarea><br>
<button type="submit">Submit Answer</button>
</form>

<div id="replyOverlay" class="overlay-div overflow-hidden d-none"></div>
<div id="replyBox" class="card container overlay-box d-none p-0">
	<div class="card-header">
		<button type="button" class="close" aria-label="Close" onclick="closeReplyBox()">
			<span aria-hidden="true">&times;</span>
		</button>
		<h5 id="content-to-reply" class="card-title text-center font-weight-normal m-2"></h5>
	</div>
	<form method="POST" action="post_reply.php" id="post-reply-form">
		<input type="hidden" name="sessionID" value="<?php echo $sessionID; ?>">
		<input type="hidden" name="StudentID" value="<?php echo $StudentID; ?>">
		<input type="hidden" name="studentName" value="<?php echo $studentName; ?>">
		<input type="hidden" name="displayName" value="<?php echo $displayName; ?>">
		<input type="hidden" name="interactionType" value="reply">
		<div class="card-body form-group">
			<textarea class="form-control" id="reply-textbox" rows="3" name="reply-content" required></textarea>
		</div>
		<div class="text-center">
			<button class="btn btn-primary" type="submit">Submit Reply</button>
		</div>
	</form>
</div>

<div id="responseArea">
	
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<script>
function closeReplyBox() {
	$('#replyOverlay').addClass("d-none");
	$('#replyBox').addClass("d-none");
}

function openReplyBox(author, content, interactionID) {
	$('#replyOverlay').removeClass("d-none");
	$('#replyBox').removeClass("d-none");
	var html = "Reply to " + "<em>" + author + "</em>" + "'\s response: " + "\"" + "<span class='font-weight-bold'>" + content + "</span>" + "\"";
	var formInput = "<input type='hidden' name='parentID' value='" + interactionID + "'>";
	$('#content-to-reply').html(html);
	$('#post-reply-form').prepend(formInput);
}

function getQuestion() {
	$.ajax({
		type: "GET",
		url: "../server/getquestion.php",
		success: function (response) {
			response = JSON.parse(response);
			var html = "";
			if(response) {
				html += response;
			}
			$("#qtitle").html(html);
		}
	});
}

function displayCard(value) {
	var html = "";
	html += "<div class='col-lg-2 col-md-3 col-6' style='margin-bottom: 10px'>";
				html += "<div class='card mb-3 mt-3' id='msg" + value.InteractionID + "'>";
					html += "<div class='card-body'>";
						html += "<p class='text-center'><b>" + value.DisplayName + "</b></p>";
						html += "<p class='text-center'>" + value.Content + "</p>";
						html += "<div class='row'>";
							html += "<div class='col'>";
								html += "<div class='d-grid gap-2 d-md-block justify-content-md-start'>";
									html += "<button class='btn btn-primary' type='button' onclick='openReplyBox(\"" + value.DisplayName + "\", \"" + value.Content + "\", \"" + value.InteractionID + "\")'>Reply</button>";
								html += "</div>";
							html += "</div>";
							html += "<div class='col'>";
								html += "<student-reaction></student-reaction>";
							html += "</div>";
						html += "</div>";
						for (let i = 0; i < value.replies.length; i++) {
							var reply = value.replies[i];
							html += displayCard(reply);
						}
					html += "</div>";
				html += "</div>";
			html += "</div>";
	return html;
}

function getMsg() {
	$.ajax({
		type: "GET",
		url: "../server/getstate.php",
		success: function (response) {
			response = JSON.parse(response);
			var html = "";
			if(response.length) {
				$.each(response, function(key, value) {
					html += displayCard(value);
				});
			} else {
				html += '<div class="alert alert-warning">';
				html += 'No messages yet!';
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

getQuestion();
getMsg();
getLatestQuestion();

var q = window.setInterval(getQuestion, 2500);
var intervalID = window.setInterval(getMsg, 2500);
var intervalID2 = window.setInterval(getLatestQuestion, 2500);


</script>
</html>