<?php
include("db.php");
//$_SESSION['computerID'] = (int)$_GET['ID'];
$_SESSION['page'] = clean(preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['page']));

if($_SESSION['userid']!=""){
    if(!in_array($_SESSION['page'], $_SESSION['excludedPages']) or $_SESSION['page']=="EventLogs" or $_SESSION['page']=="Commands")
    {  //on agent page
    $json = getComputerData($_SESSION['computerID'], array("*"), "latest");

    $query = "SELECT * FROM computerdata WHERE ID='".$_SESSION['computerID']."' ORDER BY ID DESC";
    $results = mysqli_query($db, $query);
    $existing = mysqli_fetch_assoc($results);

    //duplicate hostname
    $query = "SELECT * FROM computerdata WHERE hostname='".$existing['hostname']."' ORDER BY ID DESC";
    $results = mysqli_query($db, $query);
    $checkrows=mysqli_num_rows($results);
    if( $checkrows>1 and $_SESSION['notifReset']==""){
        echo "<script> toastr.error('Warning! Duplicate Hostnames Detected For This Asset.'); </script>";
        $_SESSION['notifReset']="1";
    }

    //check if command received.
    $query = "SELECT * FROM commands WHERE ComputerID='".$_SESSION['computerID']."' and status='Received' ORDER BY ID DESC";
    $results = mysqli_query($db, $query);
    $existing = mysqli_fetch_assoc($results);
    $checkrows=mysqli_num_rows($results);
    if($checkrows>0){
        $query = "UPDATE commands SET status='Notified' WHERE ComputerID=".$_SESSION['computerID'].";";
        $results = mysqli_query($db, $query);
        echo "<script>toastr.success('Command Was Received.'); </script>";
    }
    
    //get alert response
    $alertResponse = $json['Alert']['Response'];
    if($alertResponse!=""){
        $query = "UPDATE wmidata SET WMI_Name='Alerted' WHERE ComputerID=".$_SESSION['computerID']." and WMI_Name='Alert';";
        $results = mysqli_query($db, $query);
        echo "<script>toastr.info('User Replied to Message: ".$alertResponse."','',{timeOut:0,extendedTimeOut: 0}); </script>";
    }

    }else{
        //not on agent page
        echo "<script>toastr.clear(); </script>";
        $_SESSION['notifReset']="";


    }

//echo "<script>toastr.error('This is a test.');</script>";

}

?>
<script>
    toastr.options = {'preventDuplicates': true ,'closeButton': true }
</script>