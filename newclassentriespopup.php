<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"> 
  <div class="modal-dialog"> 
    <div class="modal-content">
      <div class="modal-header"> 
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title text-center">
          <i class="fas fa-user-graduate me-2" style="color: #0d6efd;"></i>
          Learner admission to Grade
        </h2>
      </div> 

      <div class="modal-body">
        <div class="panel panel-primary"> 
          <div class="panel panel-default">
            <div class="form-group">
              <table class="table">
                <!-- Student AdmNo -->
                <tr>
                  <td><label for="studentadmno">Student AdmNo:</label></td>
                  <td>
                    <input type="text" class="form-control" name="studentadmno" id="studentadmno"
                      placeholder="Enter AdmNo or Name here"
                      list="studentdetails-list" autocomplete="off" required autofocus
                      onblur="validateStudentAdmNo()" value="<?= $rlt->studentadmno; ?>">

                    <datalist id="studentdetails-list">
                      <?php
                      $smt = $dbh->prepare('SELECT * FROM studentdetails ORDER BY studentadmno DESC');
                      $smt->execute();
                      $data = $smt->fetchAll();
                      $students = [];
                      foreach ($data as $rw): ?>
                        <option value="<?= $rw["studentadmno"] ?>"><?= $rw["studentadmno"] ?> - <?= $rw["studentname"] ?></option>
                        <?php $students[$rw["studentadmno"]] = $rw["studentname"]; ?>
                      <?php endforeach; ?>
                    </datalist>

                    <span id="displaystudentname" style="font-size:12px; color:red;"></span>

                    <script>
                      var studentDetails = <?= json_encode($students); ?>;
                      function validateStudentAdmNo() {
                        let admNoInput = document.getElementById("studentadmno");
                        let studentNameDisplay = document.getElementById("displaystudentname");
                        if (studentDetails[admNoInput.value]) {
                          studentNameDisplay.textContent = "Learner Name: " + studentDetails[admNoInput.value];
                          studentNameDisplay.style.color = "blue";
                        } else {
                          studentNameDisplay.textContent = "Error: Admission number not found!";
                          studentNameDisplay.style.color = "red";
                          admNoInput.value = "";
                          admNoInput.focus();
                        }
                      }
                    </script>
                  </td>
                </tr>

                <!-- Grade Full Name -->
                <tr>
                  <td><label for="gradefullname">Grade Full Name:</label></td>
                  <td>
                    <?php
                    $smt = $dbh->prepare('SELECT gradefullname FROM classdetails ORDER BY gradefullname DESC');
                    $smt->execute();
                    $grades = $smt->fetchAll();
                    ?>
                    <select name="gradefullname" class="form-control" required>
                      <option value="">--select gradefullname--</option>
                      <?php foreach ($grades as $rw): ?>
                        <option value="<?= $rw["gradefullname"] ?>"><?= $rw["gradefullname"] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>

                <!-- Entry Term -->
                <tr>
                  <td><label for="entryterm">Entry Term:</label></td>
                  <td>
                    <select name="entryterm" class="form-control">
                      <option value="Firstterm">1st Term</option>
                      <option value="Secondterm">2nd Term</option>
                      <option value="Thirdterm">3rd Term</option>
                    </select>
                  </td>
                </tr>

                <!-- Fee Treatment -->
                <tr>
                  <td><label for="feetreatment">Fee Treatment:</label></td>
                  <td>
                    <select name="feetreatment" class="form-control" required>                   
                      <option value="">--select Fee Treatment--</option>
                      <?php
                        $smt = $dbh->prepare('SELECT treatment FROM feetreatmentrates');
                        $smt->execute();
                        foreach ($smt->fetchAll() as $rw): ?>
                        <option value="<?= $rw['treatment'] ?>"><?= $rw['treatment'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>

                <!-- Child Treatment -->
                <tr>
                  <td><label for="childtreatment">Child Treatment:</label></td>
                  <td>
                    <select name="childtreatment" class="form-control" required>
                     <option value="">--Select Child Treatment--</option>
                      <?php
                        $smt = $dbh->prepare('SELECT childtreatment FROM childtreatmentrates');
                        $smt->execute();
                        foreach ($smt->fetchAll() as $rw): ?>
                        <option value="<?= $rw['childtreatment'] ?>"><?= $rw['childtreatment'] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>

                <!-- Stream -->
                <tr>
                  <td><label for="stream">Class Stream:</label></td>
                  <td>
                    <?php
                    $smt = $dbh->prepare('SELECT streamname FROM streams ORDER BY streamname ASC');
                    $smt->execute();
                    $streams = $smt->fetchAll();
                    ?>
                    <select name="stream" class="form-control">
                      <option value="">--select stream--</option>
                      <?php foreach ($streams as $rw): ?>
                        <option value="<?= $rw["streamname"] ?>"><?= $rw["streamname"] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>

                <!-- Boarding -->
                <tr>
                  <td><label for="boarding">Day or Border?:</label></td>
                  <td>
                    <select id="boarding" name="boarding" class="form-control" required onchange="toggleDormitoryField()">
                      <option value="">-- Select --</option>
                      <option value="Day">Day</option>
                      <option value="Border">Border</option>
                    </select>
                  </td>
                </tr>

                <!-- Dormitory -->
                <tr id="dormitoryRow" style="display:none;">
                  <td><label for="dorm">Dorm:</label></td>
                  <td>
                    <?php
                    $smt = $dbh->prepare('SELECT * FROM dormitoriesdetails ORDER BY dormitoryname DESC');
                    $smt->execute();
                    $dorms = $smt->fetchAll();
                    ?>
                    <select name="dorm" class="form-control">
                      <option value="">--select dorm if board--</option>
                      <?php foreach ($dorms as $rw): ?>
                        <option value="<?= $rw["dormid"] ?>"><?= $rw["dormitoryname"] ?></option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                </tr>
                <!-- Fee Waiver -->
                <tr>
                  <td><label for="feewaiver"> Fee Waiver</label>:</label></td>
                  <td>
                    <select name="feewaiver" class="form-control" required>
                      <option value="No">No</option>
                      <option value="Yes">Yes</option>
                    </select>
                  </td>
                </tr>
              

              </table>
                <script>
                  function toggleDormitoryField() {
                    const status = document.getElementById('boarding').value;
                    document.getElementById('dormitoryRow').style.display = (status === 'Border') ? 'table-row' : 'none';
                  }
                </script>
            </div>
            <div class="text-center">
              <button type="submit" name="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </div> 
      </div> 
    </div>
  </div>
</div>
