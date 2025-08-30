<?php 
require_once("includes/dbconnection.php");
if(!empty($_POST["studentadmno"])) {
    $studentadmno= $_POST["studentadmno"];
    
	$sql ="SELECT feestructurename FROM feestructure WHERE feestructurename='$feestructurename'";
    $query = $dbh -> prepare($sql);
    $query->execute();
    $results=$query->fetchAll(PDO::FETCH_OBJ);
    $cnt=1;
    if($query->rowCount() > 0)
    {
foreach($results as $row)
{
    echo "<span style='color:green'>Student Name: $row->studentname</span>";
    echo "<script>$('#submit').prop('disabled',false);</script>";
}
} else{
    echo "<span style='color:red'> The StudentAdmNo doesn't exists .</span>";
    echo "<script>$('#submit').prop('disabled',true);</script>";
}

}



?>