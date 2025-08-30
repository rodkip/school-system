<div role="dialog" id="ratingscommentsfinance<?php echo $cnt; ?>" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2 class="modal-title text-center">
                    <b>Finance Department</b>
                </h2>
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
                                    <!-- Finance Comments Table -->
                                    <table class="table table-striped table-bordered table-hover">
                                        <tbody>
                                            <tr>
                                                <td colspan="2">
                                                    <div style="border: none; padding: 10px; background: none; font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif; font-size: 16px;">
                                                        <span style="color: #007BFF;"><?php echo htmlentities($rww->financeratingscomments); ?></span>
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
