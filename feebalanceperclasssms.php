<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if user is logged in
if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
    exit();
}

$searchgradefullname = isset($_POST['gradefullname']) ? trim($_POST['gradefullname']) : '';
$searchstreamname = isset($_POST['streamname']) ? trim($_POST['streamname']) : '';
$showAllStreams = isset($_POST['show_all_streams']) ? true : false;

if (isset($_POST['send_sms'])) {
    $selectedStudents = isset($_POST['selected_students']) ? $_POST['selected_students'] : [];

    if (!empty($selectedStudents)) {
        $templateMessage = isset($_POST['sms_message']) ? trim($_POST['sms_message']) : '';
        if (empty($templateMessage)) {
            $templateMessage = "Dear Parent, your child {name} (Adm: {admno}) has outstanding fee balance of KSh {balance}. Kindly clear to avoid inconvenience.";
        }

        $placeholders = implode(',', array_fill(0, count($selectedStudents), '?'));
        $sql = "SELECT 
                    s.studentname, 
                    s.studentadmno, 
                    e.stream, 
                    p.parentname, 
                    p.parentcontact,
                    fb.firsttermbal, 
                    fb.secondtermbal, 
                    fb.thirdtermbal, 
                    fb.yearlybal
                FROM studentdetails s
                JOIN parentdetails p ON s.motherparentno = p.parentno
                JOIN classentries e ON s.studentadmno = e.studentadmno
                JOIN feebalances fb ON s.studentadmno = fb.studentadmno
                WHERE s.studentadmno IN ($placeholders) 
                AND e.gradefullname = ?";

        if (!empty($searchstreamname) && !$showAllStreams) {
            $sql .= " AND e.stream = ?";
        }

        $query = $dbh->prepare($sql);
        $params = $selectedStudents;
        array_push($params, $searchgradefullname);
        if (!empty($searchstreamname) && !$showAllStreams) {
            array_push($params, $searchstreamname);
        }

        $query->execute($params);
        $recipients = $query->fetchAll(PDO::FETCH_OBJ);

        $successCount = 0;
        $failedNumbers = [];
        $sentDetails = [];

        foreach ($recipients as $recipient) {
            if (!empty($recipient->parentcontact)) {
                $tokenMap = [
                    '{name}'     => $recipient->studentname,
                    '{admno}'    => $recipient->studentadmno,
                    '{grade}'    => $searchgradefullname,
                    '{stream}'   => $recipient->stream,
                    '{balance}'  => number_format((float) $recipient->yearlybal),
                    '{parent}'   => $recipient->parentname,
                ];

                $personalizedMsg = $templateMessage;
                foreach ($tokenMap as $placeholder => $value) {
                    $personalizedMsg = str_replace($placeholder, $value, $personalizedMsg);
                }

                $personalizedMsg .= "\n\n- {$schoolname}";
                $mobilenumber = $recipient->parentcontact;
                $message = $personalizedMsg;

                // Set variables for smssettings.php to use
                $GLOBALS['mobilenumber'] = $mobilenumber;
                $GLOBALS['message'] = $message;

                // Include the SMS sending script
                include('includes/smssettings.php');

                // Check the logs to determine if send was successful
                $logContents = @file_get_contents('talksasa_response.log');
                $lastEntry = $logContents ? explode("\n\n", $logContents) : [];
                $lastEntry = end($lastEntry);
                
                if (strpos($lastEntry, '"status":"success"') !== false) {
                    $successCount++;
                    $sentDetails[] = "Sent to {$recipient->parentname} ({$mobilenumber}) for {$recipient->studentname}";
                } else {
                    $failedNumbers[] = "Failed to send to {$recipient->parentname} ({$mobilenumber})";
                }

                // Clean up global variables
                unset($GLOBALS['mobilenumber']);
                unset($GLOBALS['message']);
            } else {
                $failedNumbers[] = "No contact for {$recipient->studentname}";
            }
        }

        if ($successCount > 0) {
            $_SESSION['sms_success'] = "Successfully sent $successCount SMS notifications";
            $_SESSION['sms_details'] = implode("<br>", $sentDetails);
        }

        if (!empty($failedNumbers)) {
            $_SESSION['sms_error'] = implode("<br>", $failedNumbers);
        }
    } else {
        $_SESSION['sms_error'] = "No students selected for SMS";
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?gradefullname=" . urlencode($searchgradefullname) . "&streamname=" . urlencode($searchstreamname));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipmetz-SMS | Per Class Fee Balance</title>
    
    <!-- CSS Links -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />
    <style>
        .checkbox-cell { width: 20px; text-align: center; }
        .sms-preview { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-bottom: 15px; }
        .sms-counter { font-size: 12px; color: #6c757d; text-align: right; }
        .highlight-balance { font-weight: bold; color: #dc3545; }
        .select-all-container { margin-bottom: 10px; }
        .balance-col { min-width: 90px; }
        .contact-col { min-width: 150px; }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include_once('includes/header.php'); ?>
        <?php include_once('includes/sidebar.php'); ?>
        
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="page-header">
                        <i class="fa fa-money"></i> Fee Balances SMS Notification
                        <small class="text-muted">Send balance reminders to parents</small>
                    </h2>
                </div>
            </div>
            
            <!-- SMS Notification Alerts -->
            <?php if (isset($_SESSION['sms_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Success!</strong> <?php echo $_SESSION['sms_success']; unset($_SESSION['sms_success']); ?>
                    <?php if (isset($_SESSION['sms_details'])): ?>
                        <div class="mt-2 small"><?php echo $_SESSION['sms_details']; unset($_SESSION['sms_details']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['sms_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Error!</strong> <?php echo $_SESSION['sms_error']; unset($_SESSION['sms_error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="panel panel-primary">
                <div class="panel-body">
                    <form method="POST" class="form-inline justify-content-between align-items-center mb-3 p-3 bg-light border rounded shadow-sm flex-wrap">
                        <div class="form-group d-flex align-items-center flex-wrap">
                            <label for="gradefullname" class="mr-2 font-weight-bold">Select GRADE:</label>
                            <select name="gradefullname" id="gradefullname" class="form-control mr-3 mb-2 mb-md-0" required>
                                <option value="">-- select grade --</option>
                                <?php
                                $stmt = $dbh->prepare('SELECT gradefullname FROM classdetails ORDER BY gradefullname DESC');
                                $stmt->execute();
                                while ($grade = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= htmlentities($grade['gradefullname']) ?>" <?= ($grade['gradefullname'] == $searchgradefullname) ? 'selected' : '' ?>>
                                        <?= htmlentities($grade['gradefullname']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <label for="streamname" class="mr-2 font-weight-bold">Select STREAM:</label>
                            <select name="streamname" id="streamname" class="form-control mr-3 mb-2 mb-md-0" <?= $showAllStreams ? 'disabled' : '' ?>>
                                <option value="">-- select stream --</option>
                                <?php
                                $stmt = $dbh->prepare('SELECT streamname FROM streams ORDER BY streamname DESC');
                                $stmt->execute();
                                while ($stream = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?= htmlentities($stream['streamname']) ?>" <?= ($stream['streamname'] == $searchstreamname) ? 'selected' : '' ?>>
                                        <?= htmlentities($stream['streamname']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            
                            <input type="checkbox" class="form-check-input" id="show_all_streams" name="show_all_streams" <?= $showAllStreams ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_all_streams">Show all streams</label>
                
                            <button type="submit" name="submit" class="btn btn-primary mr-3">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </form>

                    <script>
                        // Enable/disable stream select based on checkbox
                        document.getElementById('show_all_streams').addEventListener('change', function() {
                            document.getElementById('streamname').disabled = this.checked;
                        });
                    </script>
                    
                    <?php if (!empty($searchgradefullname)): ?>
                        <!-- SMS Form -->
                        <form method="post" id="smsForm" class="mb-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h4 class="mb-0"><i class="fa fa-envelope"></i> SMS Notification Settings</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="sms_message">Message Template:</label>
                                        <textarea class="form-control" id="sms_message" name="sms_message" rows="3" maxlength="160">Hello {parent}, your child {name} (Adm: {admno}) in {grade} {stream} has outstanding fee balance of KSh {balance}. Kindly clear to avoid inconvenience.</textarea>
                                        <div class="sms-counter"><span id="charCount">0</span>/160 characters</div>
                                        <small class="form-text text-muted">
                                            Available placeholders: {name}, {admno}, {grade}, {stream}, {balance}, {parent}
                                        </small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="button" class="btn btn-secondary" id="previewSms">
                                            <i class="fa fa-eye"></i> Preview SMS
                                        </button>
                                        <button type="submit" name="send_sms" class="btn btn-success">
                                            <i class="fa fa-paper-plane"></i> Send SMS to Selected Parents
                                        </button>
                                        <span id="selectedCount" class="ml-2 text-muted">0 students selected</span>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="select-all-container">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAllStudents">
                                    <label class="form-check-label" for="selectAllStudents">Select/Deselect All Students with Contact Info</label>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="checkbox-cell"></th>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Adm No</th>
                                            <th>Stream</th>
                                            <th class="balance-col">1st Term</th>
                                            <th class="balance-col">2nd Term</th>
                                            <th class="balance-col">3rd Term</th>
                                            <th class="balance-col highlight-balance">Yearly Bal</th>
                                            <th class="contact-col">Parent Name</th>
                                            <th class="contact-col">Parent Contact</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($searchgradefullname)) {
                                            $sql = "SELECT 
                                                        s.studentname, 
                                                        s.studentadmno, 
                                                        s.feebalancereminder,
                                                        e.stream, 
                                                        p.parentname, 
                                                        p.parentcontact,
                                                        fb.firsttermbal, 
                                                        fb.secondtermbal, 
                                                        fb.thirdtermbal, 
                                                        fb.yearlybal
                                                    FROM studentdetails s
                                                    LEFT JOIN parentdetails p ON 
                                                        (s.feebalancereminder = 'Mother' AND s.motherparentno = p.parentno) OR
                                                        (s.feebalancereminder = 'Father' AND s.fatherparentno = p.parentno) OR
                                                        (s.feebalancereminder = 'Guardian' AND s.guardianparentno = p.parentno)
                                                    JOIN classentries e ON s.studentadmno = e.studentadmno
                                                    JOIN feebalances fb ON s.studentadmno = fb.studentadmno
                                                    WHERE e.gradefullname = :grade";
                                            
                                            if (!empty($searchstreamname) && !$showAllStreams) {
                                                $sql .= " AND e.stream = :stream";
                                            }
                                            
                                            $sql .= " ORDER BY fb.yearlybal DESC";
                                            
                                            $query = $dbh->prepare($sql);
                                            $query->bindParam(':grade', $searchgradefullname, PDO::PARAM_STR);
                                            
                                            if (!empty($searchstreamname) && !$showAllStreams) {
                                                $query->bindParam(':stream', $searchstreamname, PDO::PARAM_STR);
                                            }
                                            
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            
                                            if ($query->rowCount() > 0) {
                                                $cnt = 1;
                                                foreach ($results as $row): 
                                                    $hasContact = !empty($row->parentcontact);
                                                    $yearlyBalClass = $row->yearlybal > 0 ? 'highlight-balance' : '';
                                                ?>
                                                    <tr>
                                                        <td class="checkbox-cell">
                                                            <input type="checkbox" name="selected_students[]" 
                                                                   value="<?= htmlentities($row->studentadmno) ?>" 
                                                                   <?= $hasContact ? '' : 'disabled' ?>
                                                                   class="student-checkbox">
                                                        </td>
                                                        <td><?= $cnt++; ?></td>
                                                        <td><?= htmlentities($row->studentname); ?></td>
                                                        <td><?= htmlentities($row->studentadmno); ?></td>
                                                        <td><?= htmlentities($row->stream); ?></td>
                                                        <td class="balance-col"><?= number_format($row->firsttermbal); ?></td>
                                                        <td class="balance-col"><?= number_format($row->secondtermbal); ?></td>
                                                        <td class="balance-col"><?= number_format($row->thirdtermbal); ?></td>
                                                        <td class="balance-col <?= $yearlyBalClass ?>"><?= number_format($row->yearlybal); ?></td>
                                                        <td class="contact-col"><?= htmlentities($row->parentname); ?></td>
                                                        <td class="contact-col">
                                                            <?php if ($hasContact): ?>
                                                                <?= htmlentities($row->parentcontact); ?>
                                                            <?php else: ?>
                                                                <span class="text-danger">No contact</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach;
                                            } else {
                                                echo '<tr><td colspan="11" class="text-center">No fee balance records found for this selection</td></tr>';
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Hidden fields to maintain search state -->
                            <input type="hidden" name="gradefullname" value="<?= htmlentities($searchgradefullname) ?>">
                            <input type="hidden" name="streamname" value="<?= htmlentities($searchstreamname) ?>">
                            <input type="hidden" name="show_all_streams" value="<?= $showAllStreams ? '1' : '0' ?>">
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Please select a grade to view fee balances and send SMS notifications.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Preview Modal -->
    <div class="modal fade" id="smsPreviewModal" tabindex="-1" role="dialog" aria-labelledby="smsPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="smsPreviewModalLabel">SMS Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Adm No</th>
                                    <th>Parent Contact</th>
                                    <th>Message Preview</th>
                                    <th>Length</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <!-- Preview content will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>

    <!-- Enhanced SMS Functionality -->
    <script>
        const schoolName = "<?= addslashes($schoolname) ?>";

        $(document).ready(function () {
            $('#dataTables-example').dataTable({
                "columnDefs": [
                    { "orderable": false, "targets": [0] } // Disable sorting for checkbox column
                ]
            });
            
            // Character counter for SMS message
            $('#sms_message').on('input', function() {
                var length = $(this).val().length;
                $('#charCount').text(length);
                if (length > 160) {
                    $('#charCount').css('color', 'red');
                } else {
                    $('#charCount').css('color', '#6c757d');
                }
            }).trigger('input');
            
            // Select all checkboxes
            $('#selectAllCheckbox, #selectAllStudents').change(function() {
                var isChecked = $(this).prop('checked');
                $('.student-checkbox:not(:disabled)').prop('checked', isChecked);
            });
            
            // Update "Select all" checkbox when individual checkboxes change
            $('.student-checkbox').change(function() {
                var allChecked = $('.student-checkbox:not(:disabled)').length === 
                                $('.student-checkbox:not(:disabled):checked').length;
                $('#selectAllCheckbox, #selectAllStudents').prop('checked', allChecked);
            });
            
            // Preview SMS button
            $('#previewSms').click(function() {
                var template = $('#sms_message').val();
                var selectedStudents = [];
                
                $('.student-checkbox:checked').each(function() {
                    var row = $(this).closest('tr');
                selectedStudents.push({
                    name: row.find('td:eq(2)').text(),
                    admno: row.find('td:eq(3)').text(),
                    stream: row.find('td:eq(4)').text(),
                    yearlybal: row.find('td:eq(8)').text().trim(),  // Assuming yearly balance is in td index 8
                    parent: row.find('td:eq(9)').text().trim(),     // Assuming parent name is in td index 9
                    contact: row.find('td:eq(10)').text().trim()    // Assuming contact is in td index 10
                });

                });
                
                if (selectedStudents.length === 0) {
                    alert('Please select at least one student');
                    return;
                }
                
                // Build preview table
                var previewHtml = '';
                selectedStudents.forEach(function(student) {
                var personalizedMsg = template
                    .replace(/{name}/g, student.name)
                    .replace(/{admno}/g, student.admno)
                    .replace(/{grade}/g, '<?= htmlentities($searchgradefullname) ?>')
                    .replace(/{stream}/g, student.stream)
                    .replace(/{balance}/g, student.yearlybal)
                   .replace(/{parent}/g, student.parent) + "\n\n- " + schoolName;


                    
                    previewHtml += '<tr>' +
                        '<td>' + student.name + '</td>' +
                        '<td>' + student.admno + '</td>' +
                        '<td>' + student.contact + '</td>' +
                        '<td>' + personalizedMsg + '</td>' +
                        '<td>' + personalizedMsg.length + ' chars</td>' +
                        '</tr>';
                });
                
                $('#previewTableBody').html(previewHtml);
                $('#smsPreviewModal').modal('show');
            });
            
            // Form submission confirmation
            $('#smsForm').submit(function() {
                var selectedCount = $('.student-checkbox:checked').length;
                if (selectedCount === 0) {
                    alert('Please select at least one student');
                    return false;
                }
                
                return confirm('Are you sure you want to send ' + selectedCount + ' SMS notifications?');
            });
        });
    </script>
</body>
</html>