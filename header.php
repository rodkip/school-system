<nav class="navbar-fixed-top" role="navigation" id="navbar">
            <!-- navbar-header -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle"  data-target=".sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

         </div>

<!--for userdetails-->
<?php

$aid=$_SESSION['cpmsaid'];
$sql="SELECT * from  tbladmin where ID=:aid";
$query = $dbh -> prepare($sql);
$query->bindParam(':aid',$aid,PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $row)
{               ?>
<?php $username=$row->username;?>
<?php $fullnames=$row->fullnames;?>
<?php $accounttype=$row->accounttype;?>
<?php $cnt=$cnt+1;}} ?>
       
            <?php      
            
                      $searchquery="SELECT * from schooldetails";
                      $qry = $dbh -> prepare($searchquery);
                      $qry->execute();
                      $row=$qry->fetchAll(PDO::FETCH_OBJ);
                      $cnt=1;
                      if($qry->rowCount() > 0)
                    {
                      foreach($row as $rlt)
                    {   
                  
                  ?>
            <a class="navbar-brand" href="dashboard.php" > <label><span><?php echo $rlt->schoolname; ?>.</span><br /></label></a>                
            <?php $cnt=$cnt+1;}} ?>      
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
                        <li><a href="logout.php?username=<?php echo htmlentities ($username);?>" "><i class="fa fa-sign-out fa-fw"></i>Logout </a>
                        </li>
                    </ul>
                    <!-- end dropdown-user -->
                </li>
                <!-- end main dropdown -->
            </ul>
            <!-- end navbar-top-links -->

        </nav>
 