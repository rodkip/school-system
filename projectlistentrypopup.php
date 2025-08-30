<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">New TeamList-ENTRY</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
   <form method="POST" enctype="multipart/form-data" action="manage-projectlistentries.php">
                      <div class="form-group"> 
                        <input type="hidden" name="id" value="">               
                        <table  class="table" width="70%">
                          <tr>
                            <td>
                              <label for="idno">Staff IdNO:
                              </label>
                            </td>
                            <td>
                              <input type="text" class="form-control" name="idno" id="idno" placeholder="Enter IdNo or Name here" value="" list="staffdetails-list" autocomplete="off" autofocus required="required" onBlur="displaystaffname()">
                              <datalist id="staffdetails-list">
                                <?php
                                  $smt=$dbh->prepare('SELECT * from staffdetails order by id desc');
                                  $smt->execute();
                                  $data=$smt->fetchAll();
                                  ?>
                                <?php foreach ($data as $rw):?>
                                <option value="<?=$rw["idno"]?>">
                                  <?=$rw["staffname"]?>
                                </option>
                                <?php endforeach ?>  
                              </datalist>
                              <span id="displaystaffname" style="font-size:12px;"></span>
                            </td>
                                </tr>
                                <tr>
                            <td>
                              <label for="projectfullname">Project FullName:
                              </label>
                            </td>
                            <td>
                              <?php
                                $smt=$dbh->prepare('SELECT * from projectdetails order by id desc');
                                $smt->execute();
                                $data=$smt->fetchAll();
                                ?>
                              <select name="projectfullname"  value="" class="form-control" required="required"> 
                                <option value="" disabled selected>Select projectFullName
                                </option>
                                <?php foreach ($data as $rw):?>
                                <option value="<?=$rw["projectfullname"]?>">
                                  <?=$rw["projectfullname"]?>
                                </option> 
                                <?php endforeach ?>
                              </select>
                            </td>
                            </tr>
                                <tr>
                            <td>
                              <label for="projectdesignation">Project Designation:
                              </label>
                            </td>
                            <td>
                            <input type="text" class="form-control" name="projectdesignation" id="projectdesignation" placeholder="Select Designation" value="" list="projectdesignation-list" >
                  <datalist id="projectdesignation-list">
                    <?php
$smt=$dbh->prepare('SELECT * from projectdesignation order by projectdesignation asc');
$smt->execute();
$data=$smt->fetchAll();
?>
                    <?php foreach ($data as $rw):?>
                    <option value="<?=$rw["projectdesignation"]?>">
                      <?=$rw["projectdesignation"]?>
                    </option>
                    <?php endforeach ?>  
                  </datalist>
                            </td> 
                            </tr>
                                <tr>
                            <td>
                              <label for="projecttier">Tier Level:
                              </label>
                            </td>
                            <td>
                              <select name="projecttier"  class="form-control"> 
                                <option value="" disabled selected>Select an option
                                </option>
                                <option value="Tier 1">Tier 1
                                </option> 
                                <option value="Tier 2">Tier 2
                                </option>
                                <option value="Tier 3">Tier 3
                                </option>
                                <option value="Team Leader">Team Leader
                                </option> 
                                <option value="Supervision">Supervision
                                </option> 
                                <option value="YIA">YIA
                                </option> 
                                <option value="Moderator">Moderator
                                </option>
                                <option value="Note-Taker">Note-taker
                                </option>
                                <option value="Recruiter">Recruiter
                                </option>
                              </select>
                            </td> 
                          </tr>
                          <tr> 
                            <td>
                              <label for="ratings">Ratings:
                              </label>
                            </td>
                            <td>
                              <select name="ratings"  class="form-control" > 
                                <option value="" disabled selected>Select an option
                                </option>
                                <option value="5-Outstanding">5-Outstanding
                                </option>  
                                <option value="4-Exceeds Expectations">4-Exceeds Expectations
                                </option> 
                                <option value="3-Meets Expectations">3-Meets Expectations
                                </option>                                
                                <option value="2-Needs Improvement">2-Needs Improvement
                                </option>
                                <option value="1-Below Expectations">1-Below Expectations
                                </option>                     
                              </select>
                            </td>    
                            </tr>
                                <tr> 
                            <td>
                              <label for="comments">BeforeField-Comments:
                              </label>
                            </td>
                            <td>
                              <input type="textarea" class="form-control" name="comments" id="comments" placeholder="Enter comments here" value="">
                            </td>
                            </tr>
                                <tr>
                            <td>
                              <label for="region">Region/County:
                              </label>
                            </td>
                            <td>
                              <input type="textarea" class="form-control" name="region" id="region" placeholder="Enter region/county here" value="">
                            </td>
                            </tr>
                                <tr>
                            <td>  
                              <button type="submit" name="manualteamlistentry" class="btn btn-primary">Submit
                              </button>
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
 