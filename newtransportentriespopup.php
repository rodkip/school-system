<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">  
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title text-center">
                <i class="fas fa-bus me-2" style="color: #198754;"></i>
                School Transport Entry
                </h2>

            </div> 
            <div class="modal-body">
            <!-- actual form --> 
                <div class="panel panel-primary">                
                    <div class="row">
                        <div class="col-lg-12">            
                            <!-- Advanced Tables -->
                            <div class="panel panel-default">                     
                                <div class="popup">                   
                                    <form method="post" enctype="multipart/form-data" action="manage-transportentries.php"> 
                                        <div class="form-group">                                
                                            <table class="table">
                                                <tr>
                                                    <td>
                                                        <label for="studentadmno">Learner AdmNo:</label></td><td>
                                                        <input type="text" class="form-control" name="studentadmno" id="studentadmno" 
                                                        placeholder="Enter AdmNo or Name here" 
                                                        list="studentdetails-list" autocomplete="off" required autofocus 
                                                        onBlur="validateStudentAdmNo()" value="<?php echo $rlt->studentadmno; ?>">                                     
                                                        <datalist id="studentdetails-list">
                                                            <?php
                                                            // Fetch student details from the database
                                                            $smt = $dbh->prepare('SELECT * FROM studentdetails ORDER BY studentadmno DESC');
                                                            $smt->execute();
                                                            $data = $smt->fetchAll();
                                                            $students = [];
                                                            ?>
                                                            <?php foreach ($data as $rw): ?>
                                                                <option value="<?= $rw["studentadmno"] ?>">
                                                                    <?= $rw["studentadmno"] ?> - <?= $rw["studentname"] ?>
                                                                </option>
                                                                <?php $students[$rw["studentadmno"]] = $rw["studentname"]; ?>
                                                            <?php endforeach; ?>
                                                        </datalist>
                                                        <span id="displaystudentname" style="font-size:12px; color:red;"></span>
                                                        <script>
                                                            // Store PHP student details in JavaScript
                                                            var studentDetails = <?php echo json_encode($students); ?>;

                                                            function validateStudentAdmNo() {
                                                                let admNoInput = document.getElementById("studentadmno");
                                                                let studentNameDisplay = document.getElementById("displaystudentname");

                                                                // Check if admission number exists in the list
                                                                if (studentDetails[admNoInput.value]) {
                                                                    studentNameDisplay.textContent = "Learner Name: " + studentDetails[admNoInput.value];
                                                                    studentNameDisplay.style.color = "blue"; // Set to blue if valid
                                                                } else {
                                                                    studentNameDisplay.textContent = "Error: Admission number not found!";
                                                                    studentNameDisplay.style.color = "red";
                                                                    admNoInput.value = ""; // Clear the input field
                                                                    admNoInput.focus(); // Refocus the input field
                                                                }
                                                            }
                                                        </script>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="stagefullname">Stage FullName:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="stagefullname" id="stagefullname" 
                                                            placeholder="Enter Stage FullName here" list="stagefullname-list" autocomplete="off" required>
                                                        <datalist id="stagefullname-list">
                                                            <?php
                                                            $smt = $dbh->prepare('SELECT stagefullname FROM transportstructure ORDER BY stagefullname DESC');
                                                            $smt->execute();
                                                            $data = $smt->fetchAll();
                                                            ?>
                                                            <?php foreach ($data as $rw): ?>
                                                                <option value="<?= $rw["stagefullname"] ?>">
                                                                    <?= $rw["stagefullname"] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </datalist>
                                                    </td>
                                                </tr>    
                                                 <!-- Fee Treatment -->
                                                <tr>
                                                <td><label for="transporttreatment">Transport Treatment:</label></td>
                                                <td>
                                                    <select name="transporttreatment" class="form-control" required>                   
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


                                                <!-- Fee Waiver -->
                                                <tr>
                                                <td><label for="transportwaiver"> Transport Waiver?</label>:</label></td>
                                                <td>
                                                    <select name="transportwaiver" class="form-control" required>
                                                    <option value="No">No Waiver</option>
                                                    <option value="Yes">Has Waiver</option>
                                                    </select>
                                                </td>
                                                </tr>
                                                <tr>
                                                    <td >
                                                        <label>Transport Terms:</label>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="term[]" value="FirstTerm"> First Term
                                                        <input type="checkbox" name="term[]" value="SecondTerm"> Second Term
                                                        <input type="checkbox" name="term[]" value="ThirdTerm"> Third Term
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                         <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                      
                                    </form>
                                </div> 
                            </div> 
                        </div> 
                    </div>       
                </div> 
            </div> 
        </div>
    </div>
</div>
