<?php
include('includes/dbconnection.php');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
session_start();

$_SESSION['update_summary'] = [
    'updated' => $updatedCount,
    'errors' => $errorCount
];

if ($errorCount > 0) {
    $_SESSION['error_messages'] = $errors; // Optional for detailed display
}

// Count all distinct students
$countSql = "SELECT COUNT(DISTINCT studentadmno) as total FROM studentdetails";
$countQuery = $dbh->prepare($countSql);
$countQuery->execute();
$totalStudents = $countQuery->fetch(PDO::FETCH_OBJ)->total;
function send_progress($message) {
    echo $message . PHP_EOL;
    ob_flush();
    flush();
}

// Get student list
$sql = "SELECT DISTINCT studentadmno FROM studentdetails ORDER BY studentadmno";
$query = $dbh->prepare($sql);
$query->execute();

$updatedCount = 0;
$errorCount = 0;
$processed = 0;
$errors = [];

while ($student = $query->fetch(PDO::FETCH_OBJ)) {
    $processed++;
    $searchadmno = $student->studentadmno;

    try {
        $yearlybal = 0;
        $arr = 0;

        // Main balance calculation query
        $balanceSql = "SELECT 
                studentdetails.studentadmno, 
                studentdetails.studentname,  
                classentries.gradefullname,
                classentries.childstatus, 
                feestructure.EntryTerm, 
                feestructure.boarding, 
                classentries.childTreatment, 
                classentries.feeTreatment, 
                feestructure.firsttermfee, 
                feestructure.secondtermfee, 
                feestructure.thirdtermfee, 
                feestructure.othersfee, 
                COALESCE(transportentries.firsttermtransport, 0) AS firsttermtransport,
                COALESCE(transportentries.secondtermtransport, 0) AS secondtermtransport,
                COALESCE(transportentries.thirdtermtransport, 0) AS thirdtermtransport, 
                SUM(feepayments.Cash) AS totpayperyear, 
                classentries.feetreatmentrate, 
                classentries.childtreatmentrate AS classentries_childtreatmentrate,
                ROUND(feestructure.firsttermfee + feestructure.secondtermfee + feestructure.thirdtermfee + feestructure.othersfee) AS totfee, 
                ROUND((feestructure.firsttermfee * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.firsttermtransport, 0)) AS firsttermfeecal, 
                ROUND((feestructure.secondtermfee * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.secondtermtransport, 0)) AS secondtermfeecal, 
                ROUND((feestructure.thirdtermfee * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.thirdtermtransport, 0)) AS thirdtermfeecal, 
                ROUND(feestructure.othersfee * classentries.feetreatmentrate * classentries.childtreatmentrate) AS othersfeecal, 
                ROUND(((feestructure.firsttermfee + feestructure.secondtermfee + feestructure.thirdtermfee + feestructure.othersfee) * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.firsttermtransport, 0) + COALESCE(transportentries.secondtermtransport, 0) + COALESCE(transportentries.thirdtermtransport, 0)) AS totcalfee, 
                ROUND(((feestructure.firsttermfee + feestructure.secondtermfee + feestructure.thirdtermfee + feestructure.othersfee) * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.firsttermtransport, 0) + COALESCE(transportentries.secondtermtransport, 0) + COALESCE(transportentries.thirdtermtransport, 0) - SUM(feepayments.Cash)) AS balperyear, 
                ROUND(((feestructure.firsttermfee + feestructure.othersfee) * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.firsttermtransport, 0) - SUM(feepayments.Cash)) AS firsttermbal, 
                ROUND(((feestructure.firsttermfee + feestructure.secondtermfee + feestructure.othersfee) * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.firsttermtransport, 0) + COALESCE(transportentries.secondtermtransport, 0) - SUM(feepayments.Cash)) AS secondtermbal, 
                ROUND(((feestructure.firsttermfee + feestructure.secondtermfee + feestructure.thirdtermfee + feestructure.othersfee) * classentries.feetreatmentrate * classentries.childtreatmentrate) + COALESCE(transportentries.firsttermtransport, 0) + COALESCE(transportentries.secondtermtransport, 0) + COALESCE(transportentries.thirdtermtransport, 0) - SUM(feepayments.Cash)) AS thirdtermbal, 
                COUNT(feepayments.Cash) AS instalments
            FROM 
                feestructure 
            INNER JOIN 
                (classdetails 
                INNER JOIN 
                ((feepayments 
                INNER JOIN studentdetails ON feepayments.studentadmno = studentdetails.studentadmno) 
                INNER JOIN classentries ON studentdetails.studentadmno = classentries.studentAdmNo) 
                ON (classdetails.academicyear = feepayments.academicyear) 
                AND (classdetails.gradefullName = classentries.gradefullname)) 
            ON feestructure.feeStructureName = classentries.feegradename
            LEFT JOIN 
                transportentries 
            ON transportentries.classentryfullname = classentries.classentryfullname
            WHERE studentdetails.studentadmno = :searchadmno
            GROUP BY 
                studentdetails.studentadmno, 
                studentdetails.studentname, 
                classentries.gradefullname, 
                feestructure.EntryTerm, 
                feestructure.boarding, 
                classentries.childTreatment, 
                classentries.feeTreatment, 
                feestructure.firsttermfee, 
                feestructure.secondtermfee, 
                feestructure.thirdtermfee, 
                feestructure.othersfee, 
                classentries.feetreatmentrate, 
                classentries.childtreatmentrate
            ORDER BY 
                classentries.gradefullname ASC";

        $balanceQuery = $dbh->prepare($balanceSql);
        $balanceQuery->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
        $balanceQuery->execute();
        $results = $balanceQuery->fetchAll(PDO::FETCH_OBJ);

        if ($balanceQuery->rowCount() > 0) {
            foreach ($results as $row) {
                $adjusted = $yearlybal + $arr;

                $first_total = $row->firsttermbal + $adjusted;
                $second_total = $row->secondtermbal + $adjusted;
                $third_total = $row->thirdtermbal + $adjusted;

                $first_limit = $row->firsttermfeecal + $row->othersfeecal;
                $second_limit = $row->secondtermfeecal;
                $third_limit = $row->thirdtermfeecal;

                $firsttermbalcal = max(0, min($first_total, $first_limit));
                $secondtermbalcal = max(0, min($second_total, $second_limit));
                $thirdtermbalcal = max(0, min($third_total, $third_limit));

                $arrears = $yearlybal + $arr;
                $balperyear = $row->balperyear + $arrears;
                $yearlybal += $row->balperyear + $arr;

                $feebalancecode = $row->gradefullname . $row->studentadmno;

                $checkSql = "SELECT feebalancecode FROM feebalances WHERE feebalancecode = :feebalancecode";
                $checkQuery = $dbh->prepare($checkSql);
                $checkQuery->bindParam(':feebalancecode', $feebalancecode, PDO::PARAM_STR);
                $checkQuery->execute();

                if ($checkQuery->rowCount() > 0) {
                    $updateSql = "UPDATE feebalances SET 
                        childstatus = :childstatus,
                        arrears = :arrears,
                        firsttermbal = :firsttermbalcal,
                        secondtermbal = :secondtermbalcal,
                        thirdtermbal = :thirdtermbalcal,
                        yearlybal = :balperyear,
                        feetreatment = :feetreatment,
                        childtreatment = :childtreatment,
                        studentname = :studentname,
                        gradefullname = :gradefullname,
                        totalfee = :totcalfee,
                        totalpaid = :totpayperyear,
                        firsttermfee = :firsttermfeecal,
                        secondtermfee = :secondtermfeecal,
                        thirdtermfee = :thirdtermfeecal,
                        othersfee = :othersfeecal,
                        boarding = :boarding,
                        last_updated = NOW()
                    WHERE feebalancecode = :feebalancecode";

                    $updateQuery = $dbh->prepare($updateSql);
                    $success = $updateQuery->execute([
                        ':feebalancecode' => $feebalancecode,
                        ':childstatus' => $row->childstatus,
                        ':arrears' => $arrears,
                        ':firsttermbalcal' => $firsttermbalcal,
                        ':secondtermbalcal' => $secondtermbalcal,
                        ':thirdtermbalcal' => $thirdtermbalcal,
                        ':balperyear' => $balperyear,
                        ':feetreatment' => $row->feeTreatment,
                        ':childtreatment' => $row->childTreatment,
                        ':studentname' => $row->studentname,
                        ':gradefullname' => $row->gradefullname,
                        ':totcalfee' => $row->totcalfee,
                        ':totpayperyear' => $row->totpayperyear,
                        ':firsttermfeecal' => $row->firsttermfeecal,
                        ':secondtermfeecal' => $row->secondtermfeecal,
                        ':thirdtermfeecal' => $row->thirdtermfeecal,
                        ':othersfeecal' => $row->othersfeecal,
                        ':boarding' => $row->boarding
                    ]);

                    if ($success) {
                        $updatedCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Failed to update record for $feebalancecode";
                    }
                } else {
                    $errorCount++;
                    $errors[] = "No matching feebalance for $feebalancecode";
                }
            }
        }
    } catch (PDOException $e) {
        $errorCount++;
        $errors[] = "Error for student $searchadmno: " . $e->getMessage();
    }
}

// Optional: Cleanup logic
if (isset($_POST['clean'])) {
    $sql = "DELETE FROM feebalances WHERE feebalancecode NOT IN (SELECT f.classentryfullname FROM Classentries f)";
    $dbh->exec($sql);
    $messagestate = 'deleted';
    $mess = "Record Deleted....";
}
?>
