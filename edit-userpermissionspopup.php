<div role="dialog" id="edituserpermissions" class="modal fade">
    <div class="modal-dialog customstaff-modal-xl" style="max-width: 90%; overflow: hidden;">
        <div class="modal-content" style="overflow: hidden;">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h2><b> Update Permissions </b></h2>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: 80vh;">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive" style="overflow-x: auto; width: 100%;">
                            <div id="table-wrapper">
                                <div id="table-container">
                                <form method="POST" action="manage-permissions.php">
                            <table class="table table-striped table-bordered table-hover" id="dataTable2">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Permission Name</th>
                                        <th>Permission Category</th>
                                        <?php
                                        // Fetch all account types from a reliable source (e.g., account_types table)
                                        $accountTypeQuery = "SELECT DISTINCT accounttype FROM accounttypes"; 
                                        $accountTypeResult = $dbh->query($accountTypeQuery);
                                        $accountTypes = $accountTypeResult->fetchAll(PDO::FETCH_COLUMN);

                                        // Display account type headers
                                        foreach ($accountTypes as $accountType) {
                                            echo "<th>" . htmlentities($accountType) . "</th>";
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch all permissions with their categories
                                    $permissionQuery = "SELECT DISTINCT permission_name, permission_category 
                                                        FROM permissions 
                                                        ORDER BY permission_category, permission_name ASC";
                                    $permissionResult = $dbh->query($permissionQuery);
                                    $permissions = $permissionResult->fetchAll(PDO::FETCH_ASSOC);

                                    $cnt = 1;

                                    foreach ($permissions as $permission) {
                                        echo "<tr>";
                                        echo "<td>" . htmlentities($cnt) . "</td>";
                                        echo "<td>" . htmlentities($permission['permission_name']) . "</td>";
                                        echo "<td>" . htmlentities($permission['permission_category']) . "</td>";

                                        // Check each account type for the current permission
                                        foreach ($accountTypes as $accountType) {
                                            $checkQuery = "SELECT 1 
                                                        FROM role_permissions 
                                                        JOIN permissions ON role_permissions.permission_id = permissions.permission_id
                                                        WHERE permissions.permission_name = :permission 
                                                        AND role_permissions.accounttype = :accounttype";
                                            $checkStmt = $dbh->prepare($checkQuery);
                                            $checkStmt->execute([
                                                ':permission' => $permission['permission_name'], 
                                                ':accounttype' => $accountType
                                            ]);
                                            $exists = $checkStmt->fetchColumn();

                                            $checked = $exists ? "checked" : "";
                                            echo "<td>
                                                    <input type='hidden' name='permissions[{$permission['permission_name']}][{$accountType}]' value='0'> 
                                                    <input type='checkbox' name='permissions[{$permission['permission_name']}][{$accountType}]' value='1' $checked>
                                                </td>";
                                        }

                                        echo "</tr>";
                                        $cnt++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary">Update Permissions</button>
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
