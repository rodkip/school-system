<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h2 class="modal-title text-center w-100">New Payroll Entry</h2>
                <button aria-hidden="true" data-dismiss="modal" class="close text-white" type="button">&times;</button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form method="post" enctype="multipart/form-data" action="manage-feestructure.php">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <table class="table table-bordered">
                                        <tr>
                                        <td>
    <label for="staffidno">Staff ID No:</label>
</td>
<td>
    <input type="text" class="form-control" name="staffidno" id="staffidno" 
           placeholder="Enter Staff ID or Name here" 
           list="staffdetails-list" autocomplete="off" required autofocus 
           onBlur="validateStaffIdNo()" value="<?php echo $rlt->staffidno; ?>">
    <datalist id="staffdetails-list">
        <?php
        // Fetch staff details from the database
        $smt = $dbh->prepare('SELECT * FROM staffdetails ORDER BY staffidno DESC');
        $smt->execute();
        $data = $smt->fetchAll();
        $staff = [];
        ?>
        <?php foreach ($data as $rw): ?>
            <option value="<?= $rw["staffidno"] ?>">
                <?= $rw["staffidno"] ?> - <?= $rw["staffname"] ?>
            </option>
            <?php $staff[$rw["staffidno"]] = $rw["staffname"]; ?>
        <?php endforeach; ?>
    </datalist>
    <span id="displaystaffname" style="font-size:12px; color:red;"></span>
    <script>
        // Store PHP staff details in JavaScript
        var staffDetails = <?php echo json_encode($staff); ?>;

        function validateStaffIdNo() {
            let staffIdInput = document.getElementById("staffidno");
            let staffNameDisplay = document.getElementById("displaystaffname");

            // Check if staff ID exists in the list
            if (staffDetails[staffIdInput.value]) {
                staffNameDisplay.textContent = "Staff Name: " + staffDetails[staffIdInput.value];
                staffNameDisplay.style.color = "blue"; // Set to blue if valid
            } else {
                staffNameDisplay.textContent = "Error: Staff ID not found!";
                staffNameDisplay.style.color = "red";
                staffIdInput.value = ""; // Clear the input field
                staffIdInput.focus(); // Refocus the input field
            }
        }
    </script>
</td>
                                        </tr>
                                        <tr>
                                            <td><label for="payrollserialno">Payroll Serial No:</label></td>
                                            <td>
                                                <select name="payrollserialno" class="form-control">
                                                    <option value="">-- Select Payroll Serial No --</option>
                                                    <?php
                                                    $smt = $dbh->prepare('SELECT payrollserialno, id FROM payrolldetails ORDER BY id DESC');
                                                    $smt->execute();
                                                    $data = $smt->fetchAll();
                                                    foreach ($data as $rw) {
                                                        echo '<option value="' . $rw["payrollserialno"] . '">' . $rw["payrollserialno"] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="basicpay">Basic Pay:</label></td>
                                            <td>
                                                <input type="text" name="basicpay" id="basicpay" required class="form-control" placeholder="Basic Pay" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="houseallowance">House Allowance:</label></td>
                                            <td>
                                                <input type="text" name="houseallowance" id="houseallowance" class="form-control" placeholder="House Allowance" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="respallowance">Responsibility Allowance:</label></td>
                                            <td>
                                                <input type="text" name="respallowance" id="respallowance" class="form-control" placeholder="Responsibility Allowance" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="nhifdeduction">SHA Deduction:</label></td>
                                            <td>
                                                <input type="text" name="nhifdeduction" id="nhifdeduction" class="form-control" placeholder="NHIF Deduction" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="nssfdeduction">NSSF Deduction:</label></td>
                                            <td>
                                                <input type="text" name="nssfdeduction" id="nssfdeduction" class="form-control" placeholder="NSSF Deduction" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="teacherswelfarededuction">Teachers Welfare Deduction:</label></td>
                                            <td>
                                                <input type="text" name="teacherswelfarededuction" id="teacherswelfarededuction" class="form-control" placeholder="Teachers Welfare Deduction" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="staffwelfarededuction">Staff Welfare Deduction:</label></td>
                                            <td>
                                                <input type="text" name="staffwelfarededuction" id="staffwelfarededuction" class="form-control" placeholder="Staff Welfare Deduction" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="feesdeduction">Fees Deduction:</label></td>
                                            <td>
                                                <input type="text" name="feesdeduction" id="feesdeduction" class="form-control" placeholder="Fees Deduction" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="advancededuction">Advance Deduction:</label></td>
                                            <td>
                                                <input type="text" name="advancededuction" id="advancededuction" class="form-control" placeholder="Advance Deduction" value="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="othersdeduction">Others Deduction:</label></td>
                                            <td>
                                                <input type="text" name="othersdeduction" id="othersdeduction" class="form-control" placeholder="Others Deduction" value="0">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="submit" class="btn btn-primary btn-lg">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>