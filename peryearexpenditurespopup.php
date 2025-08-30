<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="peryearexpenditures" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Select Year</h2>
            </div>
            <div class="modal-body">
                <!-- Actual Form -->
                <div class="panel panel-primary">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Advanced Tables -->
                            <div class="panel panel-default">
                                <div class="popup">
                                    <form method="post" enctype="multipart/form-data" action="reportperyearexpenditures.php">
                                        <div class="form-group">
                                            <table class="table">
                                                <tr>
                                                    <td><label for="stagefullname">Financial Year:</label></td>
                                                    <td>
                                                        <?php
                                                            $smt = $dbh->prepare('SELECT financialyear FROM expendituresdetails GROUP BY financialyear ORDER BY financialyear DESC');
                                                            $smt->execute();
                                                            $data = $smt->fetchAll();
                                                        ?>
                                                        <select name="financialyear" class="form-control" required="required">
                                                            <option value="">--select financialyear--</option>
                                                            <?php foreach ($data as $rw): ?>
                                                                <option value="<?= $rw["financialyear"] ?>"><?= $rw["financialyear"] ?></option>
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
