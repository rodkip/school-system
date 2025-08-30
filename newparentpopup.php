<?php
// Include DB connection
// Default parentno
$nextparentno = "Parent0001";

try {
    // Prepare and execute query to get the latest parentno
    $sql = "SELECT parentno FROM parentdetails ORDER BY CAST(SUBSTRING(parentno, 7) AS UNSIGNED) DESC LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && isset($row['parentno'])) {
        // Extract numeric part, increment it, and pad with zeros
        $lastNumeric = (int)substr($row['parentno'], 6);
        $nextNumeric = $lastNumeric + 1;
        $nextparentno = 'Parent' . str_pad($nextNumeric, 4, '0', STR_PAD_LEFT);
    }
} catch (PDOException $e) {
    echo "Error fetching parent ID: " . $e->getMessage();
}
?>

<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade"> 
    <div class="modal-dialog"> 
        <div class="modal-content">
            <div class="modal-header"> 
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2 class="modal-title" style="text-align: center;">Add New Parent</h2> 
            </div> 
            <div class="modal-body">
                <div class="panel panel-primary">                
                    <div class="row">
                        <div class="col-lg-12">            
                            <div class="panel panel-default">                     
                                <div class="popup">                   
                                    <form method="post" enctype="multipart/form-data" id="parentForm"> 
                                        <div class="form-group"> 
                                            <br>
                                            <table class="table">
                                                <tr>
                                                    <td>
                                                        <label for="parentno">ParentNo:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="parentno" id="parentno" required="required" placeholder="parentno" value="<?php echo htmlspecialchars($nextparentno); ?>" class="form-control" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="idno">IdNo:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="idno" id="idno" placeholder="Parent IdNo" value="" class="form-control">                              
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="parentname">Name:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="parentname" id="parentname" required="required" placeholder="Parentname" value="" class="form-control">
                                                        <div id="parentnameStatus" class="mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="parentcontact">Contact:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="parentcontact" id="parentcontact" required="required" placeholder="parentcontact" value="">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="homearea">HomeArea:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="homearea" id="homearea" placeholder="homearea" value="">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <label for="proffesion">Proffesion:</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="proffesion" id="proffesion" placeholder="proffesion" value="">
                                                    </td>                           
                                                </tr>            
                                            </table>               
                                        </div>
                                        <div>
                                            <p style="padding-left: 450px">
                                                <button type="submit" name="submit" class="btn btn-primary" id="submitBtn">Submit</button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const parentnameInput = document.getElementById('parentname');
    const parentnameStatus = document.getElementById('parentnameStatus');
    const submitBtn = document.getElementById('submitBtn');
    let nameCheckTimeout;

    // Function to check parent name availability
    function checkParentName(name) {
        // Clear previous timeout if it exists
        clearTimeout(nameCheckTimeout);
        
        // Only check if name has at least 3 characters
        if (name.length < 3) {
            parentnameStatus.innerHTML = '';
            return;
        }

        // Show checking status
        parentnameStatus.innerHTML = '<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Checking name...</span>';
        
        // Create a FormData object
        const formData = new FormData();
        formData.append('action', 'check_parentname');
        formData.append('parentname', name);

        // Send AJAX request
        fetch('check_parent.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.exists) {
                parentnameStatus.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Parent name already exists!</span>';
                submitBtn.disabled = true;
            } else {
                parentnameStatus.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> Name available</span>';
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            parentnameStatus.innerHTML = '<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Error checking name</span>';
        });
    }

    // Event listener for input changes with debounce
    parentnameInput.addEventListener('input', function() {
        clearTimeout(nameCheckTimeout);
        nameCheckTimeout = setTimeout(() => {
            checkParentName(this.value.trim());
        }, 500); // 500ms delay after typing stops
    });

    // Also check when leaving the field
    parentnameInput.addEventListener('blur', function() {
        checkParentName(this.value.trim());
    });
});
</script>