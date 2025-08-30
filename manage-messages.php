<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['cpmsaid']) == 0) {
    header('location:logout.php');
} else {
    $academicyear = date("Y");
    $recipientusername = $_GET['recipient'];

    // ...

    if (isset($_POST['submit'])) {
        $recipient = $_POST['recipient'];
        $sender = $_POST['sender'];
        $message = $_POST['message'];
        $status = $_POST['status'];

        try {
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO chatmessages (recipient, sender, message, status) VALUES(:recipient, :sender, :message, :status)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':recipient', $recipient, PDO::PARAM_STR);
            $query->bindParam(':sender', $sender, PDO::PARAM_STR);
            $query->bindParam(':message', $message, PDO::PARAM_STR);
            $query->bindParam(':status', $status, PDO::PARAM_STR);
            $query->execute();

            $messagestate = 'added';
            $mess = "Message posted!!..";
            $recipientusername = $_POST['recipient'];
        } catch (PDOException $e) {
            // Handle the exception and display an error message
            $messagestate = 'error';
            $mess = "An error occurred: " . $e->getMessage();
        }
    }
  //delete a post
  if (isset($_GET['delete'])){ 
      $id=$_GET['delete'];

      $sql ="SELECT * FROM chatmessages WHERE id='$id'";
      $query = $dbh -> prepare($sql);
      $query->execute();
      $results=$query->fetchAll(PDO::FETCH_OBJ);     
      if($query->rowCount() > 0)
    {
  foreach($results as $ow)
    {
        if ($ow->sender!=$username)
        {
        $recipientusername=$ow->sender;
        }
        else
        {
        $recipientusername=$ow->recipient;
        }
    }}

      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
      $sql="DELETE FROM chatmessages WHERE id=$id";
      $dbh->exec($sql);
      $messagestate='deleted';
      $mess="Message Deleted...";
  }
//End deleting message

  //mark read to a message
  if(isset($_GET['read'])){
    $id=$_GET['read'];  
    
    $sql ="SELECT * FROM chatmessages WHERE id=$id";
    $query = $dbh -> prepare($sql);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);     
    if($query->rowCount() > 0)
    {
foreach($results as $rr)
    {
        if ($rr->sender=='$username'){
            $recipientusername=$rr->recipient;    
        }else{
            $recipientusername=$rr->sender;    
        }
  
    }}

    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->query("UPDATE chatmessages SET status='1' WHERE id=$id");
    $messagestate='added';
    $mess="Message marked as read!..";

}
  ?>
<!DOCTYPE html>
<html>

<head>
  <title>Kipmetz-SMS|Chats</title>
  <!-- Core CSS - Include with every page -->
  <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
  <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
  <link href="assets/css/main-style.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <style>
      #myTable.loading-overlay {
        position: relative;
      }
      #myTable.loading-overlay:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9999;
      }
      #myTable.loading-overlay:after {
        content: "Loading...";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        font-size: 1.2em;
        z-index: 10000;
      }
      @keyframes tickAnimation {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
        opacity: 1;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.ticking-icon {
    animation: tickAnimation 0.5s ease-in-out;
}

    </style>
</head>

<body>
  <!--  wrapper -->
  <div id="wrapper">
    <!-- navbar top --> <?php include_once('includes/header.php');?>
    <!-- end navbar top -->
    <!-- navbar side --> <?php include_once('includes/sidebar.php');?>
    <!-- end navbar side -->
    <!--  page-wrapper -->
    <div id="page-wrapper">
      <div class="row">
        <!-- page header -->
        <div class="col-lg-12"> 
            <br>
            <?php 
if ($messagestate == 'added') {
    echo '<div class="popup" id="popup" style="background: rgba(0, 128, 0, 0.7); border-radius: 10px; padding: 5px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
    <i class="fas fa-check-circle ticking-icon" style="color: white;"></i>&nbsp;&nbsp;'; 
    echo '<span style="font-size: 18px; color: white;">' . $mess . '</span>';
    echo '</div>';
} else {
    echo '<div class="popup" id="popup" style="background: rgba(206, 69, 133, 0.7); border-radius: 10px; padding: 5px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999;">
    <i class="fa fa-times"></i>&nbsp;&nbsp;'; 
    echo '<span style="font-size: 18px; color: white;">' . $mess . '</span>';
    echo '</div>';
}
?>
 <br>
                 <table>
                    <tr>
                    <td><h3 class="page-header">Chat <i class="fa fa-weixin fa-fw">
            </i> with <?php echo $recipientusername ?> </h3> <?php include_once('newmessage-popup.php');?> </td>
            <td> &nbsp;&nbsp; <?php
if (!empty($recipientusername)) {
    include_once('newmessage-popup.php');
    echo '<a href="#myModal" data-toggle="modal" class="btn btn-success">
            <i class="fa fa-plus-circle"></i> New Message
          </a>';
}
?></td>
</tr>
</table>
          
          
        </div>
        <!--end page header -->
      </div>
      <!--Quick Info section -->
      <div class="row">
        <div class="col-lg-9">
          <div class="alert alert-info text-left" style="background-color: white; color:black; font-family:'Courier New', Courier, monospace; font-size:18px;">
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped  table-hover table-condensed" id="dataTables-example" style="font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif; font-size:15px;">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Time</th>
                      <th>Sender</th>
                      <th>Message</th>
                      <th>
                      </th>
                      <th>
                      </th>
                    </tr>
                  </thead>
                  <tbody> <?php
            $sql="SELECT * from chatmessages where (sender='$username' and recipient='$recipientusername') or (sender='$recipientusername' and recipient='$username') order by id desc";
            $query = $dbh -> prepare($sql);
            $query->execute();
            $results=$query->fetchAll(PDO::FETCH_OBJ);
            $cnt=1;
            if($query->rowCount() > 0)
            {
            foreach($results as $row)
            {               
        ?> <tr>
                      <td style="width: 3%;"> <?php echo $cnt; ?> </td>
                      <td style="width: 17%;"> <?php echo ($row->time); ?> </td>
                      <td style="width: 8%;"> <?php 
                if ($row->sender==$username)
                {
    echo '<span style="color: green; ">'."You: ".'</span>';
                }
                else
                {
    echo '<span style="color: blue;">'.htmlentities($row->sender).'</span>';
                }
                ; ?> </td>
                      <td> <?php
                if ($row->status=='0' and $row->sender!=$username){
                    echo '<span style="font-weight:bold;">'.$row->message.'</span>'.'&nbsp;&nbsp;<i class="fa  fa-spinner fa-spin fa-fw">
</i>';
                }else{
                    echo $row->message;
                }
               ?> </td>
                      <td style="width: 2%;"> <?php 
            if ($row->sender!=$username){
                echo '<a href="manage-messages.php?read='.$row->id.'.". name="read">
<i class="fa  fa-unlock-alt">
</i>
</a>';
            }else{

                }                
                ?> </td>
                      <td style="width: 2%;">
                        <a href="manage-messages.php?delete=<?php echo htmlentities ($row->id);?>" onclick="return confirm('You want to delete the record?!!')" name="delete">
                          <i class="fa  fa-trash">
                          </i>
                        </a>
                      </td>
                    </tr> <?php 
               
            $cnt=$cnt+1;}} ?> </tbody>
                </table>
                <br>
              </div>
            </div>
          </div>
        </div>
        <!--all user list-->
        <div class="col-lg-3" >
          <div class="alert alert-success " style="background-color: #4682B4;" >
            <span style="color:red; text-align:center; color: Black;">Click on a user to view conversations</span>
            <table class="table table-hover table-condensed" style="font-family: 'Courier New', Courier, monospace; font-size:18px; ">
              <thead>
                <tr>
                  <th>
                  </th>
                  <th>
                  </th>
                  <th>
                  </th>
                  <th>
                  </th>
                </tr>
              </thead>
              <tbody>
            <?php
            $sql = "SELECT * from tbladmin WHERE username != :username";
            $query = $dbh->prepare($sql);
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();
            $results = $query->fetchAll(PDO::FETCH_OBJ);
            $cnt = 1;

            if ($query->rowCount() > 0) {
                foreach ($results as $userslist) {
                    // Calculate the difference between last_active and current time
                    $lastActiveTimestamp = strtotime($userslist->last_active);
                    $currentTime = time();
                    $timeDifference = $currentTime - $lastActiveTimestamp;

                    // Check if user is offline (more than 5 minutes)
                    $isOffline = $timeDifference > (5 * 60); // 5 minutes in seconds

                    ?> 
            <tr>
              <td style="color: white;"><?php echo htmlentities($cnt); ?></td>
              <td >
                  <a href="manage-messages.php?recipient=<?php echo htmlentities($userslist->username); ?>" style="color: white;">
                      <?php
                      echo htmlentities($userslist->username);
                      $sql="SELECT count(status) as unreadmessages from chatmessages where (sender='$userslist->username' and recipient='$username' and status='0') order by id desc";
                      $query = $dbh -> prepare($sql);
                    $query->execute();
                    $results=$query->fetchAll(PDO::FETCH_OBJ);              
                    if($query->rowCount() > 0)
                    {
                        foreach($results as $ww)
                        {  
                            $unreadmessages =$ww->unreadmessages;    
                        }
                    }
                    ?>
                </a>
            </td>
            <td><?php
                if ($isOffline) {
                    echo '<span style="color: black;">Offline</span>';
                } else if ($userslist->status == 'online') {
                    echo '<span style="color: green;">Online</span>';
                } else {
                    echo '<span style="color: white;">' . htmlentities($userslist->status) . '</span>';
                }
                ?></td>
            
                  <td>
                    <span class="badge"> <?php echo $unreadmessages;?> </span>
                  </td>
                </tr> <?php $cnt=$cnt+1;}} ?> </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  <!-- end page-wrapper -->
  </div>
  <!-- end wrapper -->
  </div>
  <!-- end wrapper -->
  <!-- Core Scripts - Include with every page -->
  <script src="assets/plugins/jquery-1.10.2.js">
  </script>
  <script src="assets/plugins/bootstrap/bootstrap.min.js">
  </script>
  <script src="assets/plugins/metisMenu/jquery.metisMenu.js">
  </script>
  <script src="assets/plugins/pace/pace.js">
  </script>
  <script src="assets/scripts/siminta.js">
  </script>
  <!-- Page-Level Plugin Scripts-->
  <script src="assets/plugins/dataTables/jquery.dataTables.js">
  </script>
  <script src="assets/plugins/dataTables/dataTables.bootstrap.js">
  </script>
  <script>
    $(document).ready(function() {
      $('#dataTables-example').dataTable();
    });
  </script>
  <script>
    if (window.history.replaceState) {
      window.history.replaceState(null, null, window.location.href);
    }
  </script>
  <script>
    document.getElementById("defaultOpen").click();
  </script> <?php
      if ($messagestate=='added' or $messagestate=='deleted'){
        echo '<script type="text/javascript">
        function hideMsg()
        {
          document.getElementById("popup").style.visibility="hidden";
        }
        document.getElementById("popup").style.visibility="visible";
        window.setTimeout("hideMsg()",5000);
        </script>';
      }
      ?>
</body>

</html> <?php }  ?>