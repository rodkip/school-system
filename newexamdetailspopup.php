<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Exam Detail Entry</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
   <form action="manage-examdetails.php" method="POST" enctype="multipart/form-data">
        <div class="form-group"> 
                                      <br>
                  <table class="table">
                          <tr>
                            <td>
                              <label for="examterm">Exam Term:</label></td><td>
                              <select name="examterm"  value="" class="form-control" required="required"> 
                                 <option value="firstterm">First Term</option>                                  
                                 <option value="secondterm">Second Term</option> 
                                 <option value="thirdterm">Third Term</option>                                    
                              </select>
                            </td>
                           </tr>
                           <tr>
                            <td>
                              <label for="examyear">Exam Year:</label></td><td>
                                <?php $financialyear=date("Y"); ?> 
                              <input type="text" class="form-control" name="examyear" id="examyear"
                                required="required" placeholder="exam year" value="<?php echo $financialyear; ?>">
                           </td>
                           </tr>
                           <tr>
                            <td>
                              <label for="examclass">Exam Class:</label></td><td>
                              <?php
             $smt=$dbh->prepare('SELECT grade from grades order by id asc');
              $smt->execute();
              $data=$smt->fetchAll();
        ?>

          <select name="examclass"  value="" class="form-control" required="required" > 
                  <option value="">--select grade--</option>
             <?php foreach ($data as $row):?>
                  <option value="<?=$row["grade"]?>"><?=$row["grade"]?></option> 
              <?php endforeach ?>
          </select>

                              </td>
                           </tr>          
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
 