<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<nav class="navbar-fixed-top" role="navigation" id="navbar" style="background: linear-gradient(
        45deg,
        rgba(8, 48, 107, 1) 0%,     /* Deep ocean blue */
        rgba(25, 103, 210, 1) 50%,  /* Vibrant azure */
        rgba(72, 172, 240, 1) 100%  /* Light sky blue */
    );
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    transition: background 0.3s ease;
">


    <!-- for user details -->

    <?php 
    $programmingyear = date("Y");
    $aid = $_SESSION['cpmsaid']; // Ensure session is properly initiated
    $sql = "SELECT * FROM tbladmin WHERE ID = :aid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':aid', $aid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    // Ensure the variables are sanitized before displaying
    if ($query->rowCount() > 0) {
        foreach ($results as $row) {
            $username = htmlentities($row->username, ENT_QUOTES, 'UTF-8');
            $_SESSION['username'] = htmlentities($row->username, ENT_QUOTES, 'UTF-8');
            $fullnames = htmlentities($row->fullnames, ENT_QUOTES, 'UTF-8');
            $accounttype = htmlentities($row->accounttype, ENT_QUOTES, 'UTF-8');
        }
    }
    ?>

    <!-- company details -->
    <?php
    $searchquery = "SELECT * FROM schooldetails";
    $qry = $dbh->prepare($searchquery);
    $qry->execute();
    $schooldetails = $qry->fetchAll(PDO::FETCH_OBJ);
    
    if ($qry->rowCount() > 0) {
        foreach ($schooldetails as $rlt) {
            $schoolname = htmlentities($rlt->schoolname, ENT_QUOTES, 'UTF-8');
            $latestbackupdate = htmlentities($rlt->latestbackupdate, ENT_QUOTES, 'UTF-8');
            $latestbackupusername = htmlentities($rlt->latestbackupusername, ENT_QUOTES, 'UTF-8');
    ?>
                 
    <span style="float: left; padding: 10px;">
        <img src="images/schoollogo.png" alt="<?php echo $schoolname; ?>" width="70" height="60"  />
    </span>
    <br>
    <a class="navbar-brand" href="dashboard.php" style="text-decoration: none;">
    <style>
    @keyframes glow {
        0% {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(173, 216, 230, 0.8), 0 0 30px rgba(72, 172, 240, 0.8);
        }
        50% {
            text-shadow: 0 0 20px rgba(255, 255, 255, 1), 0 0 30px rgba(173, 216, 230, 1), 0 0 40px rgba(25, 103, 210, 0.8);
        }
        100% {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(72, 172, 240, 0.8), 0 0 30px rgba(8, 48, 107, 0.8);
        }
    }

    .school-name-container {
        display: inline-block;
        position: relative;
        padding: 5px 15px;
        margin-left: 10px;
    }
    
    .school-name-main {
        color: rgb(255, 255, 255);
        font-size: 40px;
        font-weight: 700;
        letter-spacing: 1px;
        position: relative;
        z-index: 2;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }
    
    .school-name-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.1);
        border-radius: 30px;
        z-index: 1;
        transform: rotate(-2deg);
    }
    
    .dropdown-menu {
        background-color: rgba(25, 103, 210, 0.98) !important;
        border: 1px solid rgba(255, 255, 255, 0.15);
        min-width: 220px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        padding: 5px 0;
    }
    
    .dropdown-menu li a {
        color: #ffffff !important;
        padding: 10px 20px;
        display: block;
        transition: all 0.3s ease;
        font-size: 14px;
        border-left: 3px solid transparent;
    }
    
    .dropdown-menu li a:hover {
        background-color: rgba(8, 48, 107, 0.7);
        padding-left: 25px;
        border-left: 3px solid #FFD700;
    }
    
    .dropdown-menu li a i {
        margin-right: 10px;
        width: 18px;
        text-align: center;
    }
    
    .account-type-badge {
        background: rgba(255,215,0,0.15);
        color: #FFD700;
        padding: 5px 15px;
        display: inline-block;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        margin: 5px 15px;
        border: 1px solid rgba(255,215,0,0.3);
    }
    
    .badge-success {
        background-color: #28a745;
    }
    
    .user-profile-header {
        padding: 15px;
        background: rgba(0,0,0,0.1);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 5px;
    }
    </style>

    <div class="school-name-container">
        <div class="school-name-bg"></div>
        <div class="school-name-main glowing-text">
            <?php echo $schoolname; ?>
        </div>
    </div>
 
    </a>
  

                   
          
    <!-- navbar-top-links -->
    <ul class="nav navbar-top-links navbar-right">
        <!-- main dropdown -->  
       <strong style="color: #ffffff; font-size: 18px;">
    Latest BACKUP:
        </strong>
        <span style="font-size: 18px; color:rgb(250, 6, 31);">
            <?php echo htmlspecialchars($latestbackupdate); ?>
        </span>
        <span style="color: #dee2e6; font-weight: 500;">
            by:
        </span>
        <span style="font-size: 18px; color: #f8f9fa;">
            <?php echo htmlspecialchars($latestbackupusername); ?>
        </span>

        <?php
        }
    }
    ?>

         <!-- display unread messages -->
         <?php 
         // Sanitize and display unread messages
         $sql = "SELECT COUNT(status) as unreadmessages FROM chatmessages WHERE recipient = :username AND status = '0'";
         $query = $dbh->prepare($sql);
         $query->bindParam(':username', $username, PDO::PARAM_STR);
         $query->execute();
         $unreadMessagesCount = 0;
         
         $results = $query->fetchAll(PDO::FETCH_OBJ);
         if ($query->rowCount() > 0) {
             foreach ($results as $ww) {
                 $unreadMessagesCount = htmlentities($ww->unreadmessages, ENT_QUOTES, 'UTF-8');
             }
         }
         ?>

        <li>  
            <a href="manage-messages.php" style="color: white; position: relative;">
                <i class="fa fa-envelope"></i>
                <span class="badge badge-success badge-lg" style="position: absolute; top: -5px; right: -5px;"><?php echo $unreadMessagesCount; ?></span>
            </a>
        </li>
          
        <!-- end display unread messages -->             

        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#" style="color: white; display: flex; align-items: center;">
                <i class="bi bi-person-circle" style="font-size: 1.5rem; margin-right: 8px;"></i>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-weight: 600; line-height: 1.2;"><?php echo htmlentities($fullnames); ?></span>
                    <small style="font-size: 11px; opacity: 0.8;"><?php echo htmlentities($accounttype); ?></small>
                </div>
                <i class="bi bi-chevron-down" style="margin-left: 5px; font-size: 0.8rem;"></i>
            </a>
    
            <!-- dropdown user -->
            <ul class="dropdown-menu">  
                <div class="user-profile-header">
                    <div style="font-weight: 600; font-size: 15px;"><?php echo htmlentities($fullnames); ?></div>
                    <div class="account-type-badge"><?php echo htmlentities($accounttype); ?></div>
                </div>
                <li>
                    <?php
                        if ($accounttype != "Member") {
                            echo "
                            <a href='change-password.php'>
                                <i class='bi bi-lock-fill'></i>&nbsp;&nbsp;Change Password
                            </a>";
                        }
                    ?>
                    <a href="logout.php?username=<?php echo urlencode($username); ?>">
                        <i class="bi bi-box-arrow-right"></i>&nbsp;&nbsp;Logout
                    </a>
                </li>  
                
                <!-- Footer -->
                <li class="sidebar-footer" style="padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 5px; background: rgba(0,0,0,0.1);">
                    <div style="color: #ffffff; font-weight: 500; margin-bottom: 5px;">Developed by Kipmetz Solutions</div>
                    <div style="color: #e0e0e0; font-size: 12px; margin-bottom: 3px;">
                        <i class="bi bi-envelope"></i> kiplimos@gmail.com
                    </div>
                    <div style="color: #e0e0e0; font-size: 12px; margin-bottom: 5px;">
                        <i class="bi bi-phone"></i> 0721859015
                    </div>
                    <small style="color: #b0b0b0; font-size: 11px;">© 2025 All Rights Reserved</small>
                </li>
            </ul>
        </li>
    </ul>
</nav>
