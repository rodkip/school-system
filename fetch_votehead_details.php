<?php
include('includes/dbconnection.php');

if (isset($_POST['payment_id'])) {
    $payment_id = $_POST['payment_id'];
    try {
        // Fetch main payment info
        $main = $dbh->prepare("SELECT printed, print_date, receiptno, cash FROM feepayments WHERE id = :payment_id");
        $main->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
        $main->execute();
        $payment = $main->fetch(PDO::FETCH_OBJ);

        // Fetch votehead breakdown
        $sql = "SELECT v.votehead, f.amount 
                FROM feepayment_voteheads f 
                JOIN voteheads v ON v.id = f.votehead_id 
                WHERE f.payment_id = :payment_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($payment) {
            // Receipt Header
            echo '<div>';
            echo '<strong><i class="fa fa-receipt"></i> Receipt No:</strong> ' . htmlentities($payment->receiptno);
            echo ' &nbsp; | &nbsp; <strong><i class="fa fa-coins"></i> Total Paid:</strong> Ksh ' . number_format($payment->cash, 2);
            echo '</div>';

        }

        if ($results) {
            echo '<div class="table-responsive">';
            echo '<table class="table table-condensed table-bordered">';
            echo '<thead class="bg-primary text-white">';
            echo '<tr><th><i class="fa fa-tags"></i> Votehead</th><th><i class="fa fa-money-bill"></i> Amount</th></tr>';
            echo '</thead><tbody>';
            foreach ($results as $item) {
                echo '<tr>';
                echo '<td><i class="fa fa-caret-right text-info"></i> ' . htmlentities($item->votehead) . '</td>';
                echo '<td class="text-success font-weight-bold">Ksh ' . number_format($item->amount, 2) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info"><i class="fa fa-info-circle"></i> No votehead breakdown found for this payment.</div>';
        }
        if ($payment) {
          
            // Print Status
            echo '<div>';
            echo '<strong><i class="fa fa-print"></i> Printed:</strong> ';
            if ($payment->printed == 1) {
                echo '<span class="text-success"><i class="fa fa-check-circle"></i> Yes</span>';
            } else {
                echo '<span class="text-danger"><i class="fa fa-times-circle"></i> No</span>';
            }

            if (!empty($payment->print_date)) {
                echo ' &nbsp; | &nbsp; <strong><i class="fa fa-calendar-alt"></i> Print Date:</strong> ' . htmlentities($payment->print_date);
            }
            echo '</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error: ' . $e->getMessage() . '</div>';
    }
} else {
    echo '<div class="alert alert-warning"><i class="fa fa-warning"></i> Invalid payment reference.</div>';
}
?>
