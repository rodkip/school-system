<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title text-center">New Staff</h2>
      </div>

      <div class="modal-body">
        <div class="panel panel-primary">
          <div class="panel-body">

            <form method="post" action="">
              <table class="table">

                <tr>
                  <td><label for="staffidno">IdNo:</label></td>
                  <td>
                    <input type="text" name="staffidno" id="staffidno" class="form-control" required placeholder="Enter IdNo here" onblur="admnoAvailability()">
                    <span id="user-availability-status1" style="font-size: 12px;"></span>
                  </td>
                  <td><label for="staffcontact">Staff Contact:</label></td>
                  <td colspan="3">
                    <input type="text" name="staffcontact" id="staffcontact" class="form-control" placeholder="Staff Contact" value="0">
                  </td>
                </tr>

                <tr>
                  <td><label for="staffname">Full Names:</label></td>
                  <td colspan="2">
                    <input type="text" name="staffname" id="staffname" class="form-control" required placeholder="Enter Staff name">
                  </td>
                </tr>

                <tr>
                  <td><label for="stafftitle">Staff Title:</label></td>
                  <td>
                    <select name="stafftitle" class="form-control" required>
                      <option value="">--select stafftitle--</option>
                      <option value="Teacher">Teacher</option>
                      <option value="DeputyHead">DeputyHead</option>
                      <option value="HeadTeacher">HeadTeacher</option>
                      <option value="Driver">Driver</option>
                      <option value="Security">Security</option>
                      <option value="Cook">Cook</option>
                      <option value="Cleaner">Cleaner</option>
                      <option value="Groundsman">Groundsman</option>
                      <option value="Secretary">Secretary</option>
                      <option value="Accountclerk">Accounts Clerk</option>
                      <option value="Bursar">Bursar</option>
                      <option value="Others">Others</option>
                    </select>
                  </td>
                  <td><label for="gender">Gender:</label></td>
                  <td>
                    <select name="gender" class="form-control" required>
                      <option value="">--select gender--</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><label for="maritalstatus">Marital Status:</label></td>
                  <td>
                    <select name="maritalstatus" class="form-control" required>
                      <option value="">--select marital status--</option>
                      <option value="Single">Single</option>
                      <option value="Married">Married</option>
                      <option value="Divorced">Divorced</option>
                      <option value="Widowed">Widowed</option>
                    </select>
                  </td>
                  <td><label for="educationlevel">Education Level:</label></td>
                  <td>
                    <select name="educationlevel" class="form-control" required>
                      <option value="">--select education level--</option>
                      <option value="Primary">Primary</option>
                      <option value="Secondary">Secondary</option>
                      <option value="Diploma">Diploma</option>
                      <option value="Bachelor">Bachelor</option>
                      <option value="Master">Master</option>
                      <option value="PhD">PhD</option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <td><label for="healthissue">Health Issue:</label></td>
                  <td colspan="3">
                    <textarea name="healthissue" id="healthissue" class="form-control" rows="3" placeholder="Enter health issue (if any)"></textarea>
                  </td>
                </tr>

                <tr>
                  <td><label for="experience">Experience:</label></td>
                  <td colspan="3">
                    <textarea name="experience" id="experience" class="form-control" rows="3" placeholder="Enter years of experience and details"></textarea>
                  </td>
                </tr>

                <tr>
                  <td><label for="bank">Salary Bank:</label></td>
                  <td>
                    <?php
                    $smt = $dbh->prepare("SELECT bankname FROM bankdetails");
                    $smt->execute();
                    $data = $smt->fetchAll();
                    ?>
                    <select name="bank" class="form-control">
                      <option value="">--select Bank--</option>
                      <?php foreach ($data as $row): ?>
                        <option value="<?= $row['bankname'] ?>"><?= $row['bankname'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><label for="bankaccno">Bank AccNo:</label></td>
                  <td>
                    <input type="text" name="bankaccno" id="bankaccno" class="form-control" placeholder="Account No" value="0">
                  </td>
                </tr>

                <tr>
                  <td><label for="nssfaccno">NSSF Acc No:</label></td>
                  <td>
                    <input type="text" name="nssfaccno" id="nssfaccno" class="form-control" placeholder="NSSF Acc No" value="0">
                  </td>
                  <td><label for="nhifaccno">SHA Acc No:</label></td>
                  <td>
                    <input type="text" name="nhifaccno" id="nhifaccno" class="form-control" placeholder="SHA Acc No" value="0">
                  </td>
                </tr>

              </table>

              <div class="text-center">
                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
              </div>
            </form>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
