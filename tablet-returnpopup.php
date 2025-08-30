<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
  <div class="modal-dialog"> 
    <div class="modal-content">
      <div class="modal-header"> 
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;
        </button>
        <h2 class="modal-title" style="text-align: center;"> Tablet Return
        </h2> 
      </div> 
      <div class="modal-body">
        <!-- actual form --> 
        <div class="panel panel-primary">                
          <div class="row">
            <div class="col-lg-12">            
              <!-- Advanced Tables -->
              <div class="panel panel-default">                     
                <div class="popup">                   
                  <form method="post" enctype="multipart/form-data" action="manage-tabletsallocation.php"> 
                    <div class="form-group">                                
                      <table class="table">
                        <tr>
                          <td>                            
                            <label for="tabletserialnoreturn">Tablet SerialNo:
                            </label>
                          </td>
                          <td>
                            <input type="text" class="form-control" name="tabletserialnoreturn" id="tabletserialnoreturn" value="<?php echo htmlentities($row->tabletserialno); ?>" readonly><?php echo htmlentities($row->tabletserialno); ?>  
                            </td> 
                            <td>
                            <input type="text" class="form-control" name="tabletserialnoreturn" id="tabletserialnoreturn" value="<?php echo htmlentities($row->tabletserialno); ?>" readonly><?php echo htmlentities($row->tabletserialno); ?>  
                            </td>                          
                        </tr>  
                                                                          
                      </table>
                    </div>
                    <div>

                        
                    
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
