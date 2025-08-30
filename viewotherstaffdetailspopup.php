<div role="dialog" id="otherstaffdetails<?php echo $cnt; ?>" class="modal fade"> 
    <div class="modal-dialog customstaff-modal-xl"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2><b> <?php echo $row->staffname; ?></b></h2>               
            </div> 
      
            <div class="modal-body">                      
                    <div class="row">
                        <div class="col-lg-12">                            
                                        <table class="table table-striped table-bordered table-hover">
                                            <tbody>
                                                <tr>
                                                    <td style="color: green;">Disability:</td>
                                                    <td><?php echo htmlentities($row->disability); ?> <?php echo htmlentities($row->disabilitytype); ?></td>
                                                    <td>Passport Photo</td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Gender:</td>
                                                    <td><?php echo htmlentities($row->gender); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Date Of Birth:</td>
                                                    <td><?php echo htmlentities($row->dob); ?>,
                                                        &nbsp;&nbsp;Age: <span style="color: green;">
                                                        <?php
                                                        $dob = new DateTime($row->dob);
                                                        $currentDate = new DateTime();
                                                        $age = $currentDate->diff($dob)->y;
                                                        echo $age;
                                                        ?> Years
                                                        </span>
                                                    </td>
                                                    <td rowspan="10" style="text-align: center; vertical-align: middle; 
                                                        <?php if (empty($row->pfpicname)): ?>
                                                            background: none;
                                                            width: 420px; height: 410px;
                                                            line-height: 410px; /* Vertically center the text */
                                                            text-align: center;
                                                            color: #333;
                                                            font-size: 18px;
                                                        <?php else: ?>
                                                            background: url('<?php echo htmlentities($row->pfpicname); ?>') no-repeat center center;
                                                            background-size: cover;
                                                            width: 420px; height: 410px;
                                                        <?php endif; ?>
                                                    ">
                                                        <?php if (empty($row->pfpicname)): ?>
                                                            <span style="color: red;">No Profile Picture Uploaded</span>
                                                        <?php endif; ?>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Marital Status:</td>
                                                    <td><?php echo htmlentities($row->maritalstatus); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Email Address:</td>
                                                    <td><?php echo htmlentities($row->emailaddress); ?></td>
                                                </tr>                                              
                                                <tr>
                                                    <td style="color: green;">Postal Address:</td>
                                                    <td><?php echo htmlentities($row->postaladdress); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Sublocations:</td>
                                                    <td><b>Home: </b><?php echo htmlentities($row->homesublocation);?>-(<?php echo htmlentities($row->homecounty);?>)<br> <b>Residence: </b><?php echo htmlentities($row->residencesublocation);?>-(<?php echo htmlentities($row->residencecounty);?>)</td>
                                                </tr>                        
                                                <tr>
                                                    <td style="color: green;">KRA-Pin:</td>
                                                    <?php
                                                    if ($accounttype == "Supervisor" || $accounttype == "Admin") {
                                                        $krapinToShow = empty($row->krapin) ? "" : htmlentities($row->krapin);
                                                    } else {
                                                        if (strlen($row->krapin) <= 2) {
                                                            // If krapin is 2 characters or less, show it without additional masking
                                                            $krapinToShow = htmlentities($row->krapin);
                                                        } else {
                                                            // Mask krapin if longer than 2 characters
                                                            $krapinToShow = substr($row->krapin, 0, 2) . str_repeat('*', strlen($row->krapin) - 2);
                                                            $krapinToShow = htmlentities($krapinToShow);
                                                        }
                                                    }
                                                    echo "<td>$krapinToShow</td>";
                                                    ?>
                                                </tr> 
                                                <tr>
                                                    <td style="color: green;">Availability:</td>
                                                    <td><?php echo htmlentities($row->projectavailability);?>, <?php echo htmlentities($row->projecttype); ?>, <?php echo htmlentities($row->projectmode); ?></td>  
                                                </tr>                       
                                             
                                                <tr>
                                                    <td style="color: green;">MpesaNo:</td>
                                                    <td><?php echo htmlentities($row->mpesano);?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">MpesaName:</td>
                                                    <td><?php echo htmlentities($row->mpesaname);?></td>
                                                </tr>  
                                                <tr>
                                                    <td style="color: green;">MpesaIdNo:</td>
                                                    <?php
                                                    if ($accounttype == "Supervisor" || $accounttype == "Admin") {
                                                        $mpesaidnoToShow = empty($row->mpesaidno) ? "" : htmlentities($row->mpesaidno);
                                                    } else {
                                                        if (strlen($row->mpesaidno) <= 2) {
                                                            // Show the full mpesaidno if it has 2 characters or fewer
                                                            $mpesaidnoToShow = htmlentities($row->mpesaidno);
                                                        } else {
                                                            // Mask mpesaidno if it has more than 2 characters
                                                            $mpesaidnoToShow = substr($row->mpesaidno, 0, 2) . str_repeat('*', strlen($row->mpesaidno) - 2);
                                                            $mpesaidnoToShow = htmlentities($mpesaidnoToShow);
                                                        }
                                                    }
                                                    echo "<td>$mpesaidnoToShow</td>";
                                                    ?>
                                                </tr>  
                                                                       
                                                <tr>
                                                    <td style="color: green;">Emergency Name:</td>
                                                    <td style="background-color: lightgreen;"><?php echo htmlentities($row->nextofkinname);?></td>
                                                    <td style="background-color: lemonchiffon;"><?php echo htmlentities($row->secondnextofkinname);?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Emergency Contact:</td>
                                                    <td style="background-color: lightgreen;"><?php echo htmlentities($row->nextofkincontact);?></td>
                                                    <td style="background-color: lemonchiffon;"><?php echo htmlentities($row->secondnextofkincontact);?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Emergency Relation:</td>
                                                    <td style="background-color: lightgreen;"><?php echo htmlentities($row->nextofkinrelation);?></td>
                                                    <td style="background-color: lemonchiffon;"><?php echo htmlentities($row->secondnextofkinrelation);?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Beneficiary Name:</td>
                                                    <td style="background-color: lemonchiffon;"><?php echo htmlentities($row->beneficiaryname);?></td>
                                                    <td style="background-color: lightgreen;"><?php echo htmlentities($row->secondbeneficiaryname);?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Beneficiary Contact:</td>
                                                    <td style="background-color: lemonchiffon;"><?php echo htmlentities($row->beneficiarycontact);?></td>
                                                    <td style="background-color: lightgreen;"><?php echo htmlentities($row->secondbeneficiarycontact);?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Beneficiary Relation:</td>
                                                    <td style="background-color: lemonchiffon;"><?php echo htmlentities($row->beneficiaryrelation);?></td>
                                                    <td style="background-color: lightgreen;"><?php echo htmlentities($row->secondbeneficiaryrelation);?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Beneficiary Percentage:</td>
                                                    <td style="background-color: lemonchiffon;"><?php echo htmlentities($row->beneficiarypercentage);?></td>
                                                    <td style="background-color: lightgreen;"><?php echo htmlentities($row->secondbeneficiarypercentage);?></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td style="color: green;">Education:</td>
                                                    <td colspan="2" class="expandable-cell"><b>Level: </b><?php echo htmlentities($row->educationlevel);?>, <b>Speciality: </b><?php echo htmlentities($row->speciality);?></td>
                                                </tr>                                                
                                                <tr>
                                                    <td style="color: green;">Spoken Languages:</td>
                                                    <td><?php echo htmlentities($row->languages); ?></td>
                                                    <td><span style="color: green;">Proficiency:</span>  <?php echo htmlentities($row->spokenlanguagesproficiency); ?></td>
                                                </tr> 
                                                <tr>
                                                    <td style="color: green;">Written Languages:</td>
                                                    <td><?php echo htmlentities($row->writtenlanguages); ?></td>
                                                    <td><span style="color: green;">Proficiency:</span> <?php echo htmlentities($row->writtenlanguagesproficiency); ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Key Competencies:</td>                           
                                                    <td colspan="2">
                                                    <textarea readonly 
          style="width: 100%; color: blue; border: none; overflow: hidden; resize: none;" 
          oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">
