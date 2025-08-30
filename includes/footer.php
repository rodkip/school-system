<nav class="navbar-fixed-bottom" role="navigation" id="navbar">

            <a class="navbar-brand" href="dashboard.php" > <label><span><?php echo $rlt->companyname; ?>.</span><br /></label></a>                
            <?php $cnt=$cnt+1; ?>      
            <img src="images/kipmetzlogo1.png" class="img-rounded logo" >
       
            <!-- end navbar-header -->
            <!-- navbar-top-links -->
            <ul class="nav navbar-top-links navbar-right">
                <!-- main dropdown -->
                <span style="font-family: fantasy;font-size:20px;">
                Kipmetz School Management System </span> 
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-3x"></i>
                    </a>
                    <!-- dropdown user-->

                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="change-password.php"><i class="fa fa-spinner fa-pulse"></i>&nbsp;&nbsp;Change Password</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="logout.php?username=<?php echo htmlentities ($username);?>" ><i class="fa fa-sign-out fa-fw"></i>Logout </a>
                        </li>
                    </ul>
                    <!-- end dropdown-user -->
                </li>
                <!-- end main dropdown -->
            </ul>
            <!-- end navbar-top-links -->

        </nav>
 