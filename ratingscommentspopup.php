<div role="dialog" id="ratingscomments<?php echo $cnnt; ?>" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2 class="modal-title text-center"><b>Operations Ratings Comment</b></h2>
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
                                    <span style="font-size: larger; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;">
                                        <?php echo htmlentities($row->staffname); ?>
                                    </span>
                                    <table class="table table-striped table-bordered table-hover">
                                        <tbody>
                                            <tr>
                                                <td colspan="2">
                                                    <div style="border: none; padding: 10px; background: none; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif; font-size: 16px; max-width: 100%; word-wrap: break-word; white-space: normal; text-align: justify;">
                                                        <span style="color: #007BFF; display: block;">
                                                            <?php echo nl2br(htmlentities($rww->commentsafterfield)); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                            </tr>
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
