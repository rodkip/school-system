<div role="dialog" id="viewreplacedstaffdetails<?php echo $cnt; ?>" class="modal fade">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
            <h1 class="modal-title" style="text-align: center;"><b>Replaced Staff</b></h1>
         </div>
         <div class="modal-body">
            <!-- actual form --> 
            <div class="panel panel-primary">
               <div class="row">
                  <div class="col-lg-12">
                     <!-- Advanced Tables -->
                     <div class="panel panel-default">
                        <div class="popup">
                           <span style="font-size:larger; font-family:'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif" >
                              <table class="table table-striped table-bordered table-hover">
                                 <tr>
                                    <td>IdNo:</td>
                                    <td><?php echo htmlentities( $row->replacementidno); ?></td>
                                 </tr>
                                 <tr>
                                    <?php 
                                       $searchreplacementidno = $row->replacementidno;
                                       $sqll = "SELECT staffname FROM staffdetails WHERE idno = '$searchreplacementidno'";
                                       $queryy = $dbh->prepare($sqll);
                                       $queryy->execute();
                                       $resultss = $queryy->fetchAll(PDO::FETCH_OBJ);
                                       
                                       if ($queryy->rowCount() > 0) {
                                           foreach ($resultss as $rwww) { 
                                       ?>
                                 <tr>
                                    <td>StaffName:</td>
                                    <td><?php echo htmlentities($rwww->staffname); ?></td>
                                 </tr>
                                 <?php
                                    }
                                    }
                                    ?>
                                 </tr>
                                 <tr>
                                    <td>Replacement Reason:</td>
                                    <td><?php echo htmlentities( $row->replacementreason); ?></td>
                                 </tr>
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