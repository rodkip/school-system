<div role="dialog" id="staffprojectsdetails<?php echo $cnt; ?>" class="modal fade">
  <div class="modal-dialog custom-modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        <h1 class="modal-title" style="text-align: center;"><b>Projects Involved Details</b></h1>
      </div>
      <div class="modal-body">
        <!-- actual form -->
        <h3><b>Name: </b><?php echo $row->staffname; ?>,&nbsp;&nbsp;<b> IdNo: </b><?php
if ($accounttype == "Supervisor" or $accounttype == "Admin") {
    // Show the menu item for "QC Rating"
    echo htmlentities($row->idno);
} else {
    // Display first two characters and replace the rest with asterisks
    $idnoToShow = substr($row->idno, 0, 2) . str_repeat('*', strlen($row->idno) - 2);
    echo htmlentities($idnoToShow);
}
?>
</h3>
<p style="color: red; font-weight: bold; font-size: 18px;">
  Note: <span style="color: blue;">Move the pointer on top of a rate to get explanation details(Popup)</span>
</p>
        <div class="panel panel-primary">
          <div class="row">
            <div class="col-lg-12">
              <!-- Advanced Tables -->
              <div class="panel panel-default">
              <div class="table-responsive" style="overflow-x: auto; width: 100%">
                <div class="popup">
           
                  <span style="font-size:medium;font-family: Arial, sans-serif;">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                      <thead>
                        <tr>
                          <th></th>
                          <th>ProjectFullName</th>
                          <th>Title</th>
                          <th>Completion</th>
                          <th>OPs-Ratings</th>
                          <th>QC-Ratings</th>
                          <th>Finance-Ratings</th>
                          <th>Average-Ratings</th>
                          <th>DeletionRate</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if (!empty($row->idno)) {
                          $sql = "SELECT projectlistentries.idno,projectlistentries.projectfullname,projectlistentries.projectcompletion,projectlistentries.projectdesignation,projectlistentries.projecttier,projectlistentries.region,projectlistentries.comments,projectlistentries.commentsafterfield,projectlistentries.qcratings,projectlistentries.qcratingscomments,projectlistentries.financeratings,projectlistentries.financeratingscomments,projectlistentries.overralratings,projectlistentries.totalinterviewsdone,projectlistentries.totalinterviewsdeleted,projectlistentries.ratings,projectdetails.projectyear,projectdetails.projectstartdate,projectdetails.projectenddate FROM projectlistentries JOIN projectdetails ON projectlistentries.projectfullname = projectdetails.projectfullname where projectlistentries.idno=$row->idno ORDER by projectlistentries.id DESC;";
                          $query = $dbh->prepare($sql);
                          $query->execute();
                          $results = $query->fetchAll(PDO::FETCH_OBJ);
                          $recordCount = $query->rowCount();
                          $cnnt = 1;
                          if ($query->rowCount() > 0) {
                            foreach ($results as $rww) {
                        ?>
                             <tr>
                                <td><?php echo htmlentities($cnnt); ?></td>
                                <td><?php echo htmlentities($rww->projectfullname); ?></td>
                                <td><?php echo htmlentities($rww->projectdesignation); ?></td>
                                <td><?php echo htmlentities($rww->projectcompletion); ?></td>
                                <td>
                                    <div class="custom-tooltip" data-title="<?php echo htmlentities($rww->commentsafterfield); ?>">
                                        <?php echo htmlentities($rww->ratings); ?>                             
                                    </div>
                                </td>


                                <td>
                                <div class="custom-tooltip" data-title="<?php echo htmlentities($rww->financeratingscomments); ?>">
                                        <?php echo htmlentities($rww->qcratings); ?>                             
                                </div>
                            </td>
                                <td>
                                    <?php include('ratingscommentspopupfinance.php'); ?><a href="#ratingscommentsfinance<?php echo $cnt; ?>" data-toggle="modal"><?php echo htmlentities($rww->financeratings); ?></a>
                                </td>
                                <td>
                                    <?php echo htmlentities($rww->overralratings); ?>
                                </td>
                                <td>
                                    <?php
                                    $totalInterviewsDone = $rww->totalinterviewsdone;
                                    $totalInterviewsDeleted = $rww->totalinterviewsdeleted;

                                    // Check if totalInterviewsDeleted is zero to avoid division by zero
                                    if ($totalInterviewsDeleted > 0) {
                                        $percentage = ($totalInterviewsDeleted / $totalInterviewsDone) * 100;
                                        echo htmlentities(number_format($percentage, 2)) . '%'; // Display as a percentage with two decimal places
                                    } else {
                                        $percentage = 0;
                                        echo htmlentities(number_format($percentage, 2)) . '%'; // Display as a percentage with two decimal places
                                    }
                                    $sumPercentages += $percentage;
                                    $rowCount++;
                                    ?>
                                </td>
                            </tr>
                              <!-- Calculate and display the average percentage after the loop -->

                        <?php
                              $cnnt = $cnnt + 1;
                            }
                          }
                        } else {
                          // handle case where idno is empty
                        }
                        ?>
                        <h4><span style="color: blue; font-family: Arial, sans-serif;">Average Deletion Rate:</span>
                         <?php
        if ($rowCount > 0) {
            $averagePercentage = $sumPercentages / $rowCount;
            echo htmlentities(number_format($averagePercentage, 2)) . '%'; // Display average percentage with two decimal places
        } else {
            echo "N/A"; // Handle the case where there are no valid rows
        }
        ?></h4>
                      </tbody>
                    </table>
                  </span>
                </div>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
