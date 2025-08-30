<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="otherstudentdetails<?php echo $cnt; ?>" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h2 class="modal-title text-center">Learner Registration Details</h2>
                <button aria-hidden="true" data-dismiss="modal" class="close text-white" type="button">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Left Column: Student Details -->
                    <div class="card-body">
                    
                                    <!-- Personal Details -->
                                    <div class="section mb-4">
                                        <h4 class="text-primary mb-3">Personal Details</h4>
                                        <table class="table table-striped table-bordered">
                                            <tr>
                                                <td><strong>Name:</strong></td>
                                                <td><?php echo htmlspecialchars($row->studentname, ENT_QUOTES, 'UTF-8'); ?> | <b>AdmNo:</b> <?php echo htmlspecialchars($row->studentadmno, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td rowspan="5" class="text-center align-middle" style="width: 200px; height: 200px;">
                                                    <?php if (!empty($row->pfpicname) && file_exists('pfpics/' . $row->pfpicname)) { ?>
                                                        <img src="pfpics/<?php echo htmlspecialchars($row->pfpicname, ENT_QUOTES, 'UTF-8'); ?>" 
                                                             alt="Profile Picture" 
                                                             class="img-fluid shadow-lg" 
                                                             style="width: 100%; height: 100%; object-fit: cover; border: 3px solid #007bff; border-radius: 10px;">
                                                    <?php } else { ?>
                                                        <p class="text-muted">No photo available.</p>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gender:</strong></td>
                                                <td><?php echo htmlspecialchars($row->gender, ENT_QUOTES, 'UTF-8'); ?> | DOB: <?php echo htmlspecialchars($row->dateofbirth, ENT_QUOTES, 'UTF-8'); ?>
                                                    (<span class="text-success">
                                                        <?php echo round((time() - strtotime($row->dateofbirth)) / (3600 * 24 * 365.25)); ?> Years
                                                    </span>)
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Birth Cert No:</strong></td>
                                                <td><?php echo htmlspecialchars($row->birthcertno, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>UPI Code:</strong></td>
                                                <td><?php echo htmlspecialchars($row->upicode, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Assessment No:</strong></td>
                                                <td><?php echo htmlspecialchars($row->assessmentno, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Parent/Guardian Details -->
                                    <div class="section mb-4">
                                        <h4 class="text-primary mb-3">Parent/Guardian Details</h4>
                                        <table class="table table-striped table-bordered">
                                            <tr style="background-color: #e6f7ff;">
                                                <td><strong style="color: #007bff;">Mother:</strong></td>
                                                <td><?php echo htmlspecialchars($row->mothername, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row->mothercontact, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row->motherhomearea, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #f2f8fc;">
                                                <td><strong style="color: #ff6347;">Father:</strong></td>
                                                <td><?php echo htmlspecialchars($row->fathername, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row->fathercontact, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row->fatherhomearea, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #e6f7ff;">
                                                <td><strong style="color: #9932cc;">Guardian:</strong></td>
                                                <td><?php echo htmlspecialchars($row->guardianname, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row->guardiancontact, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row->guardianhomearea, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #f2f8fc;">
                                                <td><strong style="color: #28a745;">Fee Payer:</strong></td>
                                                <td><?php echo htmlspecialchars($row->feepayer, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                             <tr style="background-color: #f2f8fc;">
                                                <td><strong style="color: #28a745;">Fee BalanceReminder:</strong></td>
                                                <td><?php echo htmlspecialchars($row->feebalancereminder, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #e6f7ff;">
                                                <td><strong style="color: #dc3545;">Emergency:</strong></td>
                                                <td><?php echo htmlspecialchars($row->emergencyname, ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($row->emergencycontact, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #f2f8fc;">
                                                <td><strong style="color: #6c757d;">Home County:</strong></td>
                                                <td><?php echo htmlspecialchars($row->homecounty, ENT_QUOTES, 'UTF-8'); ?> | Subcounty: <?php echo htmlspecialchars($row->homesubcounty, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Health Details -->
                                    <div class="section mb-4">
                                        <h4 class="text-primary mb-3">Health Details</h4>
                                        <table class="table table-striped table-bordered">
                                            <tr style="background-color: #e6f7ff;">
                                                <td><strong>Health Issue:</strong></td>
                                                <td><?php echo htmlspecialchars($row->healthissue, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #f2f8fc;">
                                                <td><strong>Allergy:</strong></td>
                                                <td><?php echo htmlspecialchars($row->allergy, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #f2f8fc;">
                                                <td><strong>Insurance:</strong></td>
                                                <td><?php echo htmlspecialchars($row->insurancecover, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #e6f7ff;">
                                                <td><strong>Special Doctor:</strong></td>
                                                <td><?php echo htmlspecialchars($row->special_doctor, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row->doctor_name, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($row->doctor_contact, ENT_QUOTES, 'UTF-8'); ?>)</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Academic Details -->
                                    <div class="section mb-4">
                                        <h4 class="text-primary mb-3">Academic Details</h4>
                                        <table class="table table-striped table-bordered">
                                            <tr style="background-color: #e6f7ff;">
                                                <td><strong>Previous School:</strong></td>
                                                <td><?php echo htmlspecialchars($row->previousschool, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #f2f8fc;">
                                                <td><strong>Entry Performance Level:</strong></td>
                                                <td><?php echo htmlspecialchars($row->entryperformancelevel, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                            <tr style="background-color: #e6f7ff;">
                                                <td><strong>Admission Date:</strong></td>
                                                <td><?php echo htmlspecialchars($row->admdate, ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    
                    </div>
                </div>
            </div> 
        </div> 
    </div>
</div>
