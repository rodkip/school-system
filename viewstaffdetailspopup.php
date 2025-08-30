<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="otherstaffdetails<?php echo $cnt; ?>" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h2 class="modal-title text-center">Staff Details</h2>
                <button aria-hidden="true" data-dismiss="modal" class="close text-white" type="button">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div class="row">
                    <!-- Left Column: Staff Details -->
                   
                        <div class="card-body">
                            <!-- Personal Details -->
                            <div class="section mb-4">
                                <h4 class="text-primary mb-3">Personal Details</h4>
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <td><strong>Full Name:</strong></td>
                                        <td><?php echo htmlspecialchars($row->staffname, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>ID No:</strong></td>
                                        <td><?php echo htmlspecialchars($row->staffidno, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                 
                                    <tr>
                                        <td><strong>Gender:</strong></td>
                                        <td><?php echo htmlspecialchars($row->gender, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Marital Status:</strong></td>
                                        <td><?php echo htmlspecialchars($row->maritalstatus, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Education Level:</strong></td>
                                        <td><?php echo htmlspecialchars($row->educationlevel, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Employment Details -->
                            <div class="section mb-4">
                                <h4 class="text-primary mb-3">Employment Details</h4>
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <td><strong>Staff Title:</strong></td>
                                        <td><?php echo htmlspecialchars($row->stafftitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Experience:</strong></td>
                                        <td><?php echo htmlspecialchars($row->experience, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Employment Date:</strong></td>
                                        <td><?php echo htmlspecialchars($row->employmentdate, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Health Details -->
                            <div class="section mb-4">
                                <h4 class="text-primary mb-3">Health Details</h4>
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <td><strong>Health Issue:</strong></td>
                                        <td><?php echo htmlspecialchars($row->healthissue, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Financial Details -->
                            <div class="section mb-4">
                                <h4 class="text-primary mb-3">Financial Details</h4>
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <td><strong>Bank:</strong></td>
                                        <td><?php echo htmlspecialchars($row->bank, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bank Account No:</strong></td>
                                        <td><?php echo htmlspecialchars($row->bankaccno, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>NSSF No:</strong></td>
                                        <td><?php echo htmlspecialchars($row->nssfaccno, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>NHIF No:</strong></td>
                                        <td><?php echo htmlspecialchars($row->nhifaccno, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>