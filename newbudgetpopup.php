<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title text-center">Create Budget Allocation</h2> 
            </div> 
            <div class="modal-body">
                <div class="panel panel-primary">                
                    <div class="row">
                        <div class="col-lg-12">            
                            <div class="panel panel-default">                     
                                <div class="popup">                   
                                    <form method="post" enctype="multipart/form-data" action="manage-budgetstructure.php"> 
                                    <input type="hidden" name="username" value="<?php echo $username ?>">
                                        <div class="form-group">                                
                                            <table class="table">
                                                <tr>
                                                    <td>
                                                        <label for="financialyear">Financial Year:</label>
                                                    </td>
                                                    <td><input type="text" class="form-control" name="financialyear" id="financialyear" placeholder="Financial Year" value="<?php echo $financialyear; ?>" required></td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="votehead">Votehead:</label>
                                                    </td>
                                                    <td>
                                                    <select name="votehead" class="form-control" required>
                                                         <option value="">--select votehead--</option>
                                                         <?php
                                                         $smt = $dbh->prepare('SELECT votehead FROM voteheads');
                                                         $smt->execute();
                                                         foreach ($smt->fetchAll() as $rw): ?>
                                                         <option value="<?= $rw['votehead'] ?>"><?= $rw['votehead'] ?></option>
                                                         <?php endforeach; ?>
                                                      </select>
                                                   </td>
                                               
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="allocated_amount">Allocated Amount:</label>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="allocated_amount" placeholder="Enter amount" value="0" required>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" name="submit_budget" class="btn btn-primary">Submit</button>
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
