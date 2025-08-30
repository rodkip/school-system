<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"> 
    <div class="modal-dialog"> 
        <div class="modal-content">

            <div class="modal-header"> 
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h2 class="modal-title text-center">New Fee Structure</h2> 
            </div> 

            <div class="modal-body">
                <div class="panel panel-primary">                
                    <div class="row">
                        <div class="col-lg-12">            
                            <div class="panel panel-default">   

                                <form method="post" enctype="multipart/form-data" action="manage-feestructure.php"> 
                                    <div class="form-group">                                
                                        <table class="table">
                                            
                                            <!-- Grade Fullname -->
                                            <tr>
                                                <td><label for="gradefullname">Grade Fullname:</label></td>
                                                <td>
                                                    <?php
                                                        $smt = $dbh->prepare('SELECT gradefullname FROM classdetails ORDER BY gradefullname DESC');
                                                        $smt->execute();
                                                        $data = $smt->fetchAll();
                                                    ?>
                                                    <select name="gradefullname" class="form-control" required>
                                                        <option value="">-- Select gradefullname --</option>
                                                        <?php foreach ($data as $row): ?>
                                                            <option value="<?= $row["gradefullname"] ?>"><?= $row["gradefullname"] ?></option> 
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- Entry Term -->
                                            <tr>
                                                <td><label for="entryterm">EntryTerm:</label></td>
                                                <td>
                                                    <select name="entryterm" class="form-control" required>
                                                        <option value="">-- Select EntryTerm --</option>
                                                        <option value="FirstTerm">1st Term</option>
                                                        <option value="SecondTerm">2nd Term</option>
                                                        <option value="ThirdTerm">3rd Term</option>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- Boarding -->
                                            <tr>
                                                <td><label for="boarding">Day or Border:</label></td>
                                                <td>
                                                    <select name="boarding" class="form-control" required>
                                                        <option value="">-- Select --</option>
                                                        <option value="Day">Day</option>
                                                        <option value="Border">Border</option>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- Fees -->
                                            <tr>
                                                <td><label for="firsttermfee">First TermFee:</label></td>
                                                <td><input type="number" class="form-control" name="firsttermfee" id="firsttermfee" placeholder="First Term Fee" required min="0"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="secondtermfee">Second TermFee:</label></td>
                                                <td><input type="number" class="form-control" name="secondtermfee" id="secondtermfee" placeholder="Second Term Fee" required min="0"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="thirdtermfee">Third TermFee:</label></td>
                                                <td><input type="number" class="form-control" name="thirdtermfee" id="thirdtermfee" placeholder="Third Term Fee" required min="0"></td>
                                            </tr>
                                            <tr>
                                                <td><label for="othersfee">Others Fee:</label></td>
                                                <td><input type="number" class="form-control" name="othersfee" id="othersfee" placeholder="Other Fees" required min="0"></td>
                                            </tr>
                                            
                                        </table>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center">
                                        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>

                            </div> <!-- panel-default -->
                        </div> <!-- col-lg-12 -->
                    </div> <!-- row -->
                </div> <!-- panel-primary -->
            </div> <!-- modal-body -->

        </div> <!-- modal-content -->
    </div> <!-- modal-dialog -->
</div> <!-- modal -->
