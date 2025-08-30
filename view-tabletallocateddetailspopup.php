<div role="dialog" id="staffprojectsdetails<?php echo $cnt; ?>" class="modal fade">
  <div class="modal-dialog custom-modal-xl" style="max-width: 90%; width: auto;">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        <h1 class="modal-title" style="text-align: center; font-family: Arial, sans-serif; font-size: 24px;"><b>Gadgets Allocated</b></h1>
      </div>
      <div class="modal-body" style="font-family: Arial, sans-serif;">
        <!-- actual form -->
        <h3><b>Name: </b><?php echo $row->staffname; ?></h3>
        <div class="panel panel-primary">
          <div class="row">
            <div class="col-lg-12">
              <!-- Advanced Tables -->
              <div class="panel panel-default">
                <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                  <table class="table table-striped table-bordered table-hover" id="dataTables-example1">
                    <thead style="background-color: #f2f2f2; text-align: center;">
                      <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>SerialNo</th>
                        <th>Type</th>
                        <th>Allocated</th>
                        <th>Description</th>
                        <th>Charger</th>
                        <th>Badger</th>
                        <th>AllocatedBy</th>
                        <th>Returned</th>
                        <th>Description</th>
                        <th>Charger</th>
                        <th>Badger</th>
                        <th>ReceivedBy</th>                     
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      if (!empty($row->idno)) {
                        $sql = "SELECT * FROM tabletsallocations WHERE idno = :idno AND projectfullname = :searchprojectfullname ORDER BY id DESC";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':idno', $row->idno, PDO::PARAM_INT);
                        $query->bindParam(':searchprojectfullname', $searchprojectfullname, PDO::PARAM_STR);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $recordCount = $query->rowCount();
                        $cnnt = 1;
                        if ($query->rowCount() > 0) {
                          foreach ($results as $rww) {
                      ?>
                            <tr>
                              <td><?php echo htmlentities($cnnt); ?></td>  
                              <td><?php echo htmlentities($rww->status); ?></td>                         
                              <td><?php echo htmlentities($rww->tabletserialno); ?></td>
                              <td>
                                                                <?php
                                if (!empty($row->tabletserialno)) {
                                    // If tabletserialno is not blank, fetch the type
                                    $sqll = "SELECT * FROM tabletsdetails WHERE tabletserialno='$rww->tabletserialno'";
                                    $queryy = $dbh->prepare($sqll);
                                    $queryy->execute();
                                    $resultss = $queryy->fetchAll(PDO::FETCH_OBJ);
                                    if ($queryy->rowCount() > 0) {
                                        foreach ($resultss as $roww) {
                                            echo htmlentities($roww->type); // Display the 'type' field
                                        }
                                    } else {
                                        echo ''; // Display nothing if no matching record is found
                                    }
                                } else {
                                    echo ''; // Display nothing if tabletserialno is blank
                                }
                                ?>
                                                            </td>
                              <td><?php echo htmlentities($rww->allocatedate); ?></td>
                              <td><?php echo htmlentities($rww->allocatedescription); ?></td>
                              <td><?php echo htmlentities($rww->allocatecharger); ?></td>
                              <td><?php echo htmlentities($rww->allocatebadge); ?></td>
                              <td><?php echo htmlentities($rww->allocateusername); ?></td>
                              <td><?php echo htmlentities($rww->returndate); ?></td>
                              <td><?php echo htmlentities($rww->returndescription); ?></td>                               
                              <td><?php echo htmlentities($rww->returncharger); ?></td>
                              <td><?php echo htmlentities($rww->returnbadge); ?></td>
                              <td><?php echo htmlentities($rww->returnusername); ?></td>
                            </tr>
                      <?php
                            $cnnt++;
                          }
                        }
                      } else {
                        // Handle case where idno is empty
                      }
                      ?>                       
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
