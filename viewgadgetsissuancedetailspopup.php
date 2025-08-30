<div role="dialog" id="viewgadgetsissuancedetails<?php echo $cnt; ?>" class="modal fade">
  <div class="modal-dialog custom-modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        <h1 class="page-header"><b>Gadget Issuance History</b></h1>      
      <h2><b>SerialNo: </b><?php echo $row->tabletserialno; ?>&nbsp;&nbsp;<b>Model</b>: </b><?php echo $row->model; ?>&nbsp;&nbsp;<b>Brand:</b> </b><?php echo $row->brand; ?></b>
</h2>
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
                          <th>#</th>
                          <th>Staff IdNo</th>
                          <th>Staff Name</th>
                          <th>Project</th>   
                          <th>Date-OUT</th> 
                          <th>Description-OUT</th> 
                          <th>Date-IN</th> 
                          <th>Description-IN</th> 

                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        if (!empty($row->tabletserialno)) {
                          $sql = "SELECT tabletsallocations.tabletserialno, tabletsallocations.idno, tabletsallocations.projectfullname , tabletsallocations.allocatedate, tabletsallocations.allocatedescription, tabletsallocations.returndate, tabletsallocations.returndescription, staffdetails.staffname
                          FROM tabletsallocations JOIN staffdetails ON tabletsallocations.idno = staffdetails.idno
                          WHERE tabletsallocations.tabletserialno = '$row->tabletserialno' 
                          ORDER BY tabletsallocations.id DESC";
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
                                <td><?php echo htmlentities($rww->idno); ?></td>
                                <td><?php echo htmlentities($rww->staffname); ?></td>
                                <td><?php echo htmlentities($rww->projectfullname); ?></td>
                                <td><?php echo htmlentities($rww->allocatedate); ?></td>
                                <td><?php echo htmlentities($rww->allocatedescription); ?></td>
                                <td><?php echo htmlentities($rww->returndate); ?></td>
                                <td><?php echo htmlentities($rww->returndescription); ?></td>
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
