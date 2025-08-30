<div id="newpaymentaccount" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">    

      <!-- Modal Header -->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title text-center">New Payments Account</h2>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <form method="POST" enctype="multipart/form-data">
          <table class="table">
            <tr>
              <td><label for="bankname">Bank Name:</label></td>
              <td>
                <input type="text" class="form-control" name="bankname" id="bankname" required placeholder="Bank name" value="<?php echo $bankname; ?>">
              </td>
            </tr>

            <tr>
              <td><label for="accountno">Account No:</label></td>
              <td>
                <input type="text" class="form-control" name="accountno" id="accountno" placeholder="Account No" value="<?php echo $accountno; ?>">
              </td>
            </tr>

            <tr>
              <td><label for="accountname">Account Name:</label></td>
              <td>
                <input type="text" class="form-control" name="accountname" id="accountname" placeholder="Account Name" value="<?php echo $accountname; ?>">
              </td>
            </tr>

            <tr>
              <td><label for="accountdescription">Account Description:</label></td>
              <td>
                <textarea class="form-control" name="accountdescription" id="accountdescription"><?php echo $accountdescription; ?></textarea>
              </td>
            </tr>

            <tr>
              <td colspan="2" class="text-center">
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
              </td>
            </tr>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>
