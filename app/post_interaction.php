<?php
    // Include config file
    $config = parse_ini_file("../../database/db_config.ini"); // TODO: update this path

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
        $_content = $_POST['content'];
        // timestamp automatically generated

        // TODO: finalize db structure 

        // Prepare an insert statement
        $sql = "INSERT INTO Interactions (ParentID, SessionID, PromptID, StudentID, InteractionType, Content) VALUES (?, ?, ?, ?, ?, ?)";

        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("iiiiis", $ParentID, $SessionID, $PromptID, $StudentID, $InteractionType, $Content);
            
            // Set parameters
            $ParentID = $parent_id;
            $SessionID = $session_id;
            $PromptID = $prompt_id;
            $StudentID = $student_id;   
            $InteractionType = $interaction_type;
            $Content = $_content;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                echo "Statement POSTed successfully - check the database!";
            } else{
                echo "Oops! Something went wrong. Please try again later.";
                die;
                }

            // Close statement
            $stmt->close();
        }
    }
?>
