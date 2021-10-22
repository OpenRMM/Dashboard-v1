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
        $query = "SELECT * FROM commands WHERE ComputerID='".$_SESSION['computerID']."' and userid='".$_SESSION['userid']."' and status='Received' ORDER BY ID DESC";
        $results = mysqli_query($db, $query);
        $existing = mysqli_fetch_assoc($results);
        $checkrows=mysqli_num_rows($results);
        if($checkrows>0){
            $query = "UPDATE commands SET status='Notified' WHERE userid='".$_SESSION['userid']."' and ComputerID='".$_SESSION['computerID']."';";
            $results = mysqli_query($db, $query);
            echo "<script>toastr.success('Command Was Received.'); </script>";
        }

        //get alert response
        $alertResponse = $json['Alert']['Response'];
        $alertUser = $json['Alert']['Request']['userID'];
        if($alertResponse!="" and $alertUser==$_SESSION['userid']){
            $query = "UPDATE wmidata SET WMI_Name='Alerted' WHERE ComputerID='".$_SESSION['computerID']."' and WMI_Name='Alert';";
            $results = mysqli_query($db, $query);
            echo "<script>toastr.info('".$existing['hostname']." Replied to Message: ".$alertResponse."','',{timeOut:0,extendedTimeOut: 0}); </script>";
        }

        $_SESSION['notifReset2']="";
    }else{
        //not on agent page
        echo "<script>toastr.clear(); </script>";
        $_SESSION['notifReset']="";


    }

    //check if 2 servers
    $query = "SELECT * FROM computerdata WHERE computer_type='OpenRMM Server' and online='1' and active='1' ORDER BY ID DESC";
    $results = mysqli_query($db, $query);
    $existing = mysqli_fetch_assoc($results);
    $checkrows=mysqli_num_rows($results);
    if($checkrows>1 and $_SESSION['notifReset2']==""){
        echo "<script>toastr.error('Two OpenRMM Servers have been detcted. We recommend using one server to avoid conflict.','',{timeOut:0,extendedTimeOut: 0}); </script>";
        $_SESSION['notifReset2']="1";
    }
}

?>
<script>
    toastr.options = {'preventDuplicates': true ,'closeButton': true }
</script>
<?php $_SESSION['excludedPages'] = explode(",",$excludedPages); ?>