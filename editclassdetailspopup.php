<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal<?php echo ($row->id); ?>" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Edit Class Session Details</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup"> 
   <form method="post" enctype="multipart/form-data" action="manage-classdetails.php">                   
          <input type="hidden" name="id"  value="<?php echo $row->id;?>">
            <div class="form-group">                                          
               <table  class="table table-striped table-bordered table-hover">
                  <tr>
                     <td>
                     <label for="gradename">Gradename:</label></td><td>
                        <select name="editgradename"  value="<?php $row->gradename;?>" class="form-control">
                           <option value="<?php echo $row->gradename;?>"><?php echo $row->gradename;?></option>  
                           <option value="Baby">Baby</option> 
                           <option value="Middle">Middle</option>
                           <option value="Preunit">Pre-Unit</option> 
                           <option value="Grade1">Grade 1</option>
                           <option value="Grade2">Grade 2</option>
                           <option value="Grade3">Grade 3</option>
                           <option value="Grade4">Grade 4</option>
                           <option value="Grade5">Grade 5</option>
                           <option value="Grade6">Grade 6</option>
                           <option value="Grade7">Grade 7</option>
                           <option value="Grade8">Grade 8</option>
                        </select></td>
                     </tr>
                     <tr>
                     <td>
                     <label for="academicyear">Academicyear:</label></td><td>
                     <input type="text" class="form-control" name="editacademicyear" id="academicyear" required="required" placeholder="Enter academicyear here" value="<?php echo $row->academicyear; ?>">
                     </td> 
                     </tr>
                     <tr>
                     <td>
                     <label for="classcapacity">Class Capacity:</label></td><td>
                     <input type="text" class="form-control" name="editclasscapacity" id="classcapacity" placeholder="Enter classcapacity here" value="<?php echo $row->classcapacity; ?>"></td>
                     </tr>
                     <tr>
                     <td>
                     <label for="stream">Stream:</label></td><td>
                              <?php
                                 $smt=$dbh->prepare('SELECT streamname from streams order by streamname asc');
                                 $smt->execute();
                                 $data=$smt->fetchAll();
                              ?>
                        <select name="editstream"  value="" class="form-control"> 
                           <option value="<?php echo $row->stream;?>"><?php echo $row->stream;?></option>
                              <?php foreach ($data as $rw):?>
                           <option value="<?=$rw["streamname"]?>"><?=$rw["streamname"]?></option> 
                              <?php endforeach ?>
                        </select>
                              </td>                    
                  </tr>
               </table>
                             
 </div>
                                        <div>
                                      <p style="padding-left: 450px">
                                           <button type="submit" name="update_submit" class="btn btn-primary">Update</button>
                                              </p>
                                     </div>
                        
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
 