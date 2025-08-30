<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal<?php echo ($row->id); ?>" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Edit Payroll Entry</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
                        <form method="post" enctype="multipart/form-data" action="manage-feestructure.php"> 
                        <input type="hidden" name="id"  value="<?php echo $row->id;?>">
                           <div class="form-group">                                
                              <table class="table">
                                 <tr>
                                 <td>
                                    <label for="payrollserialno">StaffIdNo:</label></td><td>
                                       <input type="text" name="editstaffidno" id="staffidno" required="required" placeholder="Enter IdNo here" value="<?php echo $row->staffidno?>" class="form-control" onBlur="staffidnoAvailability()"> 
                                       <span id="user-availability-status1" style="font-size:12px;"></span>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="payrollserialno">PayrollSerialNo:</label></td><td>
                                    <?php
                                 $smt=$dbh->prepare('SELECT payrollserialno,id from payrolldetails order by id desc');
                                 $smt->execute();
                                 $data=$smt->fetchAll();
                                 ?>   <select name="editpayrollserialno"  value="<?php echo $payrollserialno; ?>" class="form-control"> 
                                       <option value="<?php echo $row->payrollserialno?>"><?php echo $row->payrollserialno?></option>
                                       <?php foreach ($data as $rw):?>
                                       <option value="<?=$rw["payrollserialno"]?>"><?=$rw["payrollserialno"]?></option> 
                                             <?php endforeach ?>
                                       </select>                                    
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="basicpay">Basic Pay:</label></td><td>
                                       <input type="text" class="form-control" name="editbasicpay" id="basicpay" required="required" placeholder="Basic Pay" value="<?php echo $row->basicpay?>">
                                    </td>
                                 </tr>
                                 <tr>                                
                                    <td>
                                       <label for="houseallowance">House All:</label></td><td>
                                       <input type="text" class="form-control" name="edithouseallowance" id="houseallowance" placeholder="House Allowance" value="<?php echo $row->houseallowance?>" >
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="respallowance">Resp All:</label></td><td>
                                       <input type="text" name="editrespallowance" id="respallowance" placeholder="Responsbility Allowance" value="<?php echo $row->respallowance?>" class="form-control">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="nhifdeduction">SHA Ded:</label>
                                       </td><td>
                                       <input type="text" class="form-control" name="editnhifdeduction" id="nhifdeduction" placeholder="NHIF ded" value="<?php echo $row->nhifdeduction?>">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="nssfdeduction">Nssf Ded</label>
                                       </td><td>
                                       <input type="text" class="form-control" name="editnssfdeduction" id="nssfdeduction" placeholder="NSSF ded" value="<?php echo $row->nssfdeduction?>">
                                    </td>
                                 </tr>     
                                 <tr>
                                    <td>
                                       <label for="teacherswelfarededuction">Welfare Ded:</label></td><td>
                                       <input type="text" class="form-control" name="editteacherswelfarededuction" id="teacherswelfarededuction" placeholder="Tecahers Welfare Deduction" value="<?php echo $row->teacherswelfarededuction?>">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="staffwelfarededuction">Welfare Ded:</label></td><td>
                                       <input type="text" class="form-control" name="editstaffwelfarededuction" id="staffwelfarededuction" placeholder="Staff Welfare Deduction" value="<?php echo $row->staffwelfarededuction?>">
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="feesdeduction">Fees Ded:</label></td><td>
                                       <input type="text" class="form-control" name="editfeesdeduction" id="feesdeduction" placeholder="Fees Deduction" value="<?php echo $row->feesdeduction?>">
                                    </td>
                                 </tr>
                                 <tr>                                   
                                    <td>
                                       <label for="advancededuction">Advance Ded:</label></td><td>
                                       <input type="text" class="form-control" name="editadvancededuction" id="advancededuction" placeholder="Advance Deduction" value="<?php echo $row->advancededuction?>">
                                    </td>                                   
                                 </tr>   
                                 <tr>                                   
                                    <td>
                                       <label for="othersdeduction">Others Ded</label></td><td>
                                       <input type="text" class="form-control" name="editothersdeduction" id="othersdeduction" placeholder="Others Deduction" value="<?php echo $row->othersdeduction?>">
                                    </td>                             
                                 </tr>              
                           </table>
 </div>
                                        <div>
                                      <p style="padding-left: 450px">
                                           <button type="submit" name="update_submit" class="btn btn-primary">Update</button>
                                              </p>
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
 