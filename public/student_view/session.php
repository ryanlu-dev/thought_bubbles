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
	
//	echo "Student ID : " . $StudentID . "<br>";
//	echo "Session ID : " . $sessionID . "<br>";
//	echo "Session displayName : " . $displayName . "<br>";
//	echo "Session StudentName : " . $StudentName . "<br>";
	// Retrieve the (latest) question for the session from the database
	$sql = "SELECT Content FROM interactions WHERE SessionID = ? AND InteractionType = 'Question' ORDER BY Timestamp DESC LIMIT 1";
	$stmt = $conn->prepare($sql);
	if ($stmt) {
		$stmt->bind_param("i", $sessionID);
		if ($stmt->execute()) {
			$result = $stmt->get_result();
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$question = $row['Content'];
			} else {
				echo "No question yet available for this session.";
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
			$resp =  "Answer submitted successfully.";
		} else {
			$resp =  "Error executing query: " . $stmt->error;
		}
		$stmt->close();
	} else {
		echo "Error preparing statement: " . $conn->error;
	}
}

// Close database connection
$conn->close();
?>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta
                name="viewport"
                content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />
        <link
                rel="stylesheet"
                href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
                integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N"
                crossorigin="anonymous"
        />
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
        <script src="components/reactions.js" type="text/javascript" defer></script>
        <script>
            function getMsg() {
                $.ajax({
                    type: "GET",
                    url: "../server/getstate.php",
                    success: function (response) {
                        response = JSON.parse(response);
                        var html = $('<div/>');
                        if(response.length) {
                            $.each(response, function (key, value) {
                                var content = '';
                                // html += "<div class='col-lg-2 col-md-3 col-6' style='margin-bottom: 10px'>";
                                content+="<div class = 'col'>";
                                content += "<div class='card mb-3'>";
                                content += "<div class='card-body'>";
                                content += "<p class='text-center'><b>" + value.DisplayName + "</b></p>";
                                content += "<p class='text-center'>" + value.Content + "</p>";
                                content += "<div class='row'>";
                                content += "<div class='col'>";
                                content += "<div class='d-grid gap-2 d-md-block justify-content-md-start'>";
                                content += "<button class='btn btn-primary' type='button'>Reply</button>";
                                content += "</div>";
                                content += "</div>";
                                content += "<div class='col'>";
                                content += "<student-reaction></student-reaction>";
                                content += "</div>"
                                content += "</div>"
                                content += "</div>"
                                content += "</div>"
                                content += "</div>"
                                content += "</div>"
                                html.append(content);
                            });
                        }
                        // } else {
                        //     html += '<div class="alert alert-warning">';
                        //     html += 'No records found!';
                        //     html += '</div>';
                        // }
                        $("#responseArea").html(html);
                    }
                });
            }

            // getMsg();
            //
            // var intervalID = window.setInterval(getMsg, 2500);
        </script>


        <title>Thought Bubbles</title>
    </head>
    <body>
        <div class="container">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                        <form method="post">
                            <input type="hidden" name="sessionID" value="<?php echo $sessionID; ?>">
                            <input type="hidden" name="interactionType" value="message">
                        <div class="card">
                            <div class='card-body text-center'>
                                <h3>Question:  </h3>
                                <h4><?=$question;?></h4><br>
                                <div class="input-group">
                                    <span class="input-group-text">Your Response</span>
                                    <textarea id="answer" name="answer" rows="4" cols="50" class="form-control" aria-label="With textarea" required></textarea>
                                </div><br>
                                <button class="btn btn-primary" type="submit">Submit Answer</button>
                            </div>
                        </div>
                        </form>

                    </div>
                    <div id="responseArea" class="container text-center">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4">
                                <script>getMsg()</script>
                        </div>
                </div>
        </div>
    </body>
</html>