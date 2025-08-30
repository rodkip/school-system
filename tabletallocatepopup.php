<div id="tabletallocatepopup" class="modal fade" aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" aria-hidden="true" data-dismiss="modal">&times;
        </button>
        <h2 class="modal-title" style="text-align: center;">Gadget Allocation
        </h2>
      </div>
      <div class="modal-body">
        <div class="panel panel-primary">
          <div class="row">
            <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="popup">
                  <form method="POST" enctype="multipart/form-data" action="manage-tabletsallocation.php">   
                    <div class="form-group">
                      <table class="table">
                      <input type="hidden" name="projectfullname" value="<?php echo $searchprojectfullname; ?>">    
                      <input type="hidden" name="username" value="<?php echo $username?>">                                                                 
                        <tr>
                        <td><label for="tabletserialno">Tablet Barcode/Serial No:</label></td>
                          <td>                                 
                            <input type="text" class="form-control" name="tabletserialno" id="tabletserialno" placeholder="Search by BarCode/Serial No" value="" list="tabletlistallocate" autocomplete="off" required="required" onBlur="displaytabletallocatedetails()">
                            <datalist id="tabletlistallocate">
                              <?php
                              $smt=$dbh->prepare('SELECT * from tabletsdetails where status="In Storage" order by tabletserialno asc');
                              $smt->execute();
                              $datareturn=$smt->fetchAll();
                              ?>
                              <?php foreach ($datareturn as $returndata):?>
                              <option value="<?=$returndata["tabletserialno"]?>">
                                <?=$returndata["barcode"]?>- 
                                <?=$returndata["tabletserialno"]?>- 
                                <?=$returndata["type"]?>
                                <?=$returndata["description"]?>- 
                                <?=$returndata["model"]?>
                              </option>
                              <?php endforeach ?>  
                            </datalist>
                            <span id="displaytabletallocatedetails" style="font-size:12px;"></span>
                          </td>
                        </tr>                                                          
                        <tr>
                        <td><label for="idno">Field Staff IdNo:</label></td>
                          <td>                                 
                            <input type="text" class="form-control" name="idno" id="idno" placeholder="Select IdNo" value="" list="projectteam-list" autocomplete="off" required="required" onBlur="displaystaffname()">
                            <datalist id="projectteam-list">
                              <?php
                              $smt=$dbh->prepare("SELECT projectlistentries.idno,projectlistentries.projectfullname, staffdetails.staffname from projectlistentries join staffdetails ON projectlistentries.idno = staffdetails.idno where projectfullname= '$searchprojectfullname' order by idno asc");
                              $smt->execute();
                              $data=$smt->fetchAll();
                              ?>
                                                            <?php foreach ($data as $rw):?>
                              <option value="<?=$rw["idno"]?>">
                                <?=$rw["idno"]?>- 
                                <?=$rw["staffname"]?>
                              </option>
                              <?php endforeach ?>  
                            </datalist>
                            <span id="displaystaffname" style="font-size:12px;"></span>
                          </td>
                        </tr>
                        <tr>
                        <td>
                            <label for="allocatecharger">Charger allocated?:</label>
                        </td>
                        <td>
                            <input type="checkbox" id="allocatecharger" name="allocatecharger" value="1" align="left">
                        </td>
                        </tr>
                        <tr>
                        <tr>
                        <td>
                            <label for="allocatebadge">Badge allocated?:</label>
                        </td>
                        <td>
                            <input type="checkbox" id="allocatebadge" name="allocatebadge" value="1" align="left">
                        </td>
                        </tr>
                        <tr>
                        <td>
                            <label for="allocatedate">Allocation Date:</label>
                        </td>
                        <td>                    
                            <input type="datetime-local" class="form-control" name="allocatedate" id="allocatedate" autocomplete="off" required="required">
                        </td>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                // Get the current UTC date and time
                                var currentDateTime = new Date();

                                // Adjust for East Africa Time (UTC+3)
                                var eastAfricaTimeOffset = 3 * 60 * 60 * 1000; // 3 hours in milliseconds
                                var eastAfricaTime = new Date(currentDateTime.getTime() + eastAfricaTimeOffset);

                                // Format it to yyyy-mm-ddTHH:MM (up to the minutes)
                                var formattedDateTime = eastAfricaTime.toISOString().slice(0, 16); 

                                // Set the value of the datetime input
                                document.getElementById('allocatedate').value = formattedDateTime;
                            });
                        </script>

                       </td>
                        </tr> 
                        <tr>
                        <td>
                            <label for="allocatedescription">Status of the Gadget:</label>
                        </td>
                        <td>
                            <textarea rows="6" class="form-control" name="allocatedescription" placeholder="Provide any information about the gadget during issuance" required></textarea>
                        </td>
                        </tr>
                        <tr>
                          <td colspan="2">
                            <button type="submit" name="allocatetablet" class="btn btn-primary">Allocate
                            </button>
                          </td>
                        </tr>
                      </table>
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
