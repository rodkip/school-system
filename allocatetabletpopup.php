<div id="allocatetabletpopup" class="modal fade" aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-hidden="true" data-dismiss="modal">&times;</button>
                <h2 class="modal-title" style="text-align: center;">View-Team per region</h2>
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
                                                <tr>
                                                    <td colspan="2" style="color:blue">Select project team Member</td>
                                                </tr>                                               
                                                <tr>
                                    <td>                                 
                                    <input type="text" class="form-control" name="idno" id="idno" placeholder="Select idno" value="" list="projectteam-list" autocomplete="off" required="required">
                  <datalist id="projectteam-list">
                    <?php
$smt=$dbh->prepare('SELECT * from projectdetails order by projectfullname asc');
$smt->execute();
$data=$smt->fetchAll();
?>
                    <?php foreach ($data as $rw):?>
                    <option value="<?=$rw["projectfullname"]?>">
                      <?=$rw["projectfullname"]?>
                    </option>
                    <?php endforeach ?>  
                  </datalist>
                 </td>
                                 </tr>
                                 <tr>
                                                    <td colspan="2" style="color:blue">Select SerialNo</td>
                                                </tr>                                               
                                                <tr>
                                    <td>                                 
                                    <input type="text" class="form-control" name="tabletserialno" id="tabletserialno" placeholder="Select tabletserialno" value="" list="tabletserialno-list" autocomplete="off" required="required">
                                    <datalist id="tabletserialno-list">
                    <?php
$smt=$dbh->prepare('SELECT * from tabletsdetails where tabletstatus="Available(In Office)" order by tabletserialno asc');
$smt->execute();
$data=$smt->fetchAll();
?>
                    <?php foreach ($data as $rw):?>
                    <option value="<?=$rw["tabletserialno"]?>">
                      <?=$rw["tabletserialno"]?>- <?=$rw["tabletmanufacturer"]?>- <?=$rw["tabletmodel"]?>
                    </option>
                    <?php endforeach ?>  
                  </datalist>
                 </td>
                                 </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <button type="submit" name="allocatetablet" class="btn btn-primary">Post</button>
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
