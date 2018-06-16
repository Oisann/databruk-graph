<?php
    date_default_timezone_set('Europe/Oslo');
    $servername = getenv("MYSQL_HOST");
    $username = getenv("MYSQL_USER");
    $password = getenv("MYSQL_PASSWORD");
    $dbname = getenv("MYSQL_DATABASE");

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT DataUsageMB, DataLimitMB, TotalRemainingDataMB, SmsUsage, TalkUsage, timestamp FROM datausage ORDER BY timestamp DESC LIMIT 0, 100";
    if ($conn->query($sql) === TRUE) {
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title></title>
        <script>
            var data = { "entries": [<?php
                while($row = mysql_fetch_assoc($result)) {
                    echo "{\"timestamp\": ";
                    echo "\"". intval($row['timestamp']) ."\", ";
                    echo "\"usage\": {";

                    echo "\"data\": ";
                    echo "\"". intval($row['DataUsageMB']) ."\", ";
                    echo "\"talk\": ";
                    echo "\"". intval($row['TalkUsage']) ."\", ";
                    echo "\"sms\": ";
                    echo "\"". intval($row['SmsUsage']) ."\", ";
                    
                    echo "}, ";

                    echo "\"limits\": {";

                    echo "\"data\": ";
                    echo "\"". intval($row['DataLimitMB']) ."\", ";
                    echo "\"talk\": ";
                    echo "\"". -1 ."\", ";
                    echo "\"sms\": ";
                    echo "\"". -1 ."\", ";

                    echo "}, ";

                    echo "\"totalRemainingData\": ";
                    echo "\"". intval($row['TotalRemainingDataMB']) ."\"";
                    echo "}, ";
                }
            ?>]}
        </script>
    </head>
    <body>
        <h1>Data</h1>
    </body>
</html><?php
    } else {
        header('Content-Type: application/json');
        echo "{ \"status\": \"error\" }";
    }
    
    $conn->close();
?>
