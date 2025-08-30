<div id="peryearfeebalanceperclass" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header text-center"> 
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">
                    <i class="fas fa-scale-balanced me-2" style="color: #6f42c1;"></i>
                    Whole School Fee Payments Summary
                </h3>
            </div> 
            <div class="modal-body">
                <div class="panel panel-primary">                
                    <div class="panel panel-default">                     
                        <div class="popup">                   
                            <form method="post" enctype="multipart/form-data" id="reportForm">
                                <div class="form-group">                                
                                    <table class="table">
                                        <tr>
                                            <td><label for="academicyear">Academic Year:</label></td>
                                            <td>
                                                <?php
                                                    $smt = $dbh->prepare('SELECT academicyear FROM classdetails GROUP BY academicyear ORDER BY academicyear DESC');
                                                    $smt->execute();
                                                    $data = $smt->fetchAll();
                                                ?>
                                                <select name="academicyear" id="academicYearSelect" class="form-control" required>
                                                    <option value="">-- Select Academic Year --</option>
                                                    <?php foreach ($data as $rw): ?>
                                                        <option value="<?= htmlspecialchars($rw["academicyear"]) ?>"><?= htmlspecialchars($rw["academicyear"]) ?></option> 
                                                    <?php endforeach ?>
                                                </select>
                                            </td>
                                        </tr> 
                                    </table>
                                </div>
                                <div class="text-center">                                   
                                    <button type="button" onclick="generateReport('reportfeebalanceallschool1stterm.php')" 
                                            class="btn btn-outline-success btn-gradient first-term-btn">
                                        <i class="fas fa-leaf me-1"></i> 1st-Term Report
                                    </button>
                                    <button type="button" onclick="generateReport('reportfeebalanceallschool2ndterm.php')" 
                                            class="btn btn-outline-warning btn-gradient second-term-btn">
                                        <i class="fas fa-seedling me-1"></i> 2nd-Term Report
                                    </button>
                                    <button type="button" onclick="generateReport('reportfeebalanceallschool3rdterm.php')" 
                                            class="btn btn-outline-danger btn-gradient third-term-btn">
                                        <i class="fas fa-tree me-1"></i> 3rd-Term Report
                                    </button>
                                     <button type="button" onclick="generateReport('reportfeebalanceallschool.php')" 
                                            class="btn btn-outline-primary btn-gradient full-year-btn">
                                        <i class="fas fa-file-alt me-1"></i> Full-Year Report
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div> 
                </div> 
            </div> 
        </div> 
    </div>
</div>

<style>
    .btn-gradient {
        transition: all 0.3s ease;
        border-width: 2px;
        font-weight: 500;
        min-width: 160px;
        margin: 5px;
    }
    
    .full-year-btn {
        border-color: #3a7bd5;
        color: #3a7bd5;
    }
    .full-year-btn:hover {
        background: linear-gradient(to right, #3a7bd5, #00d2ff);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 8px rgba(58, 123, 213, 0.3);
    }
    
    .first-term-btn {
        border-color: #28a745;
        color: #28a745;
    }
    .first-term-btn:hover {
        background: linear-gradient(to right, #28a745, #7bed9f);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }
    
    .second-term-btn {
        border-color: #ffc107;
        color: #ffc107;
    }
    .second-term-btn:hover {
        background: linear-gradient(to right, #ffc107, #ffde7d);
        color: #212529;
        border-color: transparent;
        box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
    }
    
    .third-term-btn {
        border-color: #dc3545;
        color: #dc3545;
    }
    .third-term-btn:hover {
        background: linear-gradient(to right, #dc3545, #ff6b6b);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
    }
    
    .btn-gradient i {
        transition: transform 0.3s ease;
    }
    .btn-gradient:hover i {
        transform: scale(1.2);
    }
</style>

<script>
function generateReport(reportUrl) {
    const academicyear = document.getElementById('academicYearSelect').value;
    const gradefullname = '<?= isset($searchgradefullname) ? urlencode($searchgradefullname) : "" ?>';
    
    if (!academicyear) {
        alert('Please select an academic year');
        return;
    }
    
    // Construct the URL with parameters
    const url = `${reportUrl}?gradefullname=${gradefullname}&academicyear=${encodeURIComponent(academicyear)}`;
    
    // Open in new tab
    window.open(url, '_blank');
    
    // Close the modal
    $('#peryearfeebalanceperclass').modal('hide');
}
</script>