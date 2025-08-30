<!-- otherItems Payment Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="receiveotherpayment" class="modal fade">
  <div class="modal-dialog" style="max-width: 650px;">
    <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: linear-gradient(135deg,rgb(201, 99, 16) 0%,#207cca 51%,#2989d8 100%); color: white; border-radius: 8px 8px 0 0; border-bottom: none; padding: 15px 20px;">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button" style="color: white; opacity: 0.8; text-shadow: none;">&times;</button>
        <h2 class="modal-title text-center" style="font-weight: 600; font-size: 18px;"><i class="bi bi-credit-card-2-back"></i> OTHER-Items Payment</h2>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <?php if (!empty($rlt->studentadmno)): ?>
        <form method="post" enctype="multipart/form-data" action="manage-feepayments.php" style="margin-bottom: 0;">
          <input type="hidden" name="id" value="<?php echo $row->id ?>">
          <input type="hidden" name="username" value="<?php echo $username ?>">



         <div class="row">
            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="studentadmno" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Admission Number</label>
                <input type="text" class="form-control" name="studentadmno" id="studentadmno" value="<?php echo $rlt->studentadmno; ?>" readonly style="background-color: #f8f9fa; border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="studentname" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Learner's Name</label>
                <input type="text" class="form-control" name="studentname" id="studentname" value="<?php echo $rlt->studentname; ?>" readonly style="background-color: #f8f9fa; border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>



            <?php
              $currentYear = date('Y');

              // Prepare and execute the query to get the latest suffix for the current year
              $stmtt = $dbh->prepare("
                  SELECT MAX(CAST(SUBSTRING_INDEX(receiptno, '-', -1) AS UNSIGNED)) AS max_suffix 
                  FROM otheritemspayments 
                  WHERE receiptno LIKE :yearprefix
              ");

              // Use 'OP2025-%' instead of just '2025-%'
              $receiptPrefix = "OP{$currentYear}-%";
              $stmtt->execute([':yearprefix' => $receiptPrefix]);

              $roww = $stmtt->fetch(PDO::FETCH_ASSOC);

              // Generate new receipt number
              $lastSuffix = $roww['max_suffix'] ?? 0;
              $newSuffix = $lastSuffix + 1;
              $newreceiptno = sprintf("OP%s-%04d", $currentYear, $newSuffix);
            ?>

           <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="receiptno" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Receipt Number</label>
                <input type="text" class="form-control" name="receiptno" id="receiptno" required value="<?= htmlspecialchars($newreceiptno) ?>" style="background-color: #fff8e1; font-weight: bold; color: #d35400; border: 1px solid #ffd699; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="totalotherpayamount" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Amount <i class="fa fa-money" style="color: #27ae60;"></i></label>
                <input type="number" class="form-control" name="totalotherpayamount" id="totalotherpayamount" placeholder="Enter amount in Ksh" required pattern="\d*" autofocus style="border: 1px solid #2ecc71; font-weight: bold; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>


            <?php
              $otherpayitems_stmt = $dbh->prepare("SELECT id, otherpayitemname FROM otherpayitems ORDER BY otherpayitemname ASC");
              $otherpayitems_stmt->execute();
              $otherpayitems = $otherpayitems_stmt->fetchAll(PDO::FETCH_OBJ);
            ?>
            <div class="col-md-12">
              <div class="panel panel-info">
                <div class="panel-heading text-center"><strong>Distribute Payment by Other Pay Items</strong></div>
                <div class="panel-body">
                  <table class="table table-bordered table-hover">
                    <thead class="bg-info">
                      <tr>
                        <th>Items</th>
                        <th>Amount (Ksh)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($otherpayitems as $vh): ?>
                      <tr>
                        <td><?= htmlspecialchars($vh->otherpayitemname) ?></td>
                        <td>
                          <input type="number" class="form-control itemized-otherpay" name="itemizedotherpay[<?= $vh->id ?>]" id="itemizedotherpay_<?= $vh->id ?>" placeholder="Enter amount" pattern="\d*" min="0" step="0.01" value="0">
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <div class="text-right">
                    <strong>Other Pay Items Total: Ksh <span id="itemizedTotal" class="total-otherpay">0.00</span></strong>
                  </div>
                  <div id="distribution-mismatch-warning" class="alert alert-danger text-right" style="display: none;">
                    <i class="fa fa-exclamation-triangle"></i> Total allocated per items must match the entered total amount.
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="reference" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Reference (Mpesa Code, if any)</label>
                <input type="text" class="form-control" name="reference" id="reference" placeholder="Reference (optional)" style="border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="bank" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Payment Mode <i class="fa fa-bank" style="color: #9b59b6;"></i></label>
                <select name="bank" class="form-control" required style="border: 1px solid #ddd; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=\"%239b59b6\" height=\"24\" viewBox=\"0 0 24 24\" width=\"24\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M7 10l5 5 5-5z\"/></svg>'); background-repeat: no-repeat; background-position: right 10px center; padding-right: 30px; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
                  <option value="">-- Select Payment Mode --</option>
                  <?php
                  $smt = $dbh->prepare('SELECT bankname FROM bankdetails');
                  $smt->execute();
                  foreach ($smt->fetchAll() as $rw): ?>
                    <option value="<?= $rw["bankname"] ?>"><?= $rw["bankname"] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="bankpaymentdate" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Bank Payment Date</label>
                <input type="date" class="form-control" name="bankpaymentdate" id="bankpaymentdate" value="<?php echo $currentdate; ?>" required style="border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="paymentdate" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Receipt Date</label>
                <input type="date" class="form-control" name="paymentdate" id="paymentdate" value="<?php echo $currentdate; ?>" required style="border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="academicyear" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Academic Year</label>
                <input type="text" class="form-control" name="academicyear" id="academicyear" value="<?php echo $academicyear; ?>" required style="border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group" style="margin-bottom: 15px;">
                <label for="details" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Payment Details</label>
                <textarea class="form-control" name="details" id="details" placeholder="Enter payment description" style="border: 1px solid #ddd; min-height: 70px; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;"></textarea>
              </div>
            </div>
          </div>


          <div class="text-center mt-3">
            <button type="submit" name="receiveotherpay_submit" class="btn btn-primary btn-lg">
              <i class="fa fa-check-circle"></i> Post Payment
            </button>
          </div>
        </form>
        <?php else: ?>
          <div class="alert alert-warning text-center">
            <i class="fa fa-exclamation-circle"></i> Please select a valid AdmNo to receive payment.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
  .receipt-no-input {
    background-color: #f5f5dc;
    font-weight: bold;
    color: #000;
  }

  .total-otherpay {
    font-size: 1.1em;
    color: #2a6496;
    font-weight: bold;
  }

  .itemized-otherpay:focus {
    border-color: #66afe9;
    box-shadow: 0 0 8px rgba(102, 175, 233, 0.6);
  }

  .btn-lg {
    padding: 10px 30px;
    font-size: 1.1em;
  }

  .table-hover tbody tr:hover {
    background-color: #f5f5f5;
  }
</style>

<script>
  const totalAmountInput = document.getElementById("totalotherpayamount");
  const itemInputs = document.querySelectorAll(".itemized-otherpay");
  const submitButton = document.querySelector("button[name='receiveotherpay_submit']");
  const totalDisplay = document.getElementById("itemizedTotal");
  const warningMsg = document.getElementById("distribution-mismatch-warning");

  function validateAmounts() {
    const totalAmount = parseFloat(totalAmountInput.value) || 0;
    let allocated = 0;

    itemInputs.forEach(input => {
      allocated += parseFloat(input.value) || 0;
    });

    const roundedTotal = Math.round(totalAmount * 100) / 100;
    const roundedAllocated = Math.round(allocated * 100) / 100;
    const difference = Math.abs(roundedAllocated - roundedTotal).toFixed(2);

    totalDisplay.textContent = roundedAllocated.toFixed(2);

    if (totalAmountInput.value.trim() === "" || totalAmount <= 0) {
      submitButton.disabled = true;
      warningMsg.style.display = 'block';
      warningMsg.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Please enter a valid total amount received.';
    } else if (roundedTotal !== roundedAllocated) {
      submitButton.disabled = true;
      warningMsg.style.display = 'block';
      warningMsg.innerHTML = `
        <i class="fa fa-exclamation-triangle"></i>
        Amounts don't match! Difference: <strong>Ksh ${difference}</strong><br>
        <small>(Allocated: Ksh ${roundedAllocated.toFixed(2)} | Expected: Ksh ${roundedTotal.toFixed(2)})</small>
      `;
    } else {
      submitButton.disabled = false;
      warningMsg.style.display = 'none';
      warningMsg.innerHTML = '';
    }
  }

  totalAmountInput.addEventListener("input", validateAmounts);
  itemInputs.forEach(input => input.addEventListener("input", validateAmounts));
  document.addEventListener("DOMContentLoaded", validateAmounts);
</script>