<?php echo htmlentities($row->keycompetencies); ?>
</textarea>

 </td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Other Comments:</td>
                                                    <td colspan="2">
                                                    <textarea readonly style="width: 100%; color: blue; border: none; overflow: hidden; resize: none;" 
          oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">
<?php echo htmlentities($row->othercomments); ?>
</textarea>
 </td>
                                                </tr>
                                                <tr>
                                                    <td rowspan="2" style="color: green;">Flagging:</td>
                                                    <td>Type: <?php echo htmlentities($row->flagseverity); ?></td>
                                                    <td>Flag Duration: <?php echo htmlentities($row->flagduration); ?> Months</td>
                                                </tr> 
                                                <tr>
                                                    <td>Start-Date: <?php echo htmlentities($row->flagstartdate); ?></td>
                                                    <td>End-Date: <?php echo htmlentities($row->flagenddate); ?></td>
                                                </tr> 
                                                <tr>
                                                    <td style="color: green;">Flag Reason:</td>
                                                    <td colspan="2">
                                                    <textarea readonly 
          style="width: 100%; color: blue; border: none; overflow: hidden; resize: none;" 
          oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">
<?php echo htmlentities($row->flagreason); ?>
</textarea> </td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;">Action Plan/Comments:</td>
                                                    <td colspan="2">
                                                    <textarea readonly 
          style="width: 100%; color: blue; border: none; overflow: hidden; resize: none;" 
          oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px';">
<?php echo htmlentities($row->flagactionplan); ?>
</textarea> </td>
                                                </tr>
                                                <tr>
                                                    <td style="color: green;"></td>
                                                    <td>FlaggedBy: <?php echo htmlentities($row->flaggedby); ?></td>
                                                    <td>ApprovedBy: <?php echo htmlentities($row->flagapprovedby); ?></td>
                                                </tr> 
                                            </tbody>
                                        </table>
                                    </span> 
                                </div>                 
                            </div>             
                        </div>             
                    </div> 
                </div>
            </div> 
    
        </div> 
    </div>
</div>
