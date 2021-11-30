<?php
include("db.php");
//$_SESSION['computerID'] = (int)base64_decode($_GET['ID']);
$_SESSION['page'] = clean(preg_replace("/[^a-zA-Z0-9]+/", "", base64_decode($_GET['page'])));

if($_SESSION['userid']!=""){
    if(!in_array($_SESSION['page'], $_SESSION['excludedPages']) or $_SESSION['page']=="EventLogs" or $_SESSION['page']=="Commands")
    {  //on agent page
        $json = getComputerData($_SESSION['computerID'], array("alert","general"));

        $query = "SELECT * FROM computers WHERE ID='".$_SESSION['computerID']."' ORDER BY ID DESC";
        $results = mysqli_query($db, $query);
        $existing = mysqli_fetch_assoc($results);

        //duplicate hostname
       // $query = "SELECT * FROM computers WHERE hostname='".$existing['hostname']."' ORDER BY ID DESC";
       // $results = mysqli_query($db, $query);
       // $checkrows=mysqli_num_rows($results);
        //if( $checkrows>1 and $_SESSION['notifReset']==""){
         //   echo "<script> toastr.error('Warning! Duplicate Hostnames Detected For This Asset.'); </script>";
         //   $_SESSION['notifReset']="1";
       // }

        //check if command received.
        $query = "SELECT * FROM commands WHERE computer_id='".$_SESSION['computerID']."' and user_id='".$_SESSION['userid']."' and status='Received' ORDER BY ID DESC";
        $results = mysqli_query($db, $query);
        $existing = mysqli_fetch_assoc($results);
        $checkrows=mysqli_num_rows($results);
        if($checkrows>0){
            $query = "UPDATE commands SET status='Notified' WHERE user_id='".$_SESSION['userid']."' and computer_id='".$_SESSION['computerID']."';";
            $results = mysqli_query($db, $query);  
            echo "<script>toastr.success('Command Was Received.'); </script>";          
        }

     
        
					

        //get alert response
        $alertResponse = $json['alert'];
        $alertUser = $json['alert']['Request']['userID'];
        if($alertResponse!="" and $alertUser==$_SESSION['userid']){
            $query = "UPDATE computer_data SET name='Alerted' WHERE computer_id='".$_SESSION['computerID']."' and name='Alert';";
            $results = mysqli_query($db, $query);
            echo "<script>toastr.info('".$existing['hostname']." replied to message: ".print_r($alertResponse)."','',{timeOut:0,extendedTimeOut: 0}); </script>";
        }
        $_SESSION['notifReset2']="";
    }else{
        //not on agent page
        //echo "<script>toastr.clear(); </script>";
        $_SESSION['notifReset']="";
    }

    //check if 2 servers
    $query = "SELECT * FROM computers WHERE computer_type='OpenRMM Server' and online='1' and active='1' ORDER BY ID DESC";
    $results = mysqli_query($db, $query);
    $existing = mysqli_fetch_assoc($results);
    $checkrows=mysqli_num_rows($results);
    if($checkrows>1 and $_SESSION['notifReset2']==""){
        echo "<script>toastr.error('Two OpenRMM Servers have been detected. We recommend using one server to avoid conflicts.','',{timeOut:0,extendedTimeOut: 0}); </script>";
        $_SESSION['notifReset2']="1";
    }
}
if($_SESSION['page']!="Asset_Chat"){
    $query = "SELECT * FROM asset_messages where userid='0' and chat_started='0' ORDER BY ID ASC Limit 1";
    $results = mysqli_query($db, $query);
    $checkrows = mysqli_num_rows($results);
    $existing = mysqli_fetch_assoc($results);
        
    if($checkrows>0){
        $json = getComputerData($existing['computer_id'], array("general"));
        $hostname = textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
        
    ?>
    <script>toastr.info('Asset: <?php echo $hostname; ?> Sent you a message<br><br><center><button onclick=\'loadChat(\"<?php echo $existing['computer_id']; ?>\");\' data-toggle=\'modal\' data-target=\'#asset_message_modal\' class=\'btn btn-sm btn-secondary\'>View Chat</button></center>','',{timeOut:0,extendedTimeOut: 0}); </script>
    <?php
        sleep(6);
        $query = "UPDATE asset_messages SET chat_started='1' WHERE ID='".$existing['ID']."' and chat_started='0';";
        $results = mysqli_query($db, $query);
    }
}
?>
<script>
    toastr.options = {'preventDuplicates': true ,'closeButton': true }
</script>
<?php $_SESSION['excludedPages'] = explode(",",$excludedPages); ?>
<?php
    $query = "SELECT  * FROM asset_messages WHERE chat_viewed='0' and userid='0'";
    $message_count = mysqli_num_rows(mysqli_query($db, $query)); 
?>
<script>
$("#messageCount").text("<?php echo (int)$message_count; ?>");
</script>
