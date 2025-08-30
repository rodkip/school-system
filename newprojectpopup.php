<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">New Project-ENTRY</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
                        <form method="post" enctype="multipart/form-data" action="manage-projectdetails.php"> 
                                    
                           <div class="form-group">  
                           <input type="hidden" name="username" value="<?php echo $username?>">                              
                              <table class="table">
                                 <tr>
                                    <td>
                                    <label for="projectname">ProjectName:</label></td><td>
                                       <input type="text" class="form-control" name="projectname" id="projectname" placeholder="Enter projectname here"  required="required">
                                       </td>
                                 </tr>
                                 <tr>
                                    <td>                                 
                                    <label for="projecttype">ProjectType:</label></td><td>
                                 <select name="projecttype" class="form-control" required="required" >    
                                 <option value="">--select projecttype--
                                </option>                               
                                <option value="QuantField">Quant-Field
                                </option>
                                <option value="QuantTelephonic">Quant-Telephonic
                                </option> 
                                <option value="QualField">Qual-Field
                                </option> 
                                <option value="QualField">Qual-Telephonic
                                </option>    
                                <option value="Online">Online
                                </option>             
                              </select> </td>
                                 </tr>
                                 <tr>
                                    <td>
                                    <label for="projectphase">ProjectPhase:</label></td>
                                       <td>                                       
                                       <select name="projectphase" class="form-control" required="required" > 
                                <option value="">--select projectphase--
                                </option>  
                                <option value="OneOff">One-Off
                                </option>           
                                <option value="Baseline">Baseline
                                </option>
                                <option value="Midline">Midline
                                </option> 
                                <option value="Endline">Endline
                                </option>   
                                <option value="Tracker">Tracker
                                </option>             
                              </select>
                                    </td>
                                 </tr>   
                                 <tr>
                                       <td>
                                       <label for="projectyear">ProjectYear:</label></td><td>
                                       <input type="text" class="form-control" name="projectyear" id="projectyear" required="required" placeholder="Enter projectyear here" value="<?php echo $currentfinancialyear; ?>">
                                         </td>
                                          </tr>
                                         <tr>
                                          <td>
                                          <label for="projectstartdate">Project-StartDate:</label></td><td>
                                          <input type="date" class="form-control" name="projectstartdate" id="projectstartdate" placeholder="Enter projectstartdate here" required="required">
                                          </td>
                                          </tr>
                                          <tr>
                                          <td>
                                          <label for="projectendtdate">Project-EndDate:</label></td><td>
                                          <input type="date" class="form-control" name="projectenddate" id="projectenddate" placeholder="Enter project end date here" required="required">
                                          </td>
                                          </tr>
                                          <tr>
                                          <td>
                                          <label for="regions">Regions:</label></td><td>
                                          <input type="text" class="form-control" name="regions" id="regions" placeholder="Enter regions here" value="">
                                          </td>
                                          </tr>
                                          <tr>
                                          <td>
                                          <label for="samplesize">Sample Size:</label></td><td>
                                          <input type="text" class="form-control" name="samplesize" id="samplesize" placeholder="Eg 1000-interviews or 10 FGDs" value="">
                                          </td>
                                          </tr>
                                          <tr>
                                          <td>
                                          <label for="comments">Comments:</label></td><td>
                                          <input type="textarea" class="form-control" name="comments" id="comments" placeholder="Enter comments here" value="">
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
 