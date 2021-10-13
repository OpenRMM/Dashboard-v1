<?php
include("db.php");
if($_SESSION['excludedPages']==""){
    $_SESSION['excludedPages'] = explode(",",$excludedPages); //use this to clear pages if an error occurs
}

$_SESSION['page'] = clean(preg_replace("/[^a-zA-Z0-9]+/", "", $_GET['page']));
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
$_SESSION['date']=preg_replace("([^0-9/])", "", $_GET['Date']);
if($_SESSION['date']==""){ 
    $_SESSION['date']="latest"; 
}

if($_SESSION['date']!="latest"){
    array_push($_SESSION['excludedPages'],$_SESSION['page']); 
}

if(in_array($_SESSION['page'], $_SESSION['excludedPages']))
    {
       include("../pages/".$_SESSION['page'].".php");  
       $_SESSION['excludedPages'] = explode(",",$excludedPages);
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
        if($_SESSION['page']=="FileManager"){
            $_SESSION['page']="Filesystem";
        }
        MQTTpublish($_SESSION['computerID']."/Commands/get".$_SESSION['page'],"true",$_SESSION['computerID']);
        sleep(1);
        $results = mysqli_fetch_assoc(mysqli_query($db, $query));
        $lastUpdate_new=$results['last_update'];
        if($lastUpdate!=$lastUpdate_new or $computer['online']=="0"){
            if($_SESSION['page']=="Filesystem"){
                $_SESSION['page']="FileManager";
            }
            include("../pages/".$_SESSION['page'].".php");  
        ?>
            <script> 
                $("html, body").animate({ scrollTop: 0 }, "slow"); 
            </script> 
        <?php
        }else{
            for ($x = 0; $x <= 15; $x++) {
                $query2 = "SELECT last_update FROM computerdata WHERE ID='".$_SESSION['computerID']."' LIMIT 1";
                $results2 = mysqli_fetch_assoc(mysqli_query($db, $query2));
                $lastUpdate2_new=$results2['last_update'];
                if($lastUpdate!=$lastUpdate2_new){ 
                    include("../pages/".$_SESSION['page'].".php");
                    break;
                }
                if($x==15){  //use 15 or 2 for testing
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
                break;
                }
                sleep(2);
                header("Refresh:0");
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