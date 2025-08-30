<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (isset($_POST['studentadmno'])) {
    $studentadmno = $_POST['studentadmno'];

    // Prepare the SQL query
    $sql = "SELECT 
                sd.id, sd.studentadmno, sd.studentname, sd.gender, sd.dateofbirth, 
                sd.previousschool, sd.entrydate, 
                pd_mother.parentname AS mothername, 
                pd_father.parentname AS fathername, 
                pd_guardian.parentname AS guardianname, 
                pd_mother.parentcontact AS mothercontact, 
                pd_father.parentcontact AS fathercontact, 
                pd_guardian.parentcontact AS guardiancontact, 
                pd_mother.homearea AS homearea 
            FROM studentdetails sd
            LEFT JOIN parentdetails pd_mother ON sd.motheridno = pd_mother.idno
            LEFT JOIN parentdetails pd_father ON sd.fatheridno = pd_father.idno
            LEFT JOIN parentdetails pd_guardian ON sd.guardianidno = pd_guardian.idno
            WHERE sd.studentadmno = :studentadmno";

    $query = $dbh->prepare($sql);
    $query->bindParam(':studentadmno', $studentadmno, PDO::PARAM_STR);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) {
        // Display student details in a table
        echo '<table class="table table-striped table-bordered">';
        echo '<tr><td>Adm No:</td><td><b>' . htmlspecialchars($row->studentadmno, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Name:</td><td><b>' . htmlspecialchars($row->studentname, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Gender:</td><td><b>' . htmlspecialchars($row->gender, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>DOB:</td><td><b>' . htmlspecialchars($row->dateofbirth, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Current Age:</td><td><b class="text-success">' . round((time() - strtotime($row->dateofbirth)) / (3600 * 24 * 365.25)) . ' Years</b></td></tr>';
        echo '<tr><td>Mother Name:</td><td><b>' . htmlspecialchars($row->mothername, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Father Name:</td><td><b>' . htmlspecialchars($row->fathername, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Guardian Name:</td><td><b>' . htmlspecialchars($row->guardianname, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Mother Contact:</td><td><b>' . htmlspecialchars($row->mothercontact, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Father Contact:</td><td><b>' . htmlspecialchars($row->fathercontact, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Guardian Contact:</td><td><b>' . htmlspecialchars($row->guardiancontact, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Previous School:</td><td><b>' . htmlspecialchars($row->previousschool, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '<tr><td>Home Area:</td><td><b>' . htmlspecialchars($row->homearea, ENT_QUOTES, 'UTF-8') . '</b></td></tr>';
        echo '</table>';
    } else {
        echo '<p class="text-danger">No records found for the given Admission Number.</p>';
    }
} else {
    echo '<p class="text-danger">Invalid request.</p>';
}
?>