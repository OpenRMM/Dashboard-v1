<?php
include("db.php");
if($_SESSION['excludedPages']==""){
    $_SESSION['excludedPages'] = explode(",",$excludedPages); //use this to clear pages if an error occurs
}
$type = $_POST['type'];
$date = $_POST['date'];

$thePage=clean(preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['page']));
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
        setCookie("section", "Init", 365);	
    </script>
<?php 
    exit;
}

$_SESSION['computerID'] = (int)$_GET['ID'];
//$_SESSION['date']=preg_replace("([^0-9/])", "", $_GET['Date']);
if($_SESSION['date']==""){ 
    $_SESSION['date']="latest"; 
}

if($_SESSION['date']!="latest"){
    array_push($_SESSION['excludedPages'],$_SESSION['page']); 
}

if(in_array($_SESSION['page'], $_SESSION['excludedPages']))
    {
       include("../pages/".$_SESSION['page'].".php");  
       $_SESSION['count']=0;
       
       
      
    }else{
        $query = "SELECT ID, hostname, online, last_update FROM computerdata WHERE ID='".$_SESSION['computerID']."' LIMIT 1";
        $results = mysqli_query($db, $query);
        $computer = mysqli_fetch_assoc($results);
        $_SESSION['ComputerHostname']=$computer['hostname'];
        ?>
        <script>
            $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
        </script>
        <?php
        $results = mysqli_fetch_assoc(mysqli_query($db, $query));
        $lastUpdate=$results['last_update'];
        $page = $_SESSION['page'];
        $retain=false;
        $message='{"userID":'.$_SESSION['userid'].'}';
        if($_SESSION['count']==0){
            switch ($page) {
                case "FileManager":
                    $page="Filesystem";
                    $retain = false;
                    $gets = clean(base64_decode($_GET['other']));
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
                case "Disks":
                    $page="LogicalDisk";
                    $retain = false;
                    $message = '{"userID":'.$_SESSION['userid'].'}';
                break;
                case "AttachedDevices":
                    $page="PnPEntitys";
                    $retain = false;
                    $message = '{"userID":'.$_SESSION['userid'].'}';
                break;
                case "Memory":
                    $page="PhysicalMemory";
                    $retain = false;
                    $message = '{"userID":'.$_SESSION['userid'].'}';
                break;
                case "Network":
                    $page="NetworkAdapters";
                    $retain = false;
                    $message = '{"userID":'.$_SESSION['userid'].'}';
                break;
                case "Programs":
                    $page="Products";
                    $retain = false;
                    $message =  '{"userID":'.$_SESSION['userid'].'}';
                break;   
                case "EventLogs":
                    $page="EventLogs";
                    $retain = false;
                    $gets = clean($_GET['other']);
                    if($gets==""){$gets="Application";}
                    $message =  '{"userID":'.$_SESSION['userid'].',"data":"'.$gets.'"}';
                case "General":
                    $page="General";
                    $retain=false;
                    $retain=false;
                    MQTTpublish($_SESSION['computerID']."/Commands/getLogicalDisk",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getServices",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getProcesses",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getProducts",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getNetworkAdapters",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getPrinters",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getUsers",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getPnPEntitys",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getPhysicalMemory",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getFilesystem","Application",getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getEventLogs",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getScreenshot",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getAgent",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getBIOS",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getProcessor",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                    MQTTpublish($_SESSION['computerID']."/Commands/getAgentLog",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),$retain);
                break;     
            }
            MQTTpublish($_SESSION['computerID']."/Commands/get".$page,$message,getSalt(20),$retain);
        }
            sleep(1);
      
        $results = mysqli_fetch_assoc(mysqli_query($db, $query));
        $lastUpdate_new=$results['last_update'];
        if($lastUpdate!=$lastUpdate_new or $computer['online']=="0"){
            include("../pages/".$_SESSION['page'].".php");  
            $_SESSION['count']=0;
        ?>
            <script> 
                $("html, body").animate({ scrollTop: 0 }, "slow"); 
            </script> 
        <?php
        }else{     
            if($_SESSION['count']>15){  //use 15 or 2 for testing
            ?>
                <div class="row col-md-6 mx-auto">
                    <div class="card card-md" style="margin-top:100px;padding:20px"> 
                        <script> 
                            $("html, body").animate({ scrollTop: 0 }, "slow"); 
                        </script> 
                        <center>
                            <h5>Asset: <?php echo $_SESSION['ComputerHostname']; ?> is online but did not respond to a request for <?php echo $_SESSION['page']; ?>.</h5>
                            <br>
                            <h6>Would you like to display the outdated assset data?</h6>
                            <br>
                            <form method="post">
                                <input value="true" type="hidden" name="ignore">
                                <input value="<?php echo $_SESSION['page']; ?>" type="hidden" name="page">
                                <button onclick="location.reload();" class='btn btn-sm btn-primary' type="button" >Retry <i class="fas fa-sync"></i></button>&nbsp;
                                <button class='btn btn-sm btn-warning' style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none;" type="submit" >View Older Asset Information <i class="fas fa-arrow-right"></i></button>  
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
?>
<script>
    var section = getCookie("section");
    if(section == "Edit" ||section == "Profile" || section == "Assets" || section == "Dashboard" || section == "AllUsers" || section == "AllCompanies" || section == "NewComputers" || section == "Versions" || section == "SiteSettings"){
        $('#sectionList').slideUp(400);
    }else if($('#sectionList').css("display")=="none"){
        $('#sectionList').slideDown(400);
    }
</script>
<script>
	<?php if($siteSettings['general']['serverStatus']=="0" or $siteSettings['general']['serverStatus']==""){ ?>
		toastr.remove()
		toastr.error('The Asset Sever is offline. Assets will not be able to send or recieve new data.');
	<?php } ?>
</script>
