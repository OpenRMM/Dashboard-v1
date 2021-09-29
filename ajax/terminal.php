<?php
include("../Includes/db.php");
$ID = (int)$_POST["id"];
$commands = explode(",", $_POST['command']);

//$args = $_POST['args'];
$expire_after = 5;
$exists = 0;

$query = "SELECT hostname FROM computerdata WHERE ID='".$ID."'";
$results = mysqli_query($db, $query);
$computer = mysqli_fetch_assoc($results);
									
$query = "SELECT ID, expire_time FROM commands WHERE ComputerID='".$computer['hostname']."' AND status='Sent' AND command='".$commands[0]."' AND userid='".$_SESSION['userid']."' ORDER BY ID DESC LIMIT 1";
$results = mysqli_query($db, $query);
$existing = mysqli_fetch_assoc($results);

if($existing['ID'] != ""){
	if(strtotime(date("m/d/Y H:i:s")) <= strtotime($existing['expire_time'])){
		$exists = 1;
	}
}

//generate expire time
$expire_time = date("m/d/Y H:i:s", strtotime('+'.$expire_after.' minutes', strtotime(date("m/d/y H:i:s"))));

if($exists == 0){
	$query = "INSERT INTO commands (ComputerID, userid, command, arg, expire_after, expire_time, status)
			  VALUES ('".$computer['hostname']."', '".$_SESSION['userid']."', '".$commands[0]."', '".$commands[1]."', '".$expire_after."', '".$expire_time."', 'Sent')";
	$results = mysqli_query($db, $query);
	$insertID = mysqli_insert_id($db);
	
	$activity="Technician Sent ".$commands[0]." ".$commands[1]." Command To: ".$computer['hostname'];
	userActivity($activity);
	
	//Get Response
	$count = 0;
	while($count <= 6){
		$query = "SELECT data_received FROM commands WHERE ID = '".$insertID."';";
		$results = mysqli_query($db, $query);
		$result = mysqli_fetch_assoc($results);
		if(trim($result["data_received"])!=""){break;}
		sleep(2);
		$count++;
	}
	
	if(trim($result["data_received"])!=""){
		$response = trim($result["data_received"]);
	}else{
		if($count >= 6){
			$response = "Timeout";
		}else{
			$response = "No Response";
		}
	}
?>
	<pre style="color:#fff;"><?php echo $response;?></pre>
<?php }else{?>
	Computer not found or command already sent
<?php }?>