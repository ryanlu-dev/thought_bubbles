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

        <form method="GET">
            <?php
                $config = parse_ini_file("../../database/db_config.ini");
                
                $conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

            ?>
        </form>
    </body>
</html>