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
	//echo "Session Code : ".$sessionCode."<br>";
	//echo "Session Name : ".$sessionName."<br>";
	//echo "Session ID : ".$sessionID."<br>";

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
		$sql = "INSERT INTO interactions VALUES (DEFAULT, -1, ?, -1, 'question', ?, DEFAULT)";
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
	<div class="container">
	<div class="row justify-content-md-center">
		<div class="col-md-auto">
		<div class="col text-center">
			<div>
				<p class="text-center display-4">
				Session Code : <?=$sessionCode;?><br>
				Session Name : <?=$sessionName;?><br>
				<!-- Session ID : <?=$sessionID;?><br> -->
				</p>
			</div>
		</div>
</div>
	</div>
	</div>
	<div class="maintitle text-center"> 
	<h1>Create Free Response Question</h1>
	<!-- HTML form for creating a free response question -->
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<input type="hidden" name="question_type" value="free_response">
		<!-- <label for="question_text">Question Text:</label><br> -->
		<textarea id="question_text" name="question_text" rows="4" cols="50" required></textarea><br>
		<button class="btn btn-primary my-3" type="submit">Add Free Response Question</button>
	</form>
	<!-- <a class="btn btn-primary" href="summary.php" role="button" id="finishSession">Finish session</a> -->
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
				<a href="#" class="btn btn-secondary">Add Another Question</a>
				<a href="summary.php" class="btn btn-primary">Submit All Questions</a>
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div id="responseArea">
		</div>
	</div>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<script>
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
        html += "<div class='col-sm mb-3' style='margin-bottom: 10px'>";
        html += "<div class='card mb-3'  id='msg" + value.InteractionID + "'>";
        html += "<div class='card-body'>";
        html += "<p class='text-center'><b>" + value.DisplayName + "</b></p>";
        html += "<p class='text-center'>" + value.Content + "</p>";
        html += "<div class='row'>";
        html += "<div class='col'>";
        //html += "<div class='d-grid gap-2 d-md-block justify-content-md-start'>";
        //html += "<button class='btn btn-primary' type='button' onclick='openReplyBox(\"" + value.DisplayName + "\", \"" + value.Content + "\", \"" + value.InteractionID + "\")'>Reply</button><br><br>";
        //html += "</div>";
        html += "</div>";
        html += "<div class='col>";
        html += "<div class='justify-content-md-end'>";
        html += "<form method='POST' action='post_likes.php'>";
				/*
        html += "<input type='hidden' name='sessionID' value='<?php echo $sessionID; ?>'>";
        html += "<input type='hidden' name='StudentID' value='<?php echo $StudentID; ?>'>";
        html += "<input type='hidden' name='studentName' value='<?php echo $StudentName; ?>'>";
        html += "<input type='hidden' name='displayName' value='<?php echo $displayName; ?>'>";
        html += "<input type='hidden' name='parentID' value='" + value.InteractionID + "'>";
        html += "<input type='hidden' name='interactionType' value='reaction'>";
				*/
        html += "<img src = '../../resources/reactions/";
		if (value.isLiked == 0) {
			html += "heart.svg'";
		}
		else {
			html += "heart-fill.svg'";
		}
		html += " alt = 'heart'/></button>";
        html += value.isLiked;
        html += "</form>";
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
                var html = "<div class = 'container-fluid'>";
                var count = 0;
                if(response.length) {
                    $.each(response, function(key, value) {
                        count+=1;
                        if(count === 2){
                            html += displayCard(value);
                            html+="</div>";
                            count = 0;
                        }
                        else {
                            html += "<div class='row''>";
                            html += displayCard(value);

                        }
                    });
                } else {
                    html += '<div class="alert alert-warning my-4">';
                    html += 'No messages yet!';
                    html += '</div>';
                }
                html+= "</div>";
                $("#responseArea").html(html);
            }
        });
    }

getQuestion();
getMsg();

var q = window.setInterval(getQuestion, 500);
var intervalID = window.setInterval(getMsg, 500);
</script>
</html>

<?php
// Close database connection
$conn->close();
?>
