<div role="dialog" id="myModal<?= $cnt; ?>" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header"> 
                <h4 class="modal-title">Per Bank Fee Payments</h4> 
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div> 

            <!-- Modal Body -->
            <div class="modal-body">
                <h5>Financial Year: <strong style="color: red;"><?= htmlentities($academicyear); ?></strong></h5>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Bank</th>
                                <th>Fee Payments Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sqll = "SELECT bank, academicyear, SUM(cash) AS sumpaid 
                                     FROM feepayments 
                                     WHERE academicyear = :year 
                                     GROUP BY bank, academicyear 
                                     ORDER BY bank ASC";
                            $qury = $dbh->prepare($sqll);
                            $qury->bindParam(':year', $academicyear, PDO::PARAM_STR);
                            $qury->execute();
                            $resuts = $qury->fetchAll(PDO::FETCH_OBJ);
                            $cnnt = 1;

                            if ($qury->rowCount() > 0) {
                                foreach ($resuts as $rw) {
                                    echo "<tr>";
                                    echo "<td>" . htmlentities($cnnt++) . "</td>";
                                    echo "<td>" . htmlentities($rw->bank) . "</td>";
                                    echo "<td>" . number_format($rw->sumpaid) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No data found for this academic year.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <p class="mt-3"><strong>Total Received:</strong> <?= number_format($sumreceived); ?></p>
                </div>
            </div>

        </div> 
    </div> 
</div>
