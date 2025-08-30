<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <button aria-hidden="true" data-dismiss="modal" class="close text-white" type="button">&times;</button>
                <h2 class="modal-title text-center">Learner Registration Details</h2>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Left Column: Student Details -->
                    <div class="col-md-7">
                        <div class="card mb-4">
                            <div class="card-body">
                                <?php
                                if (isset($searchadmno) && !empty($searchadmno)) {
                                    // Fetch student details along with parent/guardian names
                                    $searchquery = "SELECT 
                                                sd.studentadmno, sd.studentname, sd.gender, sd.dateofbirth, 
                                                sd.birthcertno, sd.upicode,sd.assessmentno, sd.motherparentno, sd.fatherparentno, 
                                                sd.guardianparentno, sd.emergencyname, sd.emergencycontact, 
                                                sd.homecounty, sd.homesubcounty, sd.healthissue, sd.allergy, sd.insurancecover,
                                                sd.pfpicname, sd.feepayer, sd.special_doctor, sd.doctor_name, 
                                                sd.doctor_contact, sd.previousschool,sd.regfee, sd.entryperformancelevel, 
                                                sd.entrydate, 
                                                pd_mother.homearea AS motherhomearea,
                                                pd_father.homearea AS fatherhomearea,
                                                pd_guardian.homearea AS guardianhomearea,
                                                pd_mother.parentname AS mothername, 
                                                pd_father.parentname AS fathername, 
                                                pd_guardian.parentname AS guardianname, 
                                                pd_mother.parentcontact AS mothercontact, 
                                                pd_father.parentcontact AS fathercontact, 
                                                pd_guardian.parentcontact AS guardiancontact 
                                                    FROM studentdetails sd
                                                    LEFT JOIN parentdetails pd_mother ON sd.motherparentno = pd_mother.parentno
                                                    LEFT JOIN parentdetails pd_father ON sd.fatherparentno = pd_father.parentno
                                                    LEFT JOIN parentdetails pd_guardian ON sd.guardianparentno = pd_guardian.parentno
                                                    WHERE sd.studentadmno = :searchadmno";

                                    $qry = $dbh->prepare($searchquery);
                                    $qry->bindParam(':searchadmno', $searchadmno, PDO::PARAM_STR);
                                    $qry->execute();
                                    $row = $qry->fetchAll(PDO::FETCH_OBJ);

                                    if ($qry->rowCount() > 0) {
                                        foreach ($row as $rlt) {
                                ?>
                                            <!-- Personal Details -->
                                            <div class="section mb-4">
                                                <h4 class="text-primary mb-3">Personal Details</h4>
                                                <table class="table table-striped table-bordered">
                                                    <tr style="background-color: #f0f8ff;">
                                                        <td><strong>Adm No:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->studentadmno, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($rlt->studentname, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                    
                                                    <tr style="background-color: #e6f7ff;">
                                                        <td><strong>Gender:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->gender, ENT_QUOTES, 'UTF-8'); ?> | DOB: <?php echo htmlspecialchars($rlt->dateofbirth, ENT_QUOTES, 'UTF-8'); ?>
                                                            (<span class="text-success">
                                                                <?php echo round((time() - strtotime($rlt->dateofbirth)) / (3600 * 24 * 365.25)); ?> Years
                                                            </span>)
                                                        </td>
                                                    </tr>

                                                    <tr style="background-color: #f0f8ff;">
                                                        <td><strong>Birth Cert No:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->birthcertno, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="background-color: #e6f7ff;">
                                                        <td><strong>UPI Code:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->upicode, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                    <tr style="background-color: #e6f7ff;">
                                                        <td><strong>Assessment No:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->assessmentno, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                </table>
                                            </div>

                                            <!-- Parent/Guardian Details -->
                                            <div class="section mb-4">
                                                <h4 class="text-primary mb-3">Parent/Guardian Details</h4>
                                                <table class="table table-striped table-bordered">
                                                    <tr style="background-color: #f9f9f9;">
                                                        <td><strong>Mother:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->mothername, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($rlt->mothercontact, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="background-color: #ffffff;">
                                                        <td><strong>Father:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->fathername, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($rlt->fathercontact, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="background-color: #f9f9f9;">
                                                        <td><strong>Guardian:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->guardianname, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($rlt->guardiancontact, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="background-color: #ffffff;">
                                                        <td><strong>Fee Payer:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->feepayer, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="background-color: #f9f9f9;">
                                                        <td><strong>Emergency:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->emergencyname, ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($rlt->emergencycontact, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="background-color: #ffffff;">
                                                        <td><strong>Home County:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->homecounty, ENT_QUOTES, 'UTF-8'); ?> | Subcounty: <?php echo htmlspecialchars($rlt->homesubcounty, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                </table>
                                            </div>

                                           <!-- Health Details -->
                                            <div class="section mb-4">
                                                <h4 class="text-primary mb-3">Health Details</h4>
                                                <table class="table table-striped table-bordered">
                                                    <tr style="background-color: #f0f0f0;">
                                                        <td style="width: 150px; word-wrap: break-word; white-space: normal;"><strong>Health Issue:</strong></td>
                                                        <td style="max-width: 300px; word-wrap: break-word; white-space: normal;"><?php echo htmlspecialchars($rlt->healthissue, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="background-color: #ffffff;">
                                                        <td style="width: 150px; word-wrap: break-word; white-space: normal;"><strong>Allergy:</strong></td>
                                                        <td style="max-width: 300px; word-wrap: break-word; white-space: normal;"><?php echo htmlspecialchars($rlt->allergy, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                    <tr style="background-color: #f2f8fc;">
                                                        <td style="width: 150px; word-wrap: break-word; white-space: normal;"><strong>Insurance:</strong></td>
                                                        <td style="max-width: 300px; word-wrap: break-word; white-space: normal;"><?php echo htmlspecialchars($rlt->insurancecover, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                    <tr style="background-color: #f0f0f0;">
                                                        <td><strong>Special Doctor:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->special_doctor, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($rlt->doctor_name, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($rlt->doctor_contact, ENT_QUOTES, 'UTF-8'); ?>)</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <!-- Academic Details -->
                                            <div class="section mb-4">
                                                <h4 class="text-primary mb-3">Academic Details</h4>
                                                <table class="table table-striped table-bordered">
                                                    <tr style="color: #4CAF50;">
                                                        <td><strong>Previous School:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->previousschool, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="color: #2196F3;">
                                                        <td><strong>Entry Performance Level:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->entryperformancelevel, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>

                                                    <tr style="color: #FF5722;">
                                                        <td><strong>Admission Date:</strong></td>
                                                        <td><?php echo htmlspecialchars($rlt->entrydate, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                </table>

                                            </div>
                                        <?php
                                        }
                                    } else {
                                        echo "<p class='text-danger'>No records found for the given Admission Number.</p>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Student Photo -->
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php if (!empty($rlt->pfpicname) && file_exists('pfpics/' . $rlt->pfpicname)) { ?>
                                    <img src="pfpics/<?php echo htmlspecialchars($rlt->pfpicname, ENT_QUOTES, 'UTF-8'); ?>" 
                                         alt="Profile Picture" 
                                         class="img-fluid rounded-circle shadow" 
                                         style="max-width: 300px; height: auto; border: 3px solid #007bff;">
                                <?php } else { ?>
                                    <p class="text-muted">No photo available.</p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>