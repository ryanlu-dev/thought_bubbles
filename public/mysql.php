<?php
	error_reporting(E_ALL);
?>

<html>

    <head>
        <script>

        </script>
    </head>

    <body>
        <h1>
            Summary of database items:
        </h1>

        <?php
            $config = parse_ini_file("../../database/db_config.ini");
            
            $conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sqlquery = "SELECT * FROM Interactions";
            $result = $conn->query($sqlquery);

            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "IntID: " . $row["InteractionID"] . "<br>";
                    if ($row["ParentID"] != NULL) {
                        echo "ParentID: " . $row["ParentID"] . "<br>";
                    }
                    echo "SessionID: " . $row["SessionID"] . "<br>";
                    echo "PromptID: " . $row["PromptID"] . "<br>";
                    echo "StudentID: " . $row["StudentID"] . "<br>";
                    echo "InteractionType: " . $row["InteractionType"] . "<br>";
                    echo "Content: " . $row["Content"] . "<br>";
                    echo "Timestamp: " . $row["Timestamp"] . "<br>";
                    echo "<br>";
                }
            }

            $conn->close();
        ?>
    </body>
</html>