<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">New TransportStructure</h2>
            </div>
            <div class="modal-body">
                <!-- actual form -->
                <div class="panel panel-primary">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Advanced Tables -->
                            <div class="panel panel-default">
                                <div class="popup">
                                    <form method="post" enctype="multipart/form-data" action="manage-transportstructure.php">

                                        <div class="form-group">
                                            <table class="table">
                                                <tr>
                                                    <td>
                                                        <label for="academicyear">Academic Year:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="academicyear" id="academicyear" placeholder="Academic Year" value="<?php echo  $currentacademicyear ?>" required>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="td">
                                                        <label for="stagename">Stage:</label>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $smt = $dbh->prepare('SELECT stagename FROM transportstages ORDER BY stagename DESC');
                                                        $smt->execute();
                                                        $data = $smt->fetchAll();
                                                        ?>

                                                        <select name="stagename" value="<?php echo $stagename; ?>" class="form-control" required="required">
                                                            <option value="">--select stage--</option>
                                                            <?php foreach ($data as $rw): ?>
                                                                <option value="<?= $rw["stagename"] ?>"><?= $rw["stagename"] ?></option>
                                                            <?php endforeach ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="firsttermcharge">First Term Charge:</label>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="firsttermcharge" id="firsttermcharge" placeholder="First Term Charge" value="0" step="0.01">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="secondtermcharge">Second Term Charge:</label>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="secondtermcharge" id="secondtermcharge" placeholder="Second Term Charge" value="0" step="0.01">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="thirdtermcharge">Third Term Charge:</label>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="thirdtermcharge" id="thirdtermcharge" placeholder="Third Term Charge" value="0" step="0.01">
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
