<?php
include('includes/dbconnection.php');

if (!isset($_GET['payment_id']) || !is_numeric($_GET['payment_id'])) {
    echo '<div class="alert alert-warning"><i class="fa fa-warning"></i> Invalid payment reference.</div>';
    exit;
}

$payment_id = intval($_GET['payment_id']);

try {
    $sql = "SELECT 
                o.printed, o.print_date,o.receiptno, o.amount AS total_amount, o.studentadmno,
                b.item_id, p.otherpayitemname, b.amount AS item_amount
            FROM 
                otheritemspayments o
            JOIN 
                otheritemspayments_breakdown b ON o.id = b.payment_id
            JOIN 
                otherpayitems p ON b.item_id = p.id
            WHERE 
                o.id = :payment_id
            ORDER BY 
                p.otherpayitemname ASC";

    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($results)) {
        echo '<div class="alert alert-info"><i class="fa fa-info-circle"></i> No item breakdown found for this payment.</div>';
        exit;
    }

    $payment = $results[0];
    $allocated_total = 0;
    foreach ($results as $row) {
        $allocated_total += $row->item_amount;
    }
    $unallocated = $payment->total_amount - $allocated_total;

    // Receipt Header
    echo '<div>';
    echo '<strong><i class="fa fa-receipt"></i> Receipt No:</strong> ' . htmlentities($payment->receiptno);
    echo ' &nbsp; | &nbsp; <strong><i class="fa fa-coins"></i> Total Paid:</strong> Ksh ' . number_format($payment->total_amount, 2);
    echo '</div>';

    // Breakdown Table
    echo '<div class="table-responsive">';
    echo '<table class="table table-condensed table-bordered">';
    echo '<thead class="bg-primary text-white">';
    echo '<tr><th><i class="fa fa-tags"></i> Item Name</th><th><i class="fa fa-money-bill"></i> Amount</th></tr>';
    echo '</thead><tbody>';

    foreach ($results as $row) {
        echo '<tr>';
        echo '<td><i class="fa fa-caret-right text-info"></i> ' . htmlentities($row->otherpayitemname) . '</td>';
        echo '<td class="text-success font-weight-bold">Ksh ' . number_format($row->item_amount, 2) . '</td>';
        echo '</tr>';
    }

    if ($unallocated > 0) {
        echo '<tr class="table-warning">';
        echo '<td><i class="fa fa-exclamation-circle text-warning"></i> <strong>Unallocated Amount</strong></td>';
        echo '<td class="text-danger font-weight-bold">Ksh ' . number_format($unallocated, 2) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '<tfoot class="bg-light fw-bold">';
    echo '<tr><td><i class="fa fa-calculator"></i> Grand Total</td><td class="text-end">Ksh ' . number_format($payment->total_amount, 2) . '</td></tr>';
    echo '</tfoot>';
    echo '</table>';
    echo '</div>';

} catch (PDOException $e) {
    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Database Error: ' . htmlentities($e->getMessage()) . '</div>';
}
if ($results) {
          
    // Print Status
    echo '<div>';
    echo '<strong><i class="fa fa-print"></i> Printed:</strong> ';
    if ($row->printed == 1) {
        echo '<span class="text-success"><i class="fa fa-check-circle"></i> Yes</span>';
    } else {
        echo '<span class="text-danger"><i class="fa fa-times-circle"></i> No</span>';
    }

    if (!empty($row->print_date)) {
        echo ' &nbsp; | &nbsp; <strong><i class="fa fa-calendar-alt"></i> Print Date:</strong> ' . htmlentities($payment->print_date);
    }
    echo '</div>';
}
?>
