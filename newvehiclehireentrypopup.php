<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title w-100 text-center">Vehicle Hire Entry</h2>
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="panel panel-primary p-3">
                        <div class="row g-3" style="padding: 10px;">
                            <!-- Column 1 -->
                            <div class="col-md-6">
                                <!-- Vehicle No Plate -->
                                <div class="form-group">
                                    <label for="vehiclenoplate">Vehicle NoPlate:</label>
                                    <select name="vehiclenoplate" class="form-control" required>
                                        <option value="">--select vehiclenoplate--</option>
                                        <?php
                                        $smt = $dbh->prepare('SELECT vehiclenoplate FROM vehiclesdetails');
                                        $smt->execute();
                                        foreach ($smt->fetchAll() as $rw): ?>
                                            <option value="<?= $rw["vehiclenoplate"] ?>"><?= $rw["vehiclenoplate"] ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <!-- Driver -->
                                <div class="form-group">
                                    <label for="driver">Driver:</label>
                                    <select name="driver" class="form-control" required>
                                        <option value="">--select driver--</option>
                                        <?php
                                        $smt = $dbh->prepare('SELECT staffname FROM staffdetails WHERE stafftitle="driver" ORDER BY id DESC');
                                        $smt->execute();
                                        foreach ($smt->fetchAll() as $rwt): ?>
                                            <option value="<?= $rwt["staffname"] ?>"><?= $rwt["staffname"] ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <!-- Trip Date -->
                                <div class="form-group">
                                    <label for="tripdate">Trip Date:</label>
                                    <input type="date" name="tripdate" id="tripdate" class="form-control" required>
                                </div>

                                <!-- Trip Days -->
                                <div class="form-group">
                                    <label for="tripdays">Trip Days:</label>
                                    <input type="text" name="tripdays" id="tripdays" class="form-control" required>
                                </div>

                                <!-- Trip Description -->
                                <div class="form-group">
                                    <label for="tripdescription">Trip Description:</label>
                                    <textarea name="tripdescription" id="tripdescription" class="form-control"></textarea>
                                </div>

                                <!-- Place From -->
                                <div class="form-group">
                                    <label for="placefrom">Place From:</label>
                                    <input type="text" name="placefrom" id="placefrom" class="form-control">
                                </div>

                                <!-- Place To -->
                                <div class="form-group">
                                    <label for="placeto">Place To:</label>
                                    <input type="text" name="placeto" id="placeto" class="form-control">
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div class="col-md-6">
                                <!-- Charge Agreed -->
                                <div class="form-group">
                                    <label for="chargeagreed">Charge Agreed:</label>
                                    <input type="text" name="chargeagreed" id="chargeagreed" class="form-control" required>
                                </div>

                                <!-- Contact Person Name -->
                                <div class="form-group">
                                    <label for="contactpersonname">Contact Person Name:</label>
                                    <input type="text" name="contactpersonname" id="contactpersonname" class="form-control">
                                </div>

                                <!-- Contact Person Phone No -->
                                <div class="form-group">
                                    <label for="contactpersonphoneno">Contact Person Phone No:</label>
                                    <input type="text" name="contactpersonphoneno" id="contactpersonphoneno" class="form-control">
                                </div>

                                <!-- Financial Year -->
                                <div class="form-group">
                                    <label for="financialyear">Financial Year:</label>
                                    <input type="text" name="financialyear" id="financialyear" class="form-control" value="<?php echo $currentyear ?>">
                                </div>

                                <!-- Driver Allowance -->
                                <div class="form-group">
                                    <label for="driverallowance">Driver Allowance:</label>
                                    <input type="text" name="driverallowance" id="driverallowance" class="form-control" value="0">
                                </div>

                                <!-- Fuel Cost -->
                                <div class="form-group">
                                    <label for="fuelcost">Fuel Cost:</label>
                                    <input type="text" name="fuelcost" id="fuelcost" class="form-control" value="0">
                                </div>

                                <!-- Other Cost -->
                                <div class="form-group">
                                    <label for="othercost">Other Cost:</label>
                                    <input type="text" name="othercost" id="othercost" class="form-control" value="0">
                                </div>

                                <!-- Reference No -->
                                <div class="form-group">
                                    <label for="referenceno">Reference (Receipt) No:</label>
                                    <input type="text" name="referenceno" id="referenceno" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mt-4">
                            <button type="submit" name="submit" class="btn btn-primary px-4">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
