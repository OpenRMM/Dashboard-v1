<?php
include("../Includes/db.php");

$_SESSION['page'] = strip_tags(ucwords($_GET['page']));
$_SESSION['computerID'] = (int)$_GET['ID'];
$_SESSION['date']=$_GET['Date'];
if($_SESSION['date']==""){ 
    $_SESSION['date']="latest"; 
}
if($_SESSION['page']==""){
    echo "<center><h5>This page could not be loaded.</h5></center>";
    exit;
}
if($_SESSION['date']!="latest"){
    array_push($_SESSION['excludedPages'],$_GET['page']);
}
if (in_array($_SESSION['page'], $_SESSION['excludedPages']))
    {
       include($_SESSION['page'].".php");  
       $_SESSION['excludedPages'] = array("Dashboard","SiteSettings","Users","Profile","Edit","AllUsers","AllCompanies","Assets","NewComputers","Versions"); 
    }else{
        $query = "SELECT ID, hostname FROM computerdata WHERE ID='".$_SESSION['computerID']."' LIMIT 1";
        $results = mysqli_query($db, $query);
        $computer = mysqli_fetch_assoc($results);
        $_SESSION['ComputerHostname']=$computer['hostname'];
        ?>
        <script>
            $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
        </script>
        <?php
        $query = "SELECT last_update FROM computerdata WHERE ID='".$_SESSION['computerID']."' LIMIT 1";
        $results = mysqli_fetch_assoc(mysqli_query($db, $query));
        $lastUpdate=$results['last_update'];
        MQTTpublish($computer['hostname']."/Commands/get".$_SESSION['page'],"true",$computer['hostname']);
        sleep(1);
        $query = "SELECT last_update FROM computerdata WHERE ID='".$_SESSION['computerID']."' LIMIT 1";
        $results = mysqli_fetch_assoc(mysqli_query($db, $query));
        $lastUpdate_new=$results['last_update'];
        
        if($lastUpdate!=$lastUpdate_new){
            include($_SESSION['page'].".php");  
        }else{
            for ($x = 0; $x <= 9; $x++) {
                if($lastUpdate!=$lastUpdate_new){ 
                    break;
                }
                if($x==1){  //use 9 or 1 for testing
                ?>
                <div style="margin-top:100px"> 
                <script> 
                    $("html, body").animate({ scrollTop: 0 }, "slow"); 
                </script> 
                    <center>
                        <h5>Asset: <?php echo $_SESSION['ComputerHostname']; ?> could not be contacted.</h5>
                        <br>
                        <h6>Would you like to display the outdated assset data?</h6>
                        <br>
                        <form method="post">
                            <input value="true" type="hidden" name="ignore">
                            <input value="<?php echo $_SESSION['page']; ?>" type="hidden" name="page">
                            <button onclick="location.reload();" class='btn btn-sm btn-primary' type="button" >Retry <i class="fas fa-sync"></i></button>&nbsp;
                            <button class='btn btn-sm btn-warning' type="submit" >View Older Asset Information <i class="fas fa-arrow-right"></i></button>  
                        </form>
                    <center>
                </div>
                <?php
                break;
                }
                sleep(3);
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