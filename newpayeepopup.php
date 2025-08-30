<?php
// Include DB connection
// Default payeeid
$nextPayeeIdNo = "Payee0001";

try {
    // Prepare and execute query to get the latest payeeid
    $sql = "SELECT payeeid FROM payeedetails ORDER BY CAST(SUBSTRING(payeeid, 7) AS UNSIGNED) DESC LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && isset($row['payeeid'])) {
        // Extract numeric part, increment it, and pad with zeros
        $lastNumeric = (int)substr($row['payeeid'], 6);
        $nextNumeric = $lastNumeric + 1;
        $nextPayeeIdNo = 'Payee' . str_pad($nextNumeric, 4, '0', STR_PAD_LEFT);
    }
} catch (PDOException $e) {
    echo "Error fetching payee ID: " . $e->getMessage();
}
?>


<!-- Modal HTML -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Add New Payee</h2> 
            </div> 
            <div class="modal-body">
                <div class="panel panel-primary">                
                    <div class="row">
                        <div class="col-lg-12">            
                            <div class="panel panel-default">                     
                                <div class="popup">                   
                                    <form method="post" enctype="multipart/form-data"> 
                                        <div class="form-group"> 
                                            <br>
                                            <table class="table">
                                                <tr>
                                                    <td><label for="payeeid">PayeeNo:</label></td>
                                                    <td>
                                                    <input type="text" name="payeeid" id="payeeid" required="required"  placeholder="payeeid" value="<?php echo htmlspecialchars($nextPayeeIdNo); ?>" class="form-control" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="payeename">Payee Name:</label></td>
                                                    <td>
                                                        <input type="text" name="payeename" id="payeename" 
                                                        placeholder="payeename" value="" class="form-control">                              
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="gender">Gender:</label></td>
                                                    <td>
                                                        <select name="gender" class="form-control"> 
                                                            <option>--select gender--</option>
                                                            <option value="Male">Male</option> 
                                                            <option value="Female">Female</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="postaladdress">Postal Address:</label></td>
                                                    <td>
                                                        <input type="text" class="form-control" name="postaladdress" id="postaladdress"
                                                        placeholder="postaladdress" value="" >
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="emailaddress">Email Address:</label></td>
                                                    <td>
                                                        <input type="text" name="emailaddress" id="emailaddress" 
                                                        placeholder="Email Address" value="" class="form-control">                              
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="mobileno">Mobile No:</label></td>
                                                    <td>
                                                        <input type="text" name="mobileno" id="mobileno" 
                                                        placeholder="mobileno" value="" class="form-control">                              
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label for="proffession">Profession/Services:</label></td>
                                                    <td>
                                                        <input type="text" class="form-control" name="proffession" id="proffession"
                                                        placeholder="profession" value="">
                                                    </td>                           
                                                </tr>            
                                            </table>               
                                        </div>
                                        <div>
                                            <p style="padding-left: 450px">
                                                <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                            </p>
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
