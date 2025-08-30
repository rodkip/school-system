<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="studentsearch" class="modal fade"> 
    <div class="modal-dialog modal-lg" style="max-width: 110%;"> <!-- Adjust width -->
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h3 class="modal-title text-center">
                    <i class="fa fa-search"></i>  by Name, AdmNo or parents names<i class="fa fa-id-badge ml-2"></i>
                </h3>
            </div> 
            <div class="modal-body">
                <div class="panel panel-primary">                
                  
                        <div class="panel panel-default">
                            <div class="panel-body">
                            <div class="table-responsive" style="overflow-x: auto; width: 100%">
                                    <table class="table table-striped table-bordered table-hover" id="dataTables-example4">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>AdmNo</th>
                                                <th>Mother Name</th>
                                                <th>Father Name</th>
                                                <th>Guardian Name</th>
                                                <th>LatestGrade</th> 
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch student details along with the latest grade
                                            $sql = "SELECT s.studentname, s.studentadmno, s.motherparentno, s.fatherparentno, s.guardianparentno, s.id,
                                                    (SELECT gradefullname FROM classentries 
                                                     WHERE studentadmno = s.studentadmno 
                                                     ORDER BY gradefullname DESC LIMIT 1) AS latestgrade
                                                    FROM studentdetails s ORDER BY s.studentadmno asc";
                                                
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $row) {
                                                    // Fetch parent names from parentdetails table
                                                    $motherName = $fatherName = $guardianName = "";

                                                    // Fetch Mother's Name
                                                    if (!empty($row->motherparentno)) {
                                                        $sqlMother = "SELECT parentname FROM parentdetails WHERE parentno = :motherparentno";
                                                        $queryMother = $dbh->prepare($sqlMother);
                                                        $queryMother->bindParam(':motherparentno', $row->motherparentno, PDO::PARAM_STR);
                                                        $queryMother->execute();
                                                        $motherResult = $queryMother->fetch(PDO::FETCH_OBJ);
                                                        if ($motherResult) {
                                                            $motherName = htmlentities($motherResult->parentname);
                                                        }
                                                    }

                                                    // Fetch Father's Name
                                                    if (!empty($row->fatherparentno)) {
                                                        $sqlFather = "SELECT parentname FROM parentdetails WHERE parentno = :fatherparentno";
                                                        $queryFather = $dbh->prepare($sqlFather);
                                                        $queryFather->bindParam(':fatherparentno', $row->fatherparentno, PDO::PARAM_STR);
                                                        $queryFather->execute();
                                                        $fatherResult = $queryFather->fetch(PDO::FETCH_OBJ);
                                                        if ($fatherResult) {
                                                            $fatherName = htmlentities($fatherResult->parentname);
                                                        }
                                                    }

                                                    // Fetch Guardian's Name
                                                    if (!empty($row->guardianparentno)) {
                                                        $sqlGuardian = "SELECT parentname FROM parentdetails WHERE parentno = :guardianparentno";
                                                        $queryGuardian = $dbh->prepare($sqlGuardian);
                                                        $queryGuardian->bindParam(':guardianparentno', $row->guardianparentno, PDO::PARAM_STR);
                                                        $queryGuardian->execute();
                                                        $guardianResult = $queryGuardian->fetch(PDO::FETCH_OBJ);
                                                        if ($guardianResult) {
                                                            $guardianName = htmlentities($guardianResult->parentname);
                                                        }
                                                    }

                                                    // Check if the latest grade is empty, if so, display "No class assigned"
                                                    $latestGrade = !empty($row->latestgrade) ? htmlentities($row->latestgrade) : 'No class assigned';
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlentities($row->studentname); ?></td>
                                                        <td data-search="<?php echo htmlentities($row->studentadmno); ?>">
                                                            <a href="manage-feepayments.php?viewstudentadmno=<?php echo htmlentities($row->studentadmno); ?>">
                                                                <?php echo htmlentities($row->studentadmno); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo $motherName; ?></td>
                                                        <td><?php echo $fatherName; ?></td>
                                                        <td><?php echo $guardianName; ?></td>
                                                        <td><?php echo $latestGrade; ?></td> <!-- Display latest grade or "No class assigned" -->
                                                    </tr>
                                                    <?php
                                                }
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

<script>
$(document).ready(function() {
    $('#dataTables-example4').DataTable({
        responsive: true,
        "order": [[1, 'asc']], // This will sort by Adm No (index 1) in ascending order by default
        "columnDefs": [
            {
                "targets": [1], // Target the Adm No column (0-based index)
                "render": function(data, type, row) {
                    if (type === 'display') {
                        return data;
                    }
                    return $(data).text() || data;
                }
            }
        ]
    });
});
</script>
