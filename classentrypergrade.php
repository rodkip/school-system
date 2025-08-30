<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid'] == 0)) {
    header('location:logout.php');
} else {
    $viewgradefullname = $_GET['viewgradefullname'];
}

// Check if the export button was clicked
if (isset($_GET['export_csv'])) {
    // Updated SQL query to include dormitoryname
    $sql = "SELECT studentdetails.studentname, 
                   classentries.studentadmno, 
                   classentries.gradefullname, 
                   classentries.feetreatment, 
                   classentries.childtreatment, 
                   classentries.entryterm, 
                   classentries.boarding, 
                   classentries.childstatus,
                   studentdetails.homecounty, 
                   studentdetails.birthcertno, 
                   studentdetails.upicode, 
                   studentdetails.assessmentno, 
                   transportentries.stagefullname,
                   dormitoriesdetails.dormitoryname
            FROM classentries
            INNER JOIN studentdetails 
                ON classentries.studentadmno = studentdetails.studentadmno
            LEFT JOIN transportentries 
                ON classentries.classentryfullname = transportentries.classentryfullname
            LEFT JOIN dormitoriesdetails 
                ON classentries.dorm = dormitoriesdetails.dormid
            WHERE classentries.gradefullname = :gradefullname
            GROUP BY studentdetails.studentname, 
                     classentries.studentadmno, 
                     classentries.gradefullname, 
                     classentries.feetreatment, 
                     classentries.childtreatment, 
                     classentries.entryterm, 
                     classentries.boarding, 
                     classentries.childstatus,
                     studentdetails.homecounty, 
                     studentdetails.birthcertno, 
                     studentdetails.upicode,
                     studentdetails.assessmentno, 
                     transportentries.stagefullname,
                     dormitoriesdetails.dormitoryname";

    $query = $dbh->prepare($sql);
    $query->bindParam(':gradefullname', $viewgradefullname, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    ob_start();

    $filename = "class_list_" . $viewgradefullname . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // Updated CSV header
    $header = [
        '#', 'AdmNo', 'Name', 'Grade Fullname', 'Entry Term',
        'Fee Treatment', 'Child Treatment', 'Boarding?', 'Dormitory',
        'Home County', 'Birth Cert No', 'UPI Code', 'Assessment No', 'Stage', 'Status'
    ];
    fputcsv($output, $header);

    $cnt = 1;
    if ($query->rowCount() > 0) {
        foreach ($results as $row) {
            $data = [
                $cnt,
                $row->studentadmno,
                $row->studentname,
                $row->gradefullname,
                $row->entryterm,
                $row->feetreatment,
                $row->childtreatment,
                $row->boarding,
                $row->dormitoryname ?? 'N/A',  // Added dormitoryname with fallback
                $row->homecounty,
                $row->birthcertno,
                $row->upicode,
                $row->assessmentno,
                $row->stagefullname,
                $row->childstatus
            ];
            fputcsv($output, $data);
            $cnt++;
        }
    }

    fclose($output);
    ob_end_flush();
    exit;
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Kipmetz-SMS|Class List</title>
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
</head>

<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>

        <div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h2 class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                <span>Class List <span style="color:green"><?php echo htmlentities($viewgradefullname); ?></span></span>
                <div style="text-align: right;">
                <a href="reportclasslistpergrade.php?gradefullname=<?php echo htmlentities($viewgradefullname); ?>" 
                       class="btn btn-info btn-sm" role="button">
                        <i class="fa fa-print"></i> Print View
                    </a>
                    <a href="?viewgradefullname=<?php echo htmlentities($viewgradefullname); ?>&export_csv=true" 
                       class="btn btn-success btn-sm" role="button">
                        <i class="fa fa-download"></i> Download CSV
                    </a>
                   
                </div>
            </h2>
        </div>
    </div>


            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                            <div id="table-wrapper">
                                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>AdmNo</th>
                                                        <th>Name</th>
                                                        <th>Grade</th>
                                                        <th>Stream</th>
                                                        <th>Entry Term</th>
                                                        <th>Fee Treatment</th>
                                                        <th>Child Treatment</th>
                                                        <th>Boarding</th>
                                                        <th>Dormitory</th> <!-- New -->
                                                        <th>Home County</th>
                                                        <th>Birth Cert</th>
                                                        <th>UPI</th>
                                                        <th>Assessment No</th>
                                                        <th>Stage</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>

                                                    <tbody>
                                                        <?php
                                                        // Fetch the data from database again to display on the page
                                                        $sql = "SELECT studentdetails.studentname, 
                                                        classentries.studentadmno, 
                                                        classentries.gradefullname, 
                                                        classentries.stream, 
                                                        classentries.feetreatment, 
                                                        classentries.childtreatment, 
                                                        classentries.entryterm, 
                                                        classentries.boarding, 
                                                        studentdetails.homecounty, 
                                                        studentdetails.birthcertno, 
                                                        studentdetails.upicode, 
                                                        studentdetails.assessmentno,
                                                        classentries.childstatus, 
                                                        transportentries.stagefullname,
                                                        dormitoriesdetails.dormitoryname
                                                 FROM classentries 
                                                 INNER JOIN studentdetails 
                                                     ON classentries.studentadmno = studentdetails.studentadmno
                                                 LEFT JOIN transportentries 
                                                     ON classentries.classentryfullname = transportentries.classentryfullname
                                                 LEFT JOIN dormitoriesdetails 
                                                     ON classentries.dorm = dormitoriesdetails.dormid
                                                 WHERE classentries.gradefullname = :gradefullname
                                                 GROUP BY studentdetails.studentname, 
                                                          classentries.studentadmno, 
                                                          classentries.gradefullname, 
                                                          classentries.stream, 
                                                          classentries.feetreatment, 
                                                          classentries.childtreatment, 
                                                          classentries.entryterm, 
                                                          classentries.boarding, 
                                                          studentdetails.homecounty, 
                                                          studentdetails.birthcertno, 
                                                          studentdetails.upicode,
                                                          studentdetails.assessmentno,
                                                          classentries.childstatus,  
                                                          transportentries.stagefullname,
                                                          dormitoriesdetails.dormitoryname";
                                         
                                                        
                                                        $query = $dbh->prepare($sql);
                                                        $query->bindParam(':gradefullname', $viewgradefullname, PDO::PARAM_STR);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);

                                                        $cnt = 1;
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $row) {
                                                        ?>
                                                               <tr>
                                                                    <td><?php echo htmlentities($cnt);?></td>
                                                                    <td><?php echo htmlentities($row->studentadmno);?></td>
                                                                    <td><?php echo htmlentities($row->studentname);?></td>
                                                                    <td><?php echo htmlentities($row->gradefullname);?></td>
                                                                    <td><?php echo htmlentities($row->stream);?></td>
                                                                    <td><?php echo htmlentities($row->entryterm);?></td>
                                                                    <td><?php echo htmlentities($row->feetreatment);?></td>
                                                                    <td><?php echo htmlentities($row->childtreatment);?></td>
                                                                    <td><?php echo htmlentities($row->boarding);?></td>
                                                                    <td><?php echo htmlentities($row->dormitoryname); ?></td> <!-- New Dorm Column -->
                                                                    <td><?php echo htmlentities($row->homecounty); ?></td>
                                                                    <td><?php echo htmlentities($row->birthcertno); ?></td>
                                                                    <td><?php echo htmlentities($row->upicode); ?></td>
                                                                    <td><?php echo htmlentities($row->assessmentno); ?></td>
                                                                    <td><?php echo htmlentities($row->stagefullname); ?></td>
                                                                    <td><?php echo htmlentities($row->childstatus); ?></td>
                                                                </tr>

                                                        <?php
                                                                $cnt++;
                                                            }
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            $('#dataTables-example').dataTable();
        });
    </script>
    <script>
    if (window.history.replaceState){
      window.history.replaceState(null,null,window.location.href);
    }
    </script>

</body>
</html>
