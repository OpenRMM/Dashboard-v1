<?php require("db.php"); 
$ID = base64_decode($_GET['ID']);
if($_SESSION['userid']!=""){
?>

<div id="plist" style="background:#343a40;color:#fff;height:120%;overflow:hidden" class="people-list">

    <ul class="list-unstyled chat-list mt-2 mb-0">
    <?php
    //Fetch Results
    $count=0;
    $query = "SELECT * FROM computers WHERE active='1' ORDER BY ID ASC";
    $results = mysqli_query($db, $query);
    while($activity = mysqli_fetch_assoc($results)){
        $query2 = "SELECT * FROM asset_messages WHERE computer_id='".$activity['ID']."' and chat_viewed='0' and userid='0' ORDER BY ID ASC";
        $results2 = mysqli_query($db, $query2);
        $Count = mysqli_num_rows($results2);
        $json = getComputerData($activity['ID'], array("general"));
        $date = strtotime($json['general_lastUpdate']);
        if($date < strtotime('-1 days')) {
            $activity['online']="0";
        }
        if($activity['online']=="1"){
            $online="online";
        }else{
            $online="offline";
        }
        if($Count==0){
            //continue;
        }
       
        $hostname = textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
        if($computerID==$activity['ID']){
            $active="secActive";
        }else{
            $active="";	
        }
        $count++;																
    ?>
        <li id="side<?php echo $activity['ID']; ?>" onclick="loadChat('<?php echo $activity['ID']; ?>');" class="sideDiv secbtn clearfix <?php echo $active; ?>">
            <i style="float:left;font-size:24px;color:#696969;margin-top:7px" class="fas fa-desktop"></i>
            <div class="about">
                <div class="name"><?php echo $hostname; ?>
                <?php if($Count>0){ ?>
                    <p style="font-size:10px;float:right;display:inline;margin-left:10px;margin-top:2px;" id="messageCount" class="text-white badge bg-c-pink"><?php echo $Count; ?></p>
                <?php } ?>
                </div> 
                <div class="status"> <i class="fa fa-circle <?php echo $online; ?>"></i> &nbsp; <?php echo ucwords($online); ?></div>                                            
            </div>
        </li>
    <?php } ?>

    </ul>
</div>
<div class="chat">

    <div style="height:300px;overflow-y:auto;" id="chatDiv2" class="chat-history">
        <ul class="m-b-0 chatList">
            <?php
                //Fetch Results
                $count4=0;
                $query = "SELECT * FROM asset_messages WHERE computer_id='".$ID."' ORDER BY ID ASC";
                $results = mysqli_query($db, $query);
                while($activity = mysqli_fetch_assoc($results)){
                    $count4++;
                    if($activity['is_typing']=="0" and $activity['userid']=="0"){
                        $_SESSION['typingmsg']="";
                    }
                    if($activity['is_typing']=="1" and $activity['userid']=="0"){
                        $_SESSION['typingmsg']="<span class='text-right'>Guest is typing</span><br><div style='margin-top:-40px;height:5px;width:150px' class='spinner-grow text-muted'></div>";
                    }
                   
                    if($activity['userid']!="0"){
                        $class1=" text-left";
                        $query2 = "SELECT * FROM users WHERE ID='".$activity['userid']."' ORDER BY ID ASC";
                        $results2 = mysqli_fetch_assoc(mysqli_query($db, $query2));
                        $name="From ".ucwords(crypto("decrypt",$results2['nicename'],$results2['hex']))." - ";
                        $class2="other-message float-right bg-primary text-white";
                    }else{
                        $name="";
                        $class1="text-right";
                        $class2="my-message bg-dark";
                    }
            ?>
                <li title="<?php echo $name.ago($activity['time']); ?>" class="clearfix">
                    <div style="text-align:center;min-width:100px;font-size:14px;padding:5px" class="message <?php echo $class2; ?>"> 
                        <?php echo $activity['message']; ?>
                    
                    </div>           
                </li>
            <?php } ?>
             <?php echo $_SESSION['typingmsg']; ?>
            </ul>
           
            <?php  
            if($count4==0){ 
            ?>
                <br>
                <center>
                    <h6>
                     Select an asset to view chat history
                      
                    </h6>
                </center>
                <div style="height:200px"></div>
            <?php } ?>
        
    </div>


<?php }else{ ?>

    <ul class="m-b-0 chatList">
        <?php
            //Fetch Results
            $count4=0;
            $query = "SELECT * FROM asset_messages WHERE computer_id='".$ID."' ORDER BY ID ASC";
            $results = mysqli_query($db, $query);
            while($activity = mysqli_fetch_assoc($results)){
                $count4++;
                if($activity['is_typing']=="0" and $activity['userid']!="0"){
                    $_SESSION['typingmsg']="";
                }
                if($activity['is_typing']=="1" and $activity['userid']!="0"){
                    $_SESSION['typingmsg']="<span class='text-right'>Technician is typing</span><br><div style='margin-top:-40px;height:5px;width:150px' class='spinner-grow text-muted'></div>";
                }
              
                
                if($activity['userid']=="0"){
                    $class1=" text-left";  
                    $name="";             
                    $class2="other-message float-right bg-primary text-white";
                }else{
                    
                    $class1="text-right";
                    $query2 = "SELECT * FROM users WHERE ID='".$activity['userid']."' ORDER BY ID ASC";
                    $results2 = mysqli_fetch_assoc(mysqli_query($db, $query2));
                    $name="From ".ucwords(crypto("decrypt",$results2['nicename'],$results2['hex']))." - ";
                    $class2="my-message bg-dark";
                }
        ?>
            <li title="<?php echo $name.ago($activity['time']); ?>" class="clearfix">
                <div style="text-align:center;min-width:100px;font-size:14px;padding:5px" class="message <?php echo $class2; ?>"> 
                    <?php echo $activity['message']; ?>
                
                </div>           
            </li>
        <?php } ?>
        <?php echo $_SESSION['typingmsg']; ?>
    </ul>

<?php } ?>