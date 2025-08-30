<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
   <div class="modal-dialog"> 
      <div class="modal-content">
         <div class="modal-header"> 
            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
            <h2 class="modal-title" style="text-align: center;">New Class Creation</h2> 
         </div> 
         <div class="modal-body">
            <div class="panel panel-primary">                
               <div class="row">
                  <div class="col-lg-12">   
                     <div class="panel panel-default">  
                        <div class="popup">             
                           <div class="form-group">                                
                              <table class="table" width="100%">
                                 <tr>
                                    <td>
                                       <label for="gradename">Grade Name:</label>
                                    </td>
                                    <td>
                                             <?php
                                             $smt = $dbh->prepare('SELECT grade FROM grade ORDER BY id ASC');
                                             $smt->execute();
                                             $data = $smt->fetchAll();
                                             ?>
                                             <select name="gradename" class="form-control" required>
                                                <option value="">-- Select Grade --</option>
                                                <?php foreach ($data as $row): ?>
                                                   <option value="<?= $row["grade"] ?>"><?= $row["grade"] ?></option>
                                                <?php endforeach; ?>
                                             </select>
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>
                                       <label for="academicyear">Academic Year:</label>
                                    </td>
                                    <td>
                                       <input type="text" class="form-control" name="academicyear" id="academicyear" required value="<?php echo $currentacademicyear; ?>">
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>
                                       <label for="classcapacity">Class Capacity:</label>
                                    </td>
                                    <td>
                                       <input type="text" class="form-control" name="classcapacity" id="classcapacity" value="0">
                                    </td>
                                    </tr>
                                    <tr>
                                    <td>
                                       <button type="submit" name="submit" class="btn btn-primary">
                                          <i class="fa fa-check-circle"></i> Submit
                                       </button>
                                    </td>
                                 </tr>
                              </table>
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
 