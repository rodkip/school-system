<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check for session to ensure user is logged in
if (empty($_SESSION['cpmsaid'])) {
    header('location:logout.php');
    exit();
}

// Run the Python script and capture the JSON output
$output = shell_exec("python3 scraper.py");

// Clean up the output to remove extra spaces or newlines
$output = trim($output);

// Decode the JSON data into an associative array
$news = json_decode($output, true);

// Check for decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error decoding JSON: " . json_last_error_msg();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-FTD | PHP & MySQL News</title>

    <!-- Core CSS -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet"/>
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet"/>
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet"/>
    <link href="assets/css/style.css" rel="stylesheet"/>
    <link href="assets/css/main-style.css" rel="stylesheet"/>
</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php');?>
        <?php include_once('includes/sidebar.php');?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Latest PHP & MySQL News</h1>
                </div>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">Latest News from PHP.net</div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>News Title</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Loop through the news array and display the results
                            if (!empty($news)) {
                                $i = 1;
                                foreach ($news as $item) {
                                    echo "<tr>
                                            <td>{$i}</td>
                                            <td>{$item['title']}</td>
                                            <td><a href='{$item['url']}' target='_blank'>Read More</a></td>
                                          </tr>";
                                    $i++;
                                }
                            } else {
                                echo "<tr><td colspan='3'>No news found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
</body>
</html>
