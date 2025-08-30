<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Upper Primary Exam Entry</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
   <form action="manage-examsupperprimary.php" method="POST" enctype="multipart/form-data">
        <div class="form-group"> 
                                      <br>
                  <table class="table">
                  <tr>
                            <td>
                              <label for="examclass">Examfullname:</label></td><td>
                              <?php
             $smt=$dbh->prepare('SELECT examfullname from examdetails order by id asc');
              $smt->execute();
              $data=$smt->fetchAll();
        ?>

          <select name="examfullname"  value="<?php echo $search_examfullname ?>" class="form-control" required="required" > 
                  <option value="<?php echo $search_examfullname ?>"><?php echo $search_examfullname ?></option>
             <?php foreach ($data as $row):?>
                  <option value="<?=$row["examfullname"]?>"><?=$row["examfullname"]?></option> 
              <?php endforeach ?>
          </select>

                              </td>
                           </tr>   
                          <tr>
                                    <td>
                                       <label for="studentadmno">Student AdmNo:</label></td><td>
                                       <input type="text" class="form-control" name="studentadmno" id="studentadmno" required="required" placeholder="Enter studentadmno here" value="" onBlur="admnoAvailability()">
                                       <span id="user-availability-status1" style="font-size:12px;"></span>
                                    </td>
                                 </tr>       

                           <tr>
                            <td>
                              <label for="maths">Maths:</label></td><td>                                
                              <input type="number" class="form-control" name="maths" id="maths"
                                required="required" placeholder="maths" value="0" max='100'>
                           </td>
                           </tr>
                           <tr>
                            <td>
                              <label for="english">English:</label></td><td>                                
                              <input type="number" class="form-control" name="english" id="english"
                                required="required" placeholder="english" value="0"  max='100' min='0'>
                           </td>
                           </tr>

                           <tr>
                            <td>
                              <label for="kiswahili">Kiswahili:</label></td><td>                                
                              <input type="number" class="form-control" name="kiswahili" id="kiswahili"
                                required="required" placeholder="kiswahili" value="0"  max='100' min='0'>
                           </td>
                           </tr>

                           <tr>
                            <td>
                              <label for="science">Science:</label></td><td>                                
                              <input type="number" class="form-control" name="science" id="science"
                                required="required" placeholder="science" value="0"  max='100' min='0'>
                           </td>
                           </tr>

                           <tr>
                            <td>
                              <label for="sstudiescre">SStudiesCRE:</label></td><td>                                
                              <input type="number" class="form-control" name="sstudiescre" id="sstudiescre"
                                required="required" placeholder="sstudiescre" value="0" max='100' min='0'>
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
 