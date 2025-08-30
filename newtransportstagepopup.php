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
                           <form method="post" enctype="multipart/form-data" action="manage-transportstages.php">                               
                           <table  class="table" >
                              <tr>
                                 <td>
                                    <label for="stagename">Stage Name:</label></td><td >
                                    <input type="text" class="form-control" name="stagename" id="stagename" required="required" placeholder="stagename" value="<?php echo $stagename; ?>">
                                 </td>
                              </tr>
                              <tr>
                                 <td>
                                    <label for="stagecomments">Comments:</label></td><td >
                                    <textarea class="form-control" name="stagecomments" id="stagecomments" placeholder="Enter years of stage comments if any" rows="3"></textarea>
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
 