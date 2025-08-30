<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal<?php echo ($row->id); ?>" class="modal fade"> 
<div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Edit transport Payment</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary" >                
                        <div class="row">
                            <div class="col-lg-12" >            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default" >     
   <div class="popup" >     
                        <input type="hidden" name="id" value="<?php echo $row->id ?>">
                           <div class="form-group" style="width:90%" > 
                              <br>
                              <center>                                
                              <table class="table">
                     <tr>
                        <td>
                           <label for="studentadmno">Adm No:</label></td><td>
                           <input type="text" name="studentadmno" id="studentadmno" required="required" placeholder="Student AdmNo" value="<?php echo $row->studentadmno; ?>" class="form-control" onBlur="admnoAvailability()" ></td>
                           <span id="user-availability-status1" style="font-size:14px;"></span>                     
                     </tr>
                     <tr>
                        <td>
                           <label for="receiptno">Receipt No:</label></td><td>
                           <input type="text" class="form-control" name="receiptno" id="receiptno" required="required" placeholder="ReceiptNo" value="<?php echo $row->receiptno;?>">
                        </td>                     
                     </tr>
                     <tr>    
                        <td>
                           <label for="cash">Cash:</label></td><td>
                           <input type="text" class="form-control" name="cash" id="Cash" placeholder="Enter cash" value="<?php echo $row->cash;?>" required="required">
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
                           <select name="bank"  value="" class="form-control" required="required"> 
                              <option value="<?php echo $row->bank;?>"><?php echo $row->bank;?></option>
                              <?php foreach ($data as $rw):?>
                              <option value="<?=$rw["bankname"]?>"><?=$rw["bankname"]?></option> 
                                    <?php endforeach ?>
                           </select>
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <label for="bankpaymentdate">BankPaymentDate:</label></td><td>
                           <input type="date" class="form-control" name="bankpaymentdate" id="bankpaymentdate"  placeholder="bankpaymentdate" value="<?php echo $row->bankpaymentdate;?>" required="required">
                        </td>                       
                     </tr>
                     <tr>
                        <td>
                           <label for="paymentdate">Receipt Date:</label></td><td>
                           <input type="date" class="form-control" name="paymentdate" id="paymentdate" placeholder="ReceiptDate" value="<?php echo $row->paymentdate;?>" required="required">
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <label for="details">Details:</label></td><td>
                           <textarea type="text" class="form-control" name="details" id="details" placeholder="Description" value="<?php echo $row->details;?>"> </textarea>
                        </td>
                     </tr>
                     <tr>                         
                        <td>                           
                           <label for="academicyear">Academic Year:</label></td><td>
                           <input type="text" class="form-control" name="academicyear" id="academicyear" placeholder="academicyear" value="<?php echo $row->academicyear;?>" required="required">
                        </td>
                     </tr>
                  </table>      
                              </center>
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