<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">New PayrollSerial Entry</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
                        <form method="post" enctype="multipart/form-data" action="manage-payrolldetails.php"> 
                                  
                           <div class="form-group">                                
                              <table class="table">
                                 <tr>
                                 <td>
                                    <label for="payrollmonth">Payroll Month:</label></td><td>
                                    <select name="payrollmonth"  value="" class="form-control" required="required"> 
                                    <option value="">--payrollmonth--</option> 
                                       <option value="Jan">January</option> 
                                       <option value="Feb">February</option>
                                       <option value="mar">March</option> 
                                       <option value="Apr">April</option>
                                       <option value="May">May</option> 
                                       <option value="Jun">June</option>
                                       <option value="Jul">July</option> 
                                       <option value="Aug">August</option>
                                       <option value="Sep">September</option> 
                                       <option value="Oct">October</option>
                                       <option value="Nov">November</option> 
                                       <option value="Dec">December</option>
                                    </select>
                                  </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <label for="payrollyear">Payroll Year:</label></td><td>
                                       <input type="text" class="form-control" name="payrollyear" id="payrollyear" required="required" placeholder="payrollyear" value="<?php echo $currentyear; ?>">                     
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
                                       <select name="bank"  value="<?php echo $bank; ?>" class="form-control" required="required"> 
                                          <option value="">--select Bank--</option>
                                                <?php foreach ($data as $row):?>
                                          <option value="<?=$row["bankname"]?>"><?=$row["bankname"]?></option> 
                                                      <?php endforeach ?>
                                       </select>
                                    </td>
                                 </tr>
                                 <tr>                                
                                    <td>
                                       <label for="chequeno">Cheque No:</label></td><td>
                                       <input type="text" class="form-control" name="chequeno" id="chequeno" placeholder="chequeno" value="0" >
                                    </td>
                                 </tr>
                                 <tr>                      
                                            
                           </table>
 </div>
                                        <div>
                                      <p style="padding-left: 450px">
                                           <button type="submit" name="submit" class="btn btn-primary">Submit</button>
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
 