<?php
// Initialize variable
$payeeid = '';
$rlt = null;

// Handle form submission
if (isset($_POST['search_submit']) && !empty($_POST['payeeid'])) {
    $payeeid = htmlspecialchars($_POST['payeeid']);

    // Prepare and execute PDO query
    $searchquery = "SELECT * FROM payeedetails WHERE payeeid = :payeeid";
    $qry = $dbh->prepare($searchquery);
    $qry->bindParam(':payeeid', $payeeid, PDO::PARAM_STR);
    $qry->execute();
    $rlt = $qry->fetch(PDO::FETCH_OBJ);
}
?>

<!-- Modal -->
<div class="modal fade" id="payeedetails" tabindex="-1" aria-labelledby="payeedetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h2 class="modal-title mx-auto" id="payeedetailsLabel">Payee Details</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <h5 class="text-primary mb-4">Personal Details</h5>

                <?php if ($rlt) { ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <tbody>
                                <tr>
                                    <th scope="row" class="w-25">PayeeId:</th>
                                    <td><?= htmlentities($rlt->payeeid) ?></td>
                                </tr>
                                <tr>
                                    <th>Names:</th>
                                    <td><?= htmlentities($rlt->payeename) ?></td>
                                </tr>
                                <tr>
                                    <th>Gender:</th>
                                    <td><?= htmlentities($rlt->gender) ?></td>
                                </tr>
                                <tr>
                                    <th>Postal Address:</th>
                                    <td><?= htmlentities($rlt->postaladdress) ?></td>
                                </tr>
                                <tr>
                                    <th>Mobile No:</th>
                                    <td><?= htmlentities($rlt->mobileno) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlentities($rlt->email) ?></td>
                                </tr>
                                <tr>
                                    <th>Profession/Services:</th>
                                    <td><?= htmlentities($rlt->proffession) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php } else if (isset($_POST['search_submit'])) { ?>
                    <div class="alert alert-warning text-center" role="alert">
                        No records found for the provided Payee ID.
                    </div>
                <?php } ?>
            </div>
           
        </div>
    </div>
</div>
