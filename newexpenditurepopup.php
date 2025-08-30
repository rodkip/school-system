<div id="makeexpenditure" class="modal fade" role="dialog" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title text-center">Make Payment</h2>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <?php if (!empty($rlt->payeeid)): ?>

        <form method="post" enctype="multipart/form-data" action="manage-expenditures.php">
          <input type="hidden" name="id" value="<?php echo $row->id ?>">
          <input type="hidden" name="username" value="<?php echo $username ?>">

          <table class="table">
            <tr>
              <td><label for="payeeid">PayeeId:</label></td>
              <td><input type="text" class="form-control" name="payeeid" id="payeeid" placeholder="Enter Payee ID or Name here" autocomplete="off" value="<?php echo htmlentities($rlt->payeeid); ?>" required readonly></td>
            </tr>

            <tr>
              <td><label for="reference">Reference:</label></td>
              <td><input type="text" class="form-control" name="reference" id="reference" placeholder="Reference"></td>
            </tr>

            <tr>
              <td><label for="bank">Bank:</label></td>
              <td>
                <select name="bank" class="form-control" required>
                  <option value="">--select Bank--</option>
                  <?php
                    $smt = $dbh->prepare('SELECT bankname FROM bankdetails');
                    $smt->execute();
                    foreach ($smt->fetchAll() as $rw): ?>
                    <option value="<?= $rw['bankname'] ?>"><?= $rw['bankname'] ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>

            <tr>
              <td><label for="Cash">Amount:</label></td>
              <td><input type="text" class="form-control" name="amount" id="Cash" placeholder="Enter amount" value="0" required></td>
            </tr>

            <tr>
              <td><label for="paymentdate">Payment Date:</label></td>
              <td><input type="date" class="form-control" name="paymentdate" id="paymentdate" value="<?php echo $currentdate; ?>" required></td>
            </tr>

            <tr>
              <td><label for="votehead">Votehead:</label></td>
              <td>
                <select name="votehead" class="form-control" required>
                  <option value="">--select votehead--</option>
                  <?php
                    $smt = $dbh->prepare('SELECT votehead FROM voteheads');
                    $smt->execute();
                    foreach ($smt->fetchAll() as $rw): ?>
                    <option value="<?= $rw['votehead'] ?>"><?= $rw['votehead'] ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>

            <tr>
              <td><label for="description">Description:</label></td>
              <td><textarea class="form-control" name="description" id="description" placeholder="Description"></textarea></td>
            </tr>

            <tr>
              <td><label for="financialyear">Financial Year:</label></td>
              <td><input type="text" class="form-control" name="financialyear" id="financialyear" placeholder="Financial Year" value="<?php echo $financialyear; ?>" required></td>
            </tr>
          </table>

          <div class="text-center">
            <button type="submit" name="makepay_submit" class="btn btn-primary">Post</button>
          </div>

        </form>
        <?php else: ?>
          <p>Please select a valid PayeeID to make payment.</p>
        <?php endif; ?>

      </div>

    </div>
  </div>
</div>
