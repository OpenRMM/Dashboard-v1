<?php
include("db.php");
//$_SESSION['computerID'] = (int)base64_decode($_GET['ID']);

$_SESSION['page'] = clean(base64_decode($_GET['page']));
$computerID = (int)base64_decode($_GET['ID']);
if($_SESSION['userid']!=""){
    if(!in_array($_SESSION['page'], $_SESSION['excludedPages']) or $_SESSION['page']=="EventLogs" or $_SESSION['page']=="Commands")
    {  //on agent page
        $json = getComputerData($_SESSION['computerID'], array("alert","general"));

        $query = "SELECT * FROM computers WHERE ID='".$_SESSION['computerID']."' ORDER BY ID DESC";
        $results = mysqli_query($db, $query);
        $existing = mysqli_fetch_assoc($results);

        //check if command received.
        $query = "SELECT * FROM commands WHERE computer_id='".$_SESSION['computerID']."' and user_id='".$_SESSION['userid']."' and status='Received' ORDER BY ID DESC";
        $results = mysqli_query($db, $query);
        $existing = mysqli_fetch_assoc($results);
        $checkrows=mysqli_num_rows($results);
        if($checkrows>0){
            $query = "UPDATE commands SET status='Notified' WHERE user_id='".$_SESSION['userid']."' and computer_id='".$_SESSION['computerID']."';";
            $results = mysqli_query($db, $query);  
            saveNotification("Command Was Received By Asset: ".$result['computer_id']);
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
    $query = "SELECT * FROM servers WHERE active='1' ORDER BY ID ASC";
    $results = mysqli_query($db, $query);
    $count=0;
    while($servers = mysqli_fetch_assoc($results)){	
        if(strtotime($servers['last_update']) < strtotime('-2 minutes')) {
            continue;
        }else{
            $count++;
        }
        if($count>1){
            echo "<script>toastr.error('".$count." OpenRMM Servers have been detected. We recommend using one server to avoid conflicts.<br><br><button onclick=\'loadSection(\"Servers\");\' class=\'btn btn-sm btn-secondary\'>View Servers</button>','',{timeOut:0,extendedTimeOut: 0}); </script>";
            $_SESSION['notifReset2']="1";
            saveNotification($count." OpenRMM Servers have been detected. We recommend using one server to avoid conflicts.");
        }
    }
}
if($_SESSION['page']!="Asset_Chat"){
   // if(in_array("AssetChat", $allowed_pages)){
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
   // }
}
$page = strtolower(str_replace("Asset_","",$_SESSION['page']));
switch ($page) {
    case "file_manager":
        $page="filesystem";  
    break;
    case "disks":
        $page="logical_disk,mapped_logical_disk,shared_drives";
    break;
    case "attached_devices":
        $page="pnp_entities,video_configuration,PointingDevice,desktop_monitor,keyboard";
    break;
    case "memory":
        $page="physical_memory";
    break;
    case "network":
        $page="network_adapters";
    break;
    case "programs":
        $page="products";
    break;   
    case "general":
        $page="general,logical_disk,services,processes,products,network_adapters,printers,users,pnp_entitys,physical_memory,filesystem,event_logs,screenshot,agent,bios,processor,agent_log";
    break;     
}
$pages = explode(",",$page);
foreach ($pages as $value) {
    $query = "SELECT ID,last_update FROM computer_data WHERE name='".$page."' and computer_id='".$computerID."' ORDER BY ID DESC";
    $results2 = mysqli_query($db, $query);
    $existing = mysqli_fetch_assoc($results2);
    $timeNow = strtotime(explode(".",$existing['last_update'])[0]);
    if($existing['ID']!=""){
        if($timeNow>$_SESSION['dbRows']){
?>
    <script>
        $("#refreshAlert").slideDown();
        $("#alertDiv").css({"margin-top": "-40px"});
        $("#refreshAlert").html('<strong><?php echo ucwords(str_replace("_"," ",$page)); ?> has updated.</strong> Refresh the page to see the newly updated content. <button type="button"  onclick="loadSection(\'<?php echo $_SESSION['page']; ?>\');" style="margin-left:20px" class="btn btn-dark btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>');
    </script>
<?php
        }else{ ?>
            <script>
                $("#refreshAlert").slideUp();
                $("#alertDiv").css({"margin-top": "0px"});
            </script>
        <?php
        }
    }
}
?>
<script>
    toastr.options = {'preventDuplicates': true ,'closeButton': true }
</script>
<?php $_SESSION['excludedPages'] = explode(",",$excludedPages); ?>
<?php
    $query = "SELECT * FROM asset_messages WHERE chat_viewed='0' and userid='0'";
    $message_count = mysqli_num_rows(mysqli_query($db, $query)); 
?>
<script>
$("#messageCount").text("<?php echo (int)$message_count; ?>");
</script>

<?php
    $data="";
    $notif = array_slice($_SESSION['notifications'], -8, 8, true);
    $count = 0;
    foreach(array_reverse($notif) as $item) {
        if($item==""){ continue; }
        $count++;
        $data .='<li class="list-group-item">'.$item.'</li>';
         } 
    if($count==0){ 
        $data .='<li class="list-group-item">No New Notifications</li>';
    }
?>
<script>
   $("#notificationList").html('<?php echo $data; ?>');  
   $("#notificationCount").html('<?php echo count($_SESSION['notifications'])-1; ?>');  
</script>





