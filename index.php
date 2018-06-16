<?php
    date_default_timezone_set('Europe/Oslo');
    $servername = getenv("MYSQL_HOST");
    $username = getenv("MYSQL_USER");
    $password = getenv("MYSQL_PASSWORD");
    $dbname = getenv("MYSQL_DATABASE");
    $FORMAT_TIME = $_GET['format'];

    if(!isset($FORMAT_TIME)) {
        $FORMAT_TIME = getenv("FORMAT_TIME");
        if($FORMAT_TIME == "") {
            $FORMAT_TIME = "l jS \of F Y H:m:s";
        }
    }

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $start = strtotime("-1 month");
    $sql = "SELECT DataUsageMB, DataLimitMB, TotalRemainingDataMB, SmsUsage, TalkUsage, max(timestamp) as lasttimestamp, timestamp FROM datausage WHERE timestamp >= " . $start . " group by date(from_unixtime(timestamp)) ORDER BY lasttimestamp ASC LIMIT 0, 100";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title></title>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            var data = { "entries": [<?php
                $cached_rows = array();
                while($row = $result->fetch_assoc()) {
                    array_push($cached_rows, $row);
                    $time = intval($row['timestamp']);
                    echo "{\"timestamp\": ";
                    echo "". $time .", ";
                    echo "\"time\": ";
                    echo "\"" . date($FORMAT_TIME, $time) ."\", ";
                    echo "\"usage\": {";

                    echo "\"data\": ";
                    echo "". intval($row['DataUsageMB']) .", ";
                    echo "\"talk\": ";
                    echo "".intval($row['TalkUsage']) .", ";
                    echo "\"sms\": ";
                    echo "".intval($row['SmsUsage']) .", ";
                    
                    echo "}, ";

                    echo "\"limits\": {";

                    echo "\"data\": ";
                    echo "". intval($row['DataLimitMB']) .", ";
                    echo "\"talk\": ";
                    echo "". -1 .", ";
                    echo "\"sms\": ";
                    echo "". -1 .", ";

                    echo "}, ";

                    echo "\"totalRemainingData\": ";
                    echo intval($row['TotalRemainingDataMB']);
                    echo "}, ";
                }
            ?>]}


            google.charts.load('current', {packages: ['corechart', 'line']});
            google.charts.setOnLoadCallback(drawBasic);

            function drawBasic() {

                var data = new google.visualization.DataTable();
                data.addColumn('date', 'Date');
                data.addColumn('number', 'MB');

                data.addRows([
                    <?php
                    foreach($cached_rows as $row) {
                        $time = intval($row['timestamp']);
                        echo "[new Date('" . date("F j, Y H:m:s", $time) . "'), " . (intval($row['DataLimitMB']) - intval($row['TotalRemainingDataMB'])) . "], ";
                    }
                ?>
                ]);

                var options = {
                    hAxis: {
                    title: 'Time'
                    },
                    vAxis: {
                    title: 'MB'
                    }/*,
                    trendlines: {
                        0: {type: 'exponential', color: '#333', opacity: 1}
                    }*/
                };

                var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }
        </script>
    </head>
    <body>
        <h1>Data</h1>
        <div id="chart_div"></div>
        
        <table>
            <tr>
                <td>
                    Data:
                </td>
                <td>
                    <div id="data">n/a</div>
                </td>
            </tr>
            <tr>
                <td>
                    SMS:
                </td>
                <td>
                    <div id="sms">n/a</div>
                </td>
            </tr>
            <tr>
                <td>
                    Minutt:
                </td>
                <td>
                    <div id="minutt">n/a</div>
                </td>
            </tr>
        </table>
    </body>
    <script>
        document.getElementById("data").innerText = data.entries[data.entries.length-1]["usage"]["data"] / 1000 + " GB ("  + (data.entries[data.entries.length-1]["limits"]["data"] - data.entries[data.entries.length-1]["totalRemainingData"]) / 1000 + " GB)";
        document.getElementById("sms").innerText = data.entries[data.entries.length-1]["usage"]["sms"];
        document.getElementById("minutt").innerText = data.entries[data.entries.length-1]["usage"]["talk"] / 60;
    </script>
</html><?php
    } else {
        header('Content-Type: application/json');
        echo "{ \"status\": \"error\" }";
    }
    
    $conn->close();
?>
