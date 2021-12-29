<?php
include("db.php");
if($_SESSION['excludedPages']==""){
    $_SESSION['excludedPages'] = explode(",",$excludedPages); //use this to clear pages if an error occurs
}
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  // do nothing
}else {
    // if page is not called via ajax
   exit("<html><head><title>OpenRMM console</title></head><body style='background:#000'><div style='margin-top:20px;color:#fff;font-family: \"Lucida Console\", \"Courier New\", monospace;font-size:14px'><h3>OpenRMM console > An error has occurred</h3></div></body></html>");
}
?>
    <script>       
        $("#refreshAlert").slideUp();
        $("#alertDiv").css({"margin-top": "0px"});
        <?php if($_SESSION['userid']!=""){ ?>
            $(".recents").load("includes/recent.php?ID="+getCookie("ID"));
        <?php } ?>
    </script>
<?php
$type = clean($_POST['type']);
$gets = clean(base64_decode($_GET['other']));
$thePage=clean(base64_decode($_GET['page']));
if($thePage!=""){
    $_SESSION['page'] = $thePage;
}
if(!in_array($_SESSION['page'], $allPages) || $_SESSION['page']==""){ 
    exit("<center><h5>This page could not be found.</h5></center>");
}
$query = "SELECT * FROM users";
$result = mysqli_num_rows(mysqli_query($db, $query));
if(!file_exists("config.php") or !$db or $mqttConnect=="timeout" or $result==0){
    $_SESSION['excludedPages'] = explode(",",$excludedPages);
    $_SESSION['userid']="";
    session_unset();
    session_destroy();
    $_SESSION['excludedPages'] = explode(",",$excludedPages);
    include("../pages/Init.php");
?>
    <script> 
        setCookie("section", btoa("Init"), 365);	
    </script>
<?php 
    exit;
}

$query = "UPDATE users SET last_login='".time()."' WHERE ID=".$_SESSION['userid'].";";
$results = mysqli_query($db, $query);
$_SESSION['computerID'] = (int)base64_decode($_GET['ID']);
if(in_array($_SESSION['page'], $_SESSION['excludedPages']))
    {  $_SESSION['computerID']="";
       include("../pages/".$_SESSION['page'].".php");  
       $_SESSION['count']=0;
    }else{
       
        $query = "SELECT ID, online FROM computers WHERE ID='".$_SESSION['computerID']."' LIMIT 1";
        $results = mysqli_query($db, $query);
        $computer = mysqli_fetch_assoc($results);
   
        $json = getComputerData($computer['ID'], array("general"));
        $hostname =  textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
        $_SESSION['ComputerHostname']=$hostname;
        ?>
        <script>
            $(".sidebarComputerName").text("<?php echo textOnNull($_SESSION['ComputerHostname'],"Unavailable");?>");
        </script>
        <?php
        $results = mysqli_fetch_assoc(mysqli_query($db, $query));

        $page = strtolower(str_replace("Asset_","",$_SESSION['page']));
        if($gets=="force" or $page=="file_manager") {       
            $retain=false;       
            $message='{"userID":'.$_SESSION['userid'].'}';
            if($_SESSION['count']==0){
                switch ($page) {
                    case "file_manager":
                        $page="filesystem";
                        $retain = false;   
                        if($gets=="force"){ $gets=""; }                  
                        $get = explode("{}",$gets);
                        $drive = $get[0];
                        $getFolder = $get[1];
                        if($drive==""){
                            $drive="C";
                        }
                        $message = $drive.":/".$getFolder;
                        $message = str_replace("//","/",$message);
                        if($message==""){ $message=$drive.":/"; }
                        $message = '{"userID":'.$_SESSION['userid'].',"data":"'.$message.'"}';
                    break;
                    case "disks":
                        $page="logical_disk";
                        $retain = false;
                        $message = '{"userID":'.$_SESSION['userid'].'}';
                        MQTTpublish($_SESSION['computerID']."/Commands/get_mapped_logical_disk",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_shared_drives",$message,getSalt(20),$retain);
                    break;
                    case "attached_devices":
                        $page="pnp_entities";
                        $retain = false;
                        $message = '{"userID":'.$_SESSION['userid'].'}';
                        MQTTpublish($_SESSION['computerID']."/Commands/get_video_configuration",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_PointingDevice",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_desktop_monitor",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_keyboard",$message,getSalt(20),$retain);
                    break;
                    case "memory":
                        $page="physical_memory";
                        $retain = false;
                        $message = '{"userID":'.$_SESSION['userid'].'}';
                    break;
                    case "network":
                        $page="network_adapters";
                        $retain = false;
                        $message = '{"userID":'.$_SESSION['userid'].'}';
                    break;
                    case "programs":
                        $page="products";
                        $retain = false;
                        $message =  '{"userID":'.$_SESSION['userid'].'}';
                    break;   
                    case "event_logs":
                        $page="event_logs";
                        $retain = false;
                        if($gets=="" or $gets=="force"){$gets="Application";}
                        $message =  '{"userID":'.$_SESSION['userid'].',"data":"'.$gets.'"}';
                    case "general":
                        $page="general";
                        $retain=false;
                        $retain=false;
                        $message='{"userID":'.$_SESSION['userid'].'}';
                        MQTTpublish($_SESSION['computerID']."/Commands/get_logical_disk",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_services",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_processes",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_products",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_network_adapters",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_printers",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_users",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_pnp_entitys",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_physical_memory",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_filesystem",'{"userID":'.$_SESSION['userid'].',"data":"C:/"}',getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_event_logs",'{"userID":'.$_SESSION['userid'].',"data":"Application"}',getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_screenshot",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_agent",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_bios",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_processor",$message,getSalt(20),$retain);
                        MQTTpublish($_SESSION['computerID']."/Commands/get_agent_log",$message,getSalt(20),$retain);
                    break;     
                }
                $json2 = getComputerData($computer['ID'], array("$page"));
                $lastUpdate = $json2[$page.'_lastUpdate'];
                MQTTpublish($_SESSION['computerID']."/Commands/get_".$page,$message,getSalt(20),$retain);
            }       
            sleep(1);
            $json2 = getComputerData($computer['ID'], array("$page"));
            $lastUpdate_new = $json2[$page.'_lastUpdate'];
        }else{
            $lastUpdate_new = "1"; 
            $lastUpdate = "2"; 
        }      
        if($lastUpdate!=$lastUpdate_new or $computer['online']=="0"){
            $query = "SELECT ID, online FROM computers WHERE ID='".$_SESSION['computerID']."' LIMIT 1";
            $results = mysqli_query($db, $query);
            $computer = mysqli_fetch_assoc($results);
            
            $json = getComputerData($computer['ID'], array("general"));
            $hostname =  textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
            $_SESSION['ComputerHostname']=$hostname;
            ?>
            <script>
                $(".sidebarComputerName").text("<?php echo textOnNull($_SESSION['ComputerHostname'],"Unavailable");?>");
            </script>
            <?php
            include("../pages/".$_SESSION['page'].".php");  
            $_SESSION['count']=0;
        ?>
        <?php if($_SESSION['raw_data_title']==""){ ?>
            <script> 
                $("html, body").animate({ scrollTop: 0 }, "slow"); 
            </script> 
        <?php
        }
    }else{     
            if($_SESSION['count']>15){  //use 15 or 2 for testing
            ?>
                <div class="row col-md-6 mx-auto">
                    <div class="card card-md" style="margin-top:100px;padding:20px"> 
                        <script> 
                            $("html, body").animate({ scrollTop: 0 }, "slow"); 
                        </script> 
                        <center>
                            <h5>Asset: <?php echo $_SESSION['ComputerHostname']; ?> is online but did not respond to a request for <?php echo str_replace("_"," ",str_replace("Asset_","",$_SESSION['page'])); ?>.</h5>
                            <br>
                            <h6>Would you like to display the outdated assset data?</h6>
                            <br>
                            <form method="post">
                                <input value="true" type="hidden" name="ignore">
                                <input value="<?php echo $_SESSION['page']; ?>" type="hidden" name="page">
                                <button onclick="location.reload();" class='btn btn-sm btn-primary' type="button" >Retry <i class="fas fa-sync"></i></button>&nbsp;
                                <button class='btn btn-sm' style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none;color:#0c5460" type="submit" >View Older Asset Information <i class="fas fa-arrow-right"></i></button>  
                            </form>
                        <center>
                    </div> 
                </div>
            <?php
                $_SESSION['count']="";
                exit;
            }else{
                if($_SESSION['count']==""){  $_SESSION['count']=0; }
                $_SESSION['count']++;
                Header('Location: https://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
             }
        } 
    }
    $query = "SELECT ID FROM tickets where active='1' and status<>'Closed'";
    $ticketCount = mysqli_num_rows(mysqli_query($db, $query));
    $query = "SELECT ID FROM computers where active='1' and online='1'";
    $assetCount = mysqli_num_rows(mysqli_query($db, $query));

    $query = "SELECT  * FROM asset_messages WHERE chat_viewed='0' and userid='0'";
    $message_count = mysqli_num_rows(mysqli_query($db, $query)); 
?>
<script>
$("#messageCount").text("<?php echo (int)$message_count; ?>");
$("#ticketCount").text("<?php echo $ticketCount; ?>"); 
$("#assetCount").text("<?php echo $assetCount; ?>");
</script>
<?php 
$_SESSION['raw_data_title']=""; 
$_SESSION['raw_data_value']="";
$_SESSION['raw_data_value_raw']="";
?>
<?php 
if($_SESSION['userid']!=""){
    require("modals.php"); 
?>
        <div id="notifications"> </div>
        <script>
            setInterval(function(section=currentSection, ID=computerID, date=sectionHistoryDate,other=otherEntry) {
                $("#notifications").load("includes/notifications.php?ID="+btoa(ID)+"&Date="+btoa(date)+"&page="+btoa(section)+"&other="+btoa(other));	
            }, 5000);
            <?php if($siteSettings['general']['server_status']=="0" or $siteSettings['general']['server_status']==""){ ?>
                toastr.remove()
                toastr.error('The Asset Sever is offline. Assets will not be able to send or recieve new data.');
            <?php } ?>
        </script>
<?php } ?>