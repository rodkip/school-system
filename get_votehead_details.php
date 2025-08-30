<?php
include('includes/dbconnection.php');

$feestructurename = $_POST['feestructurename'];

$stmt = $dbh->prepare("SELECT v.votehead, f.firstterm, f.secondterm, f.thirdterm, f.total 
                       FROM feestructurevoteheadcharges f 
                       JOIN voteheads v ON f.votehead_id = v.id 
                       WHERE f.feestructurename = ?");
$stmt->execute([$feestructurename]);

$rows = $stmt->fetchAll(PDO::FETCH_OBJ);
$counter = 1;

// Initialize accumulators for totals
$totalFirstTerm = 0;
$totalSecondTerm = 0;
$totalThirdTerm = 0;
$grandTotal = 0;

foreach ($rows as $row) {
    echo "<tr>
            <td>" . $counter++ . "</td>
            <td>" . htmlentities($row->votehead) . "</td>
            <td>" . number_format($row->firstterm) . "</td>
            <td>" . number_format($row->secondterm) . "</td>
            <td>" . number_format($row->thirdterm) . "</td>
            <td>" . number_format($row->total) . "</td>
          </tr>";

    // Accumulate totals
    $totalFirstTerm += $row->firstterm;
    $totalSecondTerm += $row->secondterm;
    $totalThirdTerm += $row->thirdterm;
    $grandTotal += $row->total;
}

if (!empty($rows)) {
    // Output totals row
    echo "<tr style='font-weight: bold; background-color:rgb(92, 166, 226);'>
            <td colspan='2'>Total</td>
            <td>" . number_format($totalFirstTerm) . "</td>
            <td>" . number_format($totalSecondTerm) . "</td>
            <td>" . number_format($totalThirdTerm) . "</td>
            <td>" . number_format($grandTotal) . "</td>
          </tr>";
} else {
    echo "<tr><td colspan='6'>No votehead data found.</td></tr>";
}
?>


