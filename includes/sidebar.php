<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<?php
// Permission check function (unchanged)
function has_permission($accounttype, $permission_name) {
    global $dbh;
    $query = "SELECT COUNT(*) AS permission_count 
              FROM role_permissions rp 
              JOIN permissions p ON rp.permission_id = p.permission_id 
              WHERE rp.accounttype = :accounttype 
              AND p.permission_name = :permission_name";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':accounttype', $accounttype, PDO::PARAM_STR);
    $stmt->bindParam(':permission_name', $permission_name, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['permission_count'] > 0;
}
?>

<div class="sidebar">
    <ul class="nav" id="side-menu">
        <!-- Dashboard -->
        <li>
            <a href="dashboard.php">
                <i class="fas fa-home fa-fw"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <!-- Admin Settings -->
        <li class='has-sub'>
            <a href='#'>
                <i class='fas fa-cogs fa-fw'></i>
                <span class='menu-text'>System Settings</span>
                <i class='fas fa-chevron-down toggle-icon'></i>
            </a>
            <ul class='sub-menu'>
                <?php if (has_permission($accounttype, 'manage_schooldetails')): ?>
                <li><a href='manage-schooldetails.php'><i class='fas fa-school fa-fw'></i>School Information</a></li>
                <?php endif; ?>
                <?php if (has_permission($accounttype, 'system_notificationssetting')): ?>
                <li><a href='manage-notifications.php'><i class='fas fa-bell fa-fw'></i>Notifications</a></li>
                <?php endif; ?>
                <li><a href='manage-feestructure.php'><i class='fas fa-file-invoice-dollar fa-fw'></i>Fees Structure</a></li>
                <li><a href='manage-paymentaccounts.php'><i class='fas fa-university fa-fw'></i>Bank Accounts</a></li>                
                <li><a href="manage-otherpayitems.php"><i class='fas fa-list fa-fw'></i>Other Payment Items</a></li>                
                <li><a href="manage-voteheads.php"><i class='fas fa-tags fa-fw'></i> Vote Heads</a></li>
                <li><a href="manage-treatmentrates.php"><i class='fas fa-percentage fa-fw'></i> Fee Treatment Rates</a></li>
                <li class="submenu-header">User Management</li>
                <?php if (has_permission($accounttype, 'manage_users')): ?>
                    <li><a href='manage-userdetails.php'><i class='fas fa-user-cog fa-fw'></i>User Accounts</a></li>
                <?php endif; ?>
                
                <li class="submenu-header">System Tools</li>
                <?php if (has_permission($accounttype, 'system_logs')): ?>
                <li><a href='view-activitylogs.php'><i class='fas fa-history fa-fw' style='color: red;'></i>Activity Logs</a></li>
                <li><a href='view-loginlogs.php'><i class='fas fa-sign-in-alt fa-fw'></i>Login Logs</a></li>                
                <?php endif; ?>
            </ul>
        </li>

        <!-- People Management Section -->
        <li class="menu-section">
            <span class="menu-text">People Management</span>
        </li>
        
        <!-- Registration -->
        <li class="has-sub">
            <a href="#">
                <i class="fas fa-users fa-fw"></i>
                <span class="menu-text">Registration</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="sub-menu">  
                <li><a href='manage-studentdetails.php'><i class='fas fa-user-graduate fa-fw'></i>Learners</a></li>
                <li><a href='manage-parentdetails.php'><i class='fas fa-user-friends fa-fw'></i>Parents</a></li>
                <li><a href='manage-staffdetails.php'><i class='fas fa-user-tie fa-fw'></i>Staff</a></li>
                <li><a href='manage-payeedetails.php'><i class='fas fa-money-check-alt fa-fw'></i>Payees</a></li>          
            </ul>
        </li>

        <!-- Academic -->
        <li class="has-sub">
            <a href="#">
                <i class="fas fa-chalkboard-teacher fa-fw"></i>
                <span class="menu-text">Academic</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="sub-menu">
                <li><a href="manage-classdetails.php"><i class='fas fa-list-alt fa-fw'></i>Grades List</a></li>
                <li><a href="manage-classentries.php"><i class='fas fa-user-plus fa-fw'></i>Grade Assignments</a></li>
                <li><a href="manage-streams.php"><i class='fas fa-stream fa-fw'></i>Streams</a></li>
                <li><a href='manage-dormitoriesdetails.php'><i class='fas fa-bed fa-fw'></i>Dormitories</a></li>
            </ul>
        </li>

        <!-- Financial Management Section -->
        <li class="menu-section">
            <span class="menu-text">Financial Management</span>
        </li>
        
        <!-- Accounts --> 
        <li class="has-sub">
            <a href="#">
                <i class="fas fa-money-bill-wave fa-fw"></i>
                <span class="menu-text">Accounts</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="sub-menu">
                <li><a href="manage-feepayments.php"><i class='fas fa-file-invoice fa-fw'></i>Fee Payments</a></li>
                <li><a href="feepaymentsanalysis.php"><i class='fas fa-chart-pie fa-fw'></i>Payment Analysis</a></li>
                <li><a href="viewall-feepaymentsvoteheadsanalysis.php"><i class='fas fa-chart-area fa-fw'></i>Votehead Insights</a></li>
                <li><a href="manage-cleanup.php"><i class='fas fa-sync-alt fa-fw'></i> Clean-up</a></li>
                <li><a href="backupmaster.php"><i class='fas fa-paper-plane fa-fw'></i>Data-Backup</a></li>
            </ul>
        </li>

        <!-- Expenses -->
        <li class="has-sub">
            <a href="#">
                <i class="fas fa-coins fa-fw"></i>
                <span class="menu-text">Expenses</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="sub-menu">              
                <li><a href="manage-expenditures.php"><i class='fas fa-file-invoice-dollar fa-fw'></i>Record Expenses</a></li>
                <li><a href="viewall-expenditurespayments.php"><i class='fas fa-file-invoice-dollar fa-fw'></i>All Expenses</a></li>
            </ul>
        </li>

        <!-- Budget -->
        <li class="has-sub">
            <a href="#">
                <i class="fas fa-wallet fa-fw"></i>
                <span class="menu-text">Budget</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="sub-menu">              
                <li><a href="manage-budgetstructure.php"><i class="fas fa-sitemap fa-fw"></i>Budget Structure</a></li>
                <li><a href="manage-budgetanalysis.php"><i class="fas fa-chart-line fa-fw"></i>Budget Analysis</a></li>
            </ul>
        </li>

        <!-- Operations Section -->
        <li class="menu-section">
            <span class="menu-text">Operations</span>
        </li>
        
        <!-- Transport --> 
        <li class="has-sub">
            <a href="#">
                <i class="fas fa-bus fa-fw"></i>
                <span class="menu-text">Transport</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="sub-menu">              
                <li><a href="manage-transportstructure.php"><i class="fas fa-network-wired fa-fw"></i>Transport Charges</a></li>
                <li><a href="manage-transportstages.php"><i class="fas fa-map-signs fa-fw"></i>Routes</a></li>
                <li><a href="manage-transportentries.php"><i class="fas fa-route fa-fw"></i>Route Assignments</a></li>
                <li><a href="manage-vehicleshiredetails.php"><i class="fas fa-shuttle-van fa-fw"></i>Hired Transport</a></li>
                <li><a href="manage-vehiclesdetails.php"><i class="fas fa-bus fa-fw"></i>School Vehicles</a></li>           
            </ul>
        </li>

        <!-- Payroll -->
        <li class="has-sub">
            <a href="#">
                <i class="fas fa-money-check fa-fw"></i>
                <span class="menu-text">Payroll</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </a>
            <ul class="sub-menu">
                <li><a href='manage-payrollentries.php'><i class='fas fa-edit fa-fw'></i>Process Payroll</a></li>
                <li><a href='manage-payrolldetails.php'><i class='fas fa-file-alt fa-fw'></i>Payroll Records</a></li>
            </ul>
        </li>
    </ul>
</div>

<style>
    .menu-section {
        padding: 10px 15px;
        font-weight: 700;
        color: #6c757d;
        font-size: 0.85em;
        letter-spacing: 1.5px;
        border-bottom: 1px solid rgba(108,117,125,0.25);
        margin-top: 10px;
        text-transform: uppercase;
    }
    
    .submenu-header {
        font-weight: 600;
        color: #dc3545; /* Bootstrap Danger red */
        padding: 8px 20px 4px 20px;
        font-size: 0.9em;
        border-bottom: 1px solid #dc3545;
        margin-top: 10px;
    }
</style>
