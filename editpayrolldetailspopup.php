<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal<?php echo ($row->id); ?>" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Edit PayrollSerial Entry</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                  
                       
               <input type="hidden" name="id"  value="<?php echo $row->id;?>">
                           <div class="form-group">                                
                              <table class="table">
                                 <tr>
                                 <td>
                                    <label for="payrollmonth">Payroll Month:</label></td><td>
                                    <select name="editpayrollmonth"  value="" class="form-control" required="required"> 
                                    <option value="<?php echo $row->payrollmonth;?>"><?php echo $row->payrollmonth;?></option> 
                                       <option value="Jan">Jan</option> 
                                       <option value="Feb">Feb</option>
                                       <option value="Mar">Mar</option> 
                                       <option value="Apr">Apr</option>
                                       <option value="May">May</option> 
                                       <option value="Jun">Jun</option>
                                       <option value="Jul">Jul</option> 
                                       <option value="Aug">Aug</option>
                                       <option value="Sep">Sep</option> 
                                       <option value="Oct">Oct</option>
                                       <option value="Nov">Nov</option> 
                                       <option value="Dec">Dec</option>
                                    </select>
                                  </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="payrollyear">Payroll Year:</label></td><td>
                                       <input type="text" class="form-control" name="editpayrollyear" id="editpayrollyear" required="required" placeholder="payrollyear" value="<?php echo $row->payrollyear;?>">                     
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="bank">Bank:</label></td><td>
                                       <?php
                              $smt=$dbh->prepare('SELECT bankname from bankdetails');
                              $smt->execute();
                              $data=$smt->fetchAll();
                              ?>   
                                       <select name="editbank"  value="<?php echo $bank; ?>" class="form-control" required="required"> 
                                          <option value="<?php echo $row->bank;?>"><?php echo $row->bank;?></option>
                                                <?php foreach ($data as $rw):?>
                                          <option value="<?=$rw["bankname"]?>"><?=$rw["bankname"]?></option> 
                                                      <?php endforeach ?>
                                       </select>
                                    </td>
                                 </tr>
                                 <tr>                                
                                    <td>
                                       <label for="chequeno">Cheque No:</label></td><td>
                                       <input type="text" class="form-control" name="editchequeno" id="editchequeno" placeholder="chequeno" value="<?php echo $row->chequeno;?>" >
                                    </td>
                                 </tr>
                                 <tr>                      
                                            
                           </table>
 </div>
                                        <div>
                                      <p style="padding-left: 450px">
                                           <button type="submit" name="update_submit" class="btn btn-primary">Update</button>
                                              </p>
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
 