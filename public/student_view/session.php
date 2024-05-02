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
            echo "Error executing query: " . $prestmt->error;
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
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="components/reactions.js" type="text/javascript" defer></script>
    <title>Discussion Time!</title>
</head>
<body>
<div class='container-fluid'>
    <div class="card text-center">
        <div class="card-header">
            Current Discussion Question
        </div>
        <div class="card-body">
            <h5 class="card-title" id="qtitle">Waiting...</h5>
            <!-- <p class="card-text">[extra clarification (optional)]</p> -->
        </div>
        <div class="card-footer text-muted">
        </div>
    </div>
</div>
<br>
<!-- Form for answering the question -->
<form method="post">
    <div class="row d-flex justify-content-center align-items-center">
        <input type="hidden" name="sessionID" value="<?php echo $sessionID; ?>">
        <input type="hidden" name="interactionType" value="message">
        <div class = "card">
            <div class="input-group input-group-lg justify-content-center">
                <span class="input-group-text" id="inputGroup-sizing-lg">Answer Here</span>
                <textarea id="answer" name="answer" aria-label = 'Answer Here' required></textarea><br>
            </div>
            <button type="submit" class="btn btn-primary" >Submit Answer</button>
        </div>
    </div>
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
        <input type="hidden" name="studentName" value="<?php echo $StudentName; ?>">
        <input type="hidden" name="displayName" value="<?php echo $displayName; ?>">
        <input type="hidden" name="interactionType" value="reply">
        <div class="card-body form-group">
            <textarea class="form-control" id="reply-textbox" name="reply-content" required></textarea>
        </div>
        <div class="text-center">
            <button class="btn btn-primary" type="submit">Submit Reply</button>
        </div>
    </form>
</div>

<div id="responseArea">

</div>

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
        html += "<div class='col-sm-6 mb-3 mb-sm-0' style='margin-bottom: 10px'>";
        html += "<div class='card mb-3'  id='msg" + value.InteractionID + "'>";
        html += "<div class='card-body'>";
        html += "<p class='text-center'><b>" + value.DisplayName + "</b></p>";
        html += "<p class='text-center'>" + value.Content + "</p>";
        html += "<div class='row'>";
        html += "<div class='col'>";
        html += "<div class='d-grid gap-2 d-md-block justify-content-md-start'>";
        html += "<button class='btn btn-primary' type='button' onclick='openReplyBox(\"" + value.DisplayName + "\", \"" + value.Content + "\", \"" + value.InteractionID + "\")'>Reply</button><br><br>";
        html += "</div>";
        html += "</div>";
        html += "<div class='col>";
        html += "<div class='justify-content-md-end'>";
        html += "<form method='POST' action='post_likes.php'>";
        html += "<input type='hidden' name='sessionID' value='<?php echo $sessionID; ?>'>";
        html += "<input type='hidden' name='StudentID' value='<?php echo $StudentID; ?>'>";
        html += "<input type='hidden' name='studentName' value='<?php echo $StudentName; ?>'>";
        html += "<input type='hidden' name='displayName' value='<?php echo $displayName; ?>'>";
        html += "<input type='hidden' name='interactionType' value='reaction'>";
        html += "<button type = 'submit'><img src = '../../resources/reactions/heart.svg' alt = 'heart'/></button>"
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
                    html += '<div class="alert alert-warning">';
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

    var q = window.setInterval(getQuestion, 2500);
    var intervalID = window.setInterval(getMsg, 2500);
</script>
</body>
</html>