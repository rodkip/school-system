<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="staffdetailsprintoutpopup" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Select One</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
                        <form method="post" enctype="multipart/form-data" action="receiptstaffdetailsprintoutpopup.php"> 
                                    
                           <div class="form-group">                                
                              <table class="table">

                                 <tr>
                                    <td>
                                       <label for="stagefullname">Academic Year:</label></td><td>
                                       <?php
                                    $smt=$dbh->prepare('SELECT available FROM staffdetails GROUP BY available order by available desc');
                                    $smt->execute();
                                    $data=$smt->fetchAll();
                                    ?>
                                          <select name="available"  class="form-control" required="required"> 
                                          <option value="">--select available--</option>
                                          <?php foreach ($data as $rw):?>
                                          <option value="<?=$rw["available"]?>"><?=$rw["available"]?></option> 
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
 