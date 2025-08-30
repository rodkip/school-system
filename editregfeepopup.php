<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="editregfee" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title text-center">
                    <i class="fa fa-credit-card"></i> Edit RegFee Payment
                </h2>
            </div>
            
            <div class="modal-body">
                <?php if (!empty($rlt->studentadmno)): ?>
                    <form method="post" enctype="multipart/form-data" action="manage-feepayments.php">
                        <input type="hidden" name="id" value="<?php echo $row->id ?>">
                        <input type="hidden" name="username" value="<?php echo $username ?>">
                        
                        <table class="table">
                            <tr>
                                <td>AdmNo:</td>
                                <td>
                                    <input type="text" class="form-control" name="studentadmno" id="studentadmno" 
                                           value="<?php echo $rlt->studentadmno; ?>" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>Learner's Name:</td>
                                <td>
                                    <input type="text" class="form-control" name="studentname" id="studentname" 
                                           value="<?php echo $rlt->studentname; ?>" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td>RegFee</td>
                                <td>
                                    <?php
                                    $admno = $rlt->studentadmno;
                                    $amount = '';
                                    if (!empty($admno)) {
                                        $sql = "SELECT bank,amount FROM regfeepayments WHERE studentadmno = ? ORDER BY id DESC LIMIT 1";
                                        $query = $dbh->prepare($sql);
                                        $query->execute([$admno]);
                                        $regfeeamount = $query->fetch(PDO::FETCH_OBJ);
                                        if ($regfeeamount) {
                                            $amount = htmlspecialchars($regfeeamount->amount);
                                            $bank = htmlspecialchars($regfeeamount->bank);
                                        }
                                    }
                                    ?>
                                    <input type="number" class="form-control" name="cash" id="cash" 
                                           placeholder="Enter cash" value="<?php echo $amount; ?>" 
                                           required pattern="\d*" title="Numbers only, please." autofocus>
                                </td>
                            </tr>
                            <tr>
                              <td>Mode</td>
                              <td>
                                <select name="bank" class="form-control" required>
                                  <option value="<?php echo $bank; ?>"><?php echo $bank; ?></option>
                                  <?php
                                  $smt = $dbh->prepare('SELECT bankname from bankdetails');
                                  $smt->execute();
                                  $data = $smt->fetchAll();
                                  foreach ($data as $rw): ?>
                                    <option value="<?= $rw["bankname"] ?>"><?= $rw["bankname"] ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <button type="submit" name="editregfee_submit" class="btn btn-primary">
                                        Update
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
                <?php else: ?>
                    <p>Please select a valid AdmNo to receive payment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>