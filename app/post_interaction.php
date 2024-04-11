<?php
    // Include config file
    $config = parse_ini_file("../../../database/db_config.ini"); // TODO: update this path

    //Database connection
    $link = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);
    // Check connection
    if ($link->connect_error) {
        die("Connection failed: " . $link->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // interaction ID automatically generated
        $parent_id = $_POST['parent_id'];
        $session_id = $_POST['session_id'];
        $prompt_id = $_POST['prompt_id'];
        $student_id = $_POST['student_id'];
        $interaction_type = $_POST['interaction_type'];
        $content = $_POST['content'];
        // timestamp automatically generated

        // TODO: finalize db structure 

        // Prepare an insert statement
        $sql = "INSERT INTO Interactions (ParentID, SessionID, PromptID, StudentID, InteractionType, Content) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $ParentID, $SessionID, $PromptID, $StudentID, $InteractionType, $Content);
            
            // Set parameters
            $ParentID = $parent_id;
            $SessionID = $session_id;
            $PromptID = $prompt_id;
            $StudentID = $student_id;
            $InteractionType = $interaction_type;
            $Content = $response_text;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // TODO: desired action
            } else{
                echo "Oops! Something went wrong. Please try again later.";
                die;
                }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
?>
