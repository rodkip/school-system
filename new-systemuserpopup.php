<div role="dialog" id="new-systemuserentry" class="modal fade"> 
    <div class="modal-dialog customstaff-modal-xl"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2><b> New System User Entry</b></h2>               
            </div>       
            <div class="modal-body">                      
                    <div class="row">
                        <div class="col-lg-12">                            
                              <form name="changepassword" method="post" onsubmit="return checkpass();" enctype="multipart/form-data" action="manage-userdetails.php"> 
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <table  class="table" width="70%">
                              <tr>
                                <td>
                                  <label for="newfullnames">Full Names:
                                  </label>
                                </td>
                                <td>
                                  <input type="text" name="newfullnames"  class="form-control" required='true' value="">
                                </td>
                                </tr>
                                <tr>
                                <td>
                                  <label for="newusername">User Name
                                  </label>
                                </td>
                                <td>
                                  <input type="text" name="newusername"  class="form-control" required='true' value=""> 
                                </td>
                                </tr>
                                <tr>
                                <td>
                                  <label for="newaccounttype">Account Type:
                                  </label>
                                </td>
                                <td>
                                  <input type="text" class="form-control" name="newaccounttype" id="newaccounttype" value="" list="accounttypelist" required='true'>
                                    <datalist id="accounttypelist">                              
                                      <?php
                                          $smt=$dbh->prepare('SELECT * from accounttypes order by accounttype asc');
                                          $smt->execute();
                                          $data=$smt->fetchAll();
                                          ?> 
                                          
                                      <?php foreach ($data as $rw):?> 
                                        
                                          <option value="<?=$rw["accounttype"]?>"> <?=$rw["accounttype"]?> </option> 
                                          <?php endforeach ?> 
                                    </datalist>
                                </td>
                                </tr>
                                <tr>
                                <td>
                                  <label for="mobilenumber">Mobile No:
                                  </label>
                                </td>
                                <td>
                                  <input type="text" name="mobilenumber" value=""  class="form-control" maxlength='10'  pattern="[0-9]+"> 
                                </td>
                              </tr>
                              <tr>
                                <td>
                                  <label for="emailaddress">Email Address:
                                  </label>
                                </td>
                                <td>
                                  <input type="email" name="emailaddress" value="" class="form-control" required>
                                </td>
                                </tr>
                             
                                <tr>
                                  <td>
                                  <label for="newpassword">New Password:
                                  </label>
                                </td>
                                <td>
                                  <input type="password" name="newpassword"  class="form-control" required="true" value="">
                                </td>
                                </tr>
                                <tr>
                                <td>
                                  <label for="confirmpassword">Confirm Password:
                                  </label>
                                </td>
                                <td>
                                  <input type="password" name="confirmpassword" id="confirmpassword"   class="form-control" required="true" value=""> 
                                </td>
                                </tr>
                                <tr>
                                <td>
                                  <label for="profilepic">Profile Pic:
                                  </label>
                                </td>
                                <td>
                                  <input type="file" name="profilepic" value="" class="form-control" >
                                </td>
                                </tr>
                                <tr>
                                <td colspan="2">
                                  <button type="submit" class="btn btn-primary" name="submit" id="submit" action="manage-userdetails.php" autofocus>Submit
                                  </button>                                  
                                </td>
                              </tr>
                            </table>
                          </form>
                        </span> 
                                </div>                 
                            </div>             
                        </div>             
                    </div> 
                </div>
            </div> 
    
        </div> 
    </div>
</div>
