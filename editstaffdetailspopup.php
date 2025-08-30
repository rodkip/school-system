<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal<?php echo ($row->id); ?>" class="modal fade"> 
    <div class="modal-dialog"> <!-- Increased modal size for better spacing -->
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"> <!-- Added background color -->
                <button aria-hidden="true" data-dismiss="modal" class="close text-white" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Edit Staff Details</h2> 
            </div> 
            <div class="modal-body p-4"> <!-- Added padding to modal body -->
                <!-- Actual Form --> 
                <div class="panel panel-primary">                
                    <div class="row">
                        <div class="col-lg-12">            
                            <!-- Advanced Tables -->
                            <div class="panel panel-default">                     
                                <div class="popup">                   
                                    <input type="hidden" name="id" value="<?php echo $row->id; ?>">
                                    <div class="form-group"> 
                                        <br>
                                        <center>                                
                                            <table class="table table-bordered" style="width: 100%;"> <!-- Full width table -->
                                                <!-- Staff ID and Name -->
                                                <tr>
                                                    <td style="width: 30%; padding: 10px;"> <!-- Added padding -->
                                                        <label for="staffidno">Staff IdNo:</label>
                                                    </td>
                                                    <td style="width: 70%; padding: 10px;"> <!-- Added padding -->
                                                        <input type="text" name="editstaffidno" id="staffidno" required="required" placeholder="IdNo" value="<?php echo $row->staffidno; ?>" class="form-control" style="width: 100%;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="staffname">Staff Names:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <input type="text" class="form-control" name="editstaffname" id="staffname" required="required" placeholder="Enter Staff name" value="<?php echo $row->staffname; ?>" style="width: 100%;">
                                                    </td>
                                                </tr>

                                                <!-- Gender and Marital Status -->
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="gender">Gender:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <select name="editgender" class="form-control" required="required" style="width: 100%;"> 
                                                            <option value="<?php echo $row->gender; ?>"><?php echo $row->gender; ?></option>
                                                            <option value="Male">Male</option> 
                                                            <option value="Female">Female</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="maritalstatus">Marital Status:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <select name="editmaritalstatus" class="form-control" required="required" style="width: 100%;"> 
                                                            <option value="<?php echo $row->maritalstatus; ?>"><?php echo $row->maritalstatus; ?></option>
                                                            <option value="Single">Single</option>
                                                            <option value="Married">Married</option>
                                                            <option value="Divorced">Divorced</option>
                                                            <option value="Widowed">Widowed</option>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Staff Title and Education Level -->
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="editstafftitle">Staff Title:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <select name="editstafftitle" class="form-control" required="required" style="width: 100%;"> 
                                                            <option value="<?php echo $row->stafftitle; ?>"><?php echo $row->stafftitle; ?></option>
                                                            <option value="Teacher">Teacher</option> 
                                                            <option value="DeputyHead">DeputyHead</option>
                                                            <option value="HeadTeacher">HeadTeacher</option> 
                                                            <option value="Driver">Driver</option>
                                                            <option value="Security">Security</option>
                                                            <option value="Cook">Cook</option> 
                                                            <option value="Cleaner">Cleaner</option>
                                                            <option value="Groundsman">Groundsman</option> 
                                                            <option value="Secretary">Secretary</option> 
                                                            <option value="Accountclerk">Account Clerk</option>
                                                            <option value="Bursar">Bursar</option>                               
                                                            <option value="Others">Others</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="educationlevel">Education Level:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <select name="editeducationlevel" class="form-control" required="required" style="width: 100%;"> 
                                                            <option value="<?php echo $row->educationlevel; ?>"><?php echo $row->educationlevel; ?></option>
                                                            <option value="Primary">Primary</option>
                                                            <option value="Secondary">Secondary</option>
                                                            <option value="Diploma">Diploma</option>
                                                            <option value="Bachelor">Bachelor</option>
                                                            <option value="Master">Master</option>
                                                            <option value="PhD">PhD</option>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Bank Details -->
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="bank">Bank:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <?php
                                                        $smt = $dbh->prepare('SELECT bankname from bankdetails');
                                                        $smt->execute();
                                                        $data = $smt->fetchAll();
                                                        ?> 
                                                        <select name="editbank" class="form-control" style="width: 100%;"> 
                                                            <option value="<?php echo $row->bank; ?>"><?php echo $row->bank; ?></option>
                                                            <?php foreach ($data as $rw): ?>
                                                                <option value="<?= $rw["bankname"] ?>"><?= $rw["bankname"] ?></option> 
                                                            <?php endforeach ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="bankaccno">Bank AccNo:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <input type="text" class="form-control" name="editbankaccno" id="bankaccno" placeholder="Enter bankaccno here" value="<?php echo $row->bankaccno; ?>" style="width: 100%;">
                                                    </td>
                                                </tr>

                                                <!-- NSSF and NHIF Details -->
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="nssfaccno">NSSF AccNo:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <input type="text" class="form-control" name="editnssfaccno" id="nssfaccno" placeholder="NSSF No" value="<?php echo $row->nssfaccno; ?>" style="width: 100%;">     
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="nhifaccno">NHIF AccNo:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <input type="text" class="form-control" name="editnhifaccno" id="nhifaccno" placeholder="NHIF No" value="<?php echo $row->nhifaccno; ?>" style="width: 100%;">                        
                                                    </td>
                                                </tr>

                                                <!-- Staff Contact and Experience -->
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="staffcontact">Staff Contact:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <input type="text" class="form-control" name="editstaffcontact" id="staffcontact" placeholder="Staff Contact" value="<?php echo $row->staffcontact; ?>" style="width: 100%;">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="experience">Experience:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <textarea class="form-control" name="editexperience" id="experience" placeholder="Enter experience details" style="width: 100%;"><?php echo $row->experience; ?></textarea>
                                                    </td>
                                                </tr>

                                                <!-- Health Issue -->
                                                <tr>
                                                    <td style="padding: 10px;">
                                                        <label for="healthissue">Health Issue:</label>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <textarea class="form-control" name="edithealthissue" id="healthissue" placeholder="Enter health issue (if any)" style="width: 100%;"><?php echo $row->healthissue; ?></textarea>
                                                    </td>
                                                </tr>
                                            </table>
                                        </center>
                                    </div>
                                    <div class="text-center mt-4"> <!-- Centered button with margin -->
                                        <button type="submit" name="update_submit" class="btn btn-primary btn-lg">Update</button> <!-- Larger button -->
                                    </div>
                                </div>
                            </div> 
                        </div>       
                    </div> 
                </div>                
            </div> 
        </div>
    </div>
</div>