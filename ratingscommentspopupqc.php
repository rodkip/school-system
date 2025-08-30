<div role="dialog" id="ratingscommentsqc<?php echo $cnt; ?>" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2 class="modal-title text-center"><b>QC Rating</b></h2>
            </div>
            <h3>
            <?php echo htmlentities($rww->staffname); ?>
          </h3> 
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="panel panel-primary">
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Advanced Tables -->
                            <div class="panel panel-default">
                                <div class="popup">                                 
                                                               
                                    <!-- General Comments Table -->
                                    <table class="table table-striped table-bordered table-hover">
                                        <tbody>
                                            <tr>
                                                <td colspan="2">                                                   
                                                    <div style="border: none; padding: 10px; background: none; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif; font-size: 16px;">
                                                        <span style="color: #007BFF;"><?php echo htmlentities($rww->qcratingscomments); ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b>Total Interviews Done</b></td>
                                                <td><?php echo htmlentities($rww->totalinterviewsdone); ?></td>
                                            </tr>
                                            <tr>
                                                <td><b>Total Interviews Deleted</b></td>
                                                <td><?php echo htmlentities($rww->totalinterviewsdeleted); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <!-- Deleted Interviews Breakdown -->
                                    <p><b>Deleted Interviews Breakdown</b></p>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Rating Topic</th>
                                                <th>Deleted Interviews</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sqlratingdeleted = "SELECT * FROM projectteamratingsdeletesqc WHERE idno = '$rww->idno' AND projectfullname = '$rww->projectfullname'";
                                            $queryratingdeleted = $dbh->prepare($sqlratingdeleted);
                                            $queryratingdeleted->execute();
                                            $resultsratingdeleted = $queryratingdeleted->fetchAll(PDO::FETCH_OBJ);
                                            $cnnnt = 1;
                                            if ($queryratingdeleted->rowCount() > 0) {
                                                foreach ($resultsratingdeleted as $rwww) {
                                            ?>
                                            <tr>
                                                <td><?php echo $cnnnt; ?></td>
                                                <td><?php echo htmlentities($rwww->ratingtopic); ?></td>
                                                <td><?php echo htmlentities($rwww->deletedinterviews); ?></td>
                                            </tr>
                                            <?php $cnnnt++; }} ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
