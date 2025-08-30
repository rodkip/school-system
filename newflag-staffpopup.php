<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal1" class="modal fade"> 
  <div class="modal-dialog"> 
    <div class="modal-content">
      <div class="modal-header"> 
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;
        </button>
        <h2 class="modal-title" style="text-align: center;">Flag ENTRY
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
                  <form method="post" enctype="multipart/form-data" action="manage-staffflagging.php"> 
                    <div class="form-group">  
                    <input type="hidden" name="username" value="<?php echo $username?>">    
                    <input type="hidden" name="flagstatus" value="Pending"> 
                                  
                    <table class="table">
  <tr>
    <td>
      <label for="idno">IdNo:</label>
    </td>
    <td>
      <input type="text" class="form-control" name="idno" id="idno" placeholder="Enter IdNo or Name here" value="" list="staffdetails-list" autocomplete="off" required autofocus onBlur="displaystaffname()">
      <datalist id="staffdetails-list">
        <?php
        $smt = $dbh->prepare('SELECT * from staffdetails order by id desc');
        $smt->execute();
        $data = $smt->fetchAll();
        ?>
        <?php foreach ($data as $rw): ?>
          <option value="<?= $rw["idno"] ?>">
            <?= $rw["staffname"] ?>
          </option>
        <?php endforeach ?>
      </datalist>
      <span id="displaystaffname" style="font-size:12px;"></span>
    </td>
  </tr>
  
  <tr>
    <td>
      <label for="flagreason">Flag Explanation:</label>
    </td>
    <td>
      <textarea rows="6" class="form-control" name="flagreason" placeholder="Flag explanation"></textarea>
    </td>
  </tr>
</table>
                    </div>
                    <div>
                      <p style="padding-left: 40px">
                        <button type="submit" name="submit-flag" class="btn btn-primary">Submit for Approval
                        </button>
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
