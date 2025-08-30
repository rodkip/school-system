<div role="dialog" id="myModal<?php echo $cnt; ?>" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: left;">Student Counts per Parent</h2> 
               <h4> <b>Parent name: </b><?php echo $parentname; ?></h4>
            </div> 
            <div class="modal-body">
            <!-- actual form --> 
               
                    <div class="panel panel-primary">                
                        <div class="row">
                            <div class="col-lg-12">            
                        <!-- Advanced Tables -->
                                <div class="panel panel-default">                     
                               
   <div class="popup">                   
                        <form method="post" enctype="multipart/form-data"> 
                                    
                           <div class="form-group"> 
                                      <br>           
   <table class="table table-striped table-bordered table-hover">
       <tr> 
           <th>#</th>
           <th>AdmNo</th>
           <th>Name</th>
           <th>Gender</th>
           <th>Age</th>
           <th>Last Grade</th>
       </tr>
               <?php                                            
                     $sqll="SELECT studentdetails.parentname,studentdetails.studentname,studentdetails.studentadmno,studentdetails.gender,studentdetails.dateofbirth,max(classentries.gradefullname) as lastgrade from studentdetails INNER JOIN classentries ON studentdetails.studentadmno=classentries.studentadmno GROUP BY studentdetails.parentname,studentdetails.studentname,studentdetails.studentadmno,studentdetails.gender,studentdetails.dateofbirth HAVING studentdetails.parentname='$parentname' ORDER BY studentdetails.studentadmno asc";
                        $qry = $dbh -> prepare($sqll);
                               $qry->execute();
                                  $rslts=$qry->fetchAll(PDO::FETCH_OBJ);  
                                  $ct=1;           
                                  foreach($rslts as $rw)
                                  {   ?>   
                              <tr> 
                            <td><?php echo htmlentities($ct); ?></td>                              
                            <td><a href="manage-feepayments.php?viewstudentadmno=<?php echo htmlentities($rw->studentadmno);?>"><?php echo htmlentities($rw->studentadmno); ?></a></td>
                            <td><?php echo htmlentities($rw->studentname); ?></td>
                            <td><?php echo htmlentities($rw->gender); ?></td> 
                            <td><?php $datestring=($rw->dateofbirth);
                    $age=round((time()-strtotime($datestring))/(3600*24*365.25));
                    echo $age;?></td>
                            <td><?php echo htmlentities($rw->lastgrade); ?></td>

                                                  <?php $ct=$ct+1; }?> 

                              </tr>
                            </tbody>
                          </table>  
                          </div>       
                        </form>
               </div>
                                        </div> 
                                    </div> 
                                </div> 
                            </div>       
                        </div> 
                    </div>                
            </div> 
</div>
            