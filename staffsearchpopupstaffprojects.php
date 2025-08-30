<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal1" class="modal fade"> 
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Search By StaffName Or IdNo</h2> 
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                    <div class="row">
               <div class="col-lg-12">
                  <!-- Advanced Tables -->
                  
                  <div class="panel panel-default">
                     <div class="panel-body"> 
                         <span style="color: red;">Search by either name, IdNo or Contact to view</span> 
                        <div class="table-responsive">
                           <table class="table table-striped table-bordered table-hover" id="dataTables-example1" style="font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif">
                           <thead>
                              
                                 <tr>
                                    <th>StaffName</th>
                                    <th>IdNo</th>                                  
                                    <th>Contact</th>
    
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php
                                    $sql="SELECT * FROM staffdetails ORDER BY id DESC";
                                    $query = $dbh -> prepare($sql);
                                    $query->execute();
                                    $results=$query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt=1;
                                    if($query->rowCount() > 0)
                                    {
                                    foreach($results as $row)
                                    {               
                                    ?>
                                 <tr>
                                    <td><?php echo htmlentities($row->staffname);?></td>
                                    <td>
                                        <a href="manage-staffprojects.php?viewidno=<?php echo htmlentities($row->idno);?>"><?php echo htmlentities($row->idno);?></a>
                                </td>   
                                   
                                    <td><?php echo htmlentities($row->contact);?></td>                                   
                                 </tr>
                                 <?php $cnt=$cnt+1;}}?> 
                                 </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
                  <!--End Advanced Tables -->
               </div>
            </div> 
                            </div>       
                        </div> 
                    </div>                
            </div> 
</div>
