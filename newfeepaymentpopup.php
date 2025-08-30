<!-- Fee Payment Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="receivefeepayment" class="modal fade">
  <div class="modal-dialog" style="max-width: 650px;">
    <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
      <div class="modal-header" style="background: linear-gradient(135deg,rgb(174, 25, 201) 0%,#207cca 51%,#2989d8 100%); color: white; border-radius: 8px 8px 0 0; border-bottom: none; padding: 15px 20px;">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button" style="color: white; opacity: 0.8; text-shadow: none;">&times;</button>
        <h2 class="modal-title text-center" style="font-weight: 600; font-size: 18px;"><i class="bi bi-wallet2"></i> FEE PAYMENT</h2>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <?php if (!empty($rlt->studentadmno)): ?>
        <form method="post" enctype="multipart/form-data" action="manage-feepayments.php" style="margin-bottom: 0;">
          <input type="hidden" name="id" value="<?php echo $row->id ?>">
          <input type="hidden" name="username" value="<?php echo $username ?>">

          <div class="row">
            <div class="col-md-2">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="studentadmno" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">AdmNo</label>
                <input type="text" class="form-control" name="studentadmno" id="studentadmno" value="<?php echo $rlt->studentadmno; ?>" readonly style="background-color: #f8f9fa; border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="studentname" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Learner's Name</label>
                <input type="text" class="form-control" name="studentname" id="studentname" value="<?php echo $rlt->studentname; ?>" readonly style="background-color: #f8f9fa; border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>

            <?php
              $currentYear = date('Y');
              $stmt = $dbh->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(receiptno, '-', -1) AS UNSIGNED)) AS max_suffix FROM feepayments WHERE receiptno LIKE :yearprefix");
              $stmt->execute([':yearprefix' => $currentYear . '-%']);
              $row = $stmt->fetch(PDO::FETCH_ASSOC);
              $lastSuffix = $row['max_suffix'] ?? 0;
              $newSuffix = $lastSuffix + 1;
              $newreceiptno = sprintf("%s-%04d", $currentYear, $newSuffix);
            ?>

            <div class="col-md-3">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="receiptno" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">ReceiptNo</label>
                <input type="text" class="form-control" name="receiptno" id="receiptno" required value="<?= htmlspecialchars($newreceiptno) ?>" style="background-color: #fff8e1; font-weight: bold; color: #d35400; border: 1px solid #ffd699; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="academicyear" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Year</label>
                <input type="text" class="form-control" name="academicyear" id="academicyear" value="<?php echo $academicyear; ?>" required style="border: 1px solid #ddd; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="cash" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Amount <i class="fa fa-money" style="color: #27ae60;"></i></label>
                <input type="number" class="form-control" name="cash" id="cash" placeholder="Enter amount in Ksh" required pattern="\d*" min="0" autofocus style="border: 1px solid #2ecc71; font-weight: bold; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <div class="col-md-8">
              <div class="form-group" style="margin-bottom: 12px;">
                <label for="payer" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">
                  Payer <i class="fas fa-user-tag" style="color: #2980b9;"></i>
                </label>
                <input type="text" class="form-control" name="payer" id="payer" placeholder="Enter payer name" 
                      style="border: 1px solid #3498db; font-weight: bold; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
              </div>
            </div>
            <!-- Votehead Allocation Box -->
            <?php
              $fee_voteheads_stmt = $dbh->prepare("SELECT id, votehead FROM voteheads WHERE isfeepayment = 'Yes' ORDER BY votehead ASC");
              $fee_voteheads_stmt->execute();
              $fee_voteheads = $fee_voteheads_stmt->fetchAll(PDO::FETCH_OBJ);
            ?>
               <div class="col-md-12">
              <div class="panel panel-info">
                <div class="panel-heading text-center"><strong>Distribute Payment Per Voteheads</strong></div>
                <div class="panel-body">
                  <table class="table table-bordered table-hover">
                    <thead class="bg-info">
                        <th style="width: 60%; border-bottom: 2px solid #3498db; padding: 8px 12px; text-align: left;">Votehead</th>
                        <th style="width: 40%; border-bottom: 2px solid #3498db; padding: 8px 12px; text-align: left;">Amount (Ksh)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($fee_voteheads as $vh): ?>
                      <tr>
                        <td><?= htmlspecialchars($vh->votehead) ?></td>
                        <td>
                          <input type="number" class="form-control votehead-amount" name="votehead_amounts[<?= $vh->id ?>]" step="1" min="0" value="0" style="border: 1px solid #ddd; text-align: right; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;">
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  
                  <div class="text-right" style="padding: 10px 12px; background-color: #f8f9fa; border-top: 2px solid #eee; font-size: 13px;">
                    <strong style="color: #2c3e50;">Total Allocated: Ksh <span id="allocatedTotal" class="total-otherpay">0.00</span></strong>
                  </div>
                   <div id="amountMatchStatus" class="alert alert-danger text-right" style="display: none;">
                    <i class="fa fa-exclamation-triangle"></i> Total allocated per items must match the entered total amount.
                  </div>
                </div>
              </div>
            </div>
            <!-- End Votehead Allocation -->

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

            <div class="col-md-12">
              <div class="form-group" style="margin-bottom: 15px;">
                <label for="details" style="font-weight: 500; color: #555; display: block; margin-bottom: 5px; font-size: 13px;">Payment Details</label>
                <textarea class="form-control" name="details" id="details" placeholder="Enter payment description" style="border: 1px solid #ddd; min-height: 40px; width: 100%; padding: 6px 10px; border-radius: 3px; font-size: 13px;"></textarea>
              </div>
            </div>
          </div>

          <div class="text-center" style="margin-top: 15px;">
            <button type="submit" name="receivepay_submit" id="submitBtn" class="btn" style="background: #95a5a6; border: none; padding: 8px 25px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; box-shadow: 0 2px 5px rgba(0,0,0,0.1); color: white; border-radius: 3px; font-size: 13px; cursor: not-allowed;" disabled>
              <i class="fa fa-check-circle"></i> POST PAYMENT
            </button>
          </div>
        </form>
        <?php else: ?>
          <div class="alert alert-warning text-center" style="border-radius: 3px; background-color: #fff3cd; border-color: #ffeeba; padding: 12px; margin-bottom: 0; font-size: 13px;">
            <i class="fa fa-exclamation-triangle"></i> Please select a valid Admission Number to receive payment.
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
  function updateTotalAllocation() {
    let total = 0;
    document.querySelectorAll(".votehead-amount").forEach(input => {
      total += parseFloat(input.value) || 0;
    });

    const totalElement = document.getElementById("allocatedTotal");
    totalElement.textContent = total.toFixed(2);

    const cash = parseFloat(document.getElementById("cash").value) || 0;
    const statusElement = document.getElementById("amountMatchStatus");
    const submitBtn = document.getElementById("submitBtn");

    // Modified condition to allow zero
    if (cash >= 0 && total >= 0) {
      const difference = (cash - total).toFixed(2);
      const absDiff = Math.abs(difference).toFixed(2);

      if (Math.abs(total - cash) <= 0.01) {
        // Amounts match (including zero)
        totalElement.style.color = "#27ae60";
        statusElement.style.display = "block";
        statusElement.style.backgroundColor = "#e8f5e9";
        statusElement.style.color = "#27ae60";
        statusElement.innerHTML = `
          <i class="fa fa-check-circle"></i> Amounts match! Ready to submit.
        `;
        submitBtn.disabled = false;
        submitBtn.style.background = "linear-gradient(135deg, #27ae60 0%,#219653 100%)";
        submitBtn.style.cursor = "pointer";
      } else {
        // Amounts don't match
        totalElement.style.color = "#e74c3c";
        statusElement.style.display = "block";
        statusElement.style.backgroundColor = "#ffebee";
        statusElement.style.color = "#e74c3c";
        statusElement.innerHTML = `
          <i class="fa fa-exclamation-triangle"></i> 
          Amounts don't match! Difference: <strong>Ksh ${absDiff}</strong><br> 
          (${difference > 0 ? 'Under' : 'Over'} allocated)
          <br><small>(Allocated: Ksh ${total.toFixed(2)} | Cash: Ksh ${cash.toFixed(2)})</small>
        `;
        submitBtn.disabled = true;
        submitBtn.style.background = "#95a5a6";
        submitBtn.style.cursor = "not-allowed";
      }
    } else {
      // Negative input
      totalElement.style.color = "#000";
      statusElement.style.display = "block";
      statusElement.style.backgroundColor = "#ffebee";
      statusElement.style.color = "#e74c3c";
      statusElement.innerHTML = `
        <i class="fa fa-exclamation-triangle"></i> 
        Amounts cannot be negative!
      `;
      submitBtn.disabled = true;
      submitBtn.style.background = "#95a5a6";
      submitBtn.style.cursor = "not-allowed";
    }
  }

  // Event listeners
  document.querySelectorAll(".votehead-amount").forEach(input => {
    input.addEventListener("input", updateTotalAllocation);
  });

  document.getElementById("cash").addEventListener("input", updateTotalAllocation);

  // Initial validation
  updateTotalAllocation();

  // Final form submission validation
  document.querySelector("form").addEventListener("submit", function(e) {
    const cash = parseFloat(document.getElementById("cash").value) || 0;
    let total = 0;
    document.querySelectorAll(".votehead-amount").forEach(input => {
      total += parseFloat(input.value) || 0;
    });

    if (cash < 0 || total < 0) {
      e.preventDefault();
      Swal.fire({
        title: 'Invalid Amount',
        text: 'Amounts cannot be negative',
        icon: 'error',
        confirmButtonColor: '#e74c3c',
        confirmButtonText: 'OK'
      });
    } else if (Math.abs(cash - total) > 0.01) {
      e.preventDefault();
      Swal.fire({
        title: 'Amount Mismatch',
        html: `
          Total allocated (<strong>Ksh ${total.toFixed(2)}</strong>) must match 
          the cash entered (<strong>Ksh ${cash.toFixed(2)}</strong>).
        `,
        icon: 'error',
        confirmButtonColor: '#e74c3c',
        confirmButtonText: 'OK'
      });
    }
  });
</script>