<?php
	//This runs once/day
	///usr/local/bin/php /home/mjyoa30cqo3p/public_html/rmm.smgunlimited.com/rmm/Includes/cronJobs/cleanup.php >/dev/null 2>&1

	$debug = false;
	
	if(php_sapi_name() != 'cli' && $debug == false){
		exit("<center><h2>This script cannot be ran via the browser, if you wish to test this, please change the debug variable in cleanup.php</h2></center>");
	}
	
	include("../db.php");
	
	//Delete all recieved commands
	$query = "DELETE FROM commands WHERE status <> 'Sent'";
	mysqli_query($db, $query);
	
	//Delete WMI data older than 15 days
	$daysToKeep = $siteSettings['Max_History_Days'];
	$date = date("m/d/Y", strtotime("-".$daysToKeep." days"));
	$query = "SELECT ID, last_update FROM wmidata ORDER BY ID ASC";
	$results = mysqli_query($db, $query);
	
	$query = "DELETE FROM wmidata WHERE ";
	$count = 0;
	while($result = mysqli_fetch_assoc($results)){
		$count++;
		
		if($date > strtotime($result['last_update'])){
			if($debug){ echo "Deleting: ".$result['ID']."<br/>";}
			$query .= "ID='".$result['ID']."' OR ";
		}		
	}
	if($count > 0){
		mysqli_query($db, rtrim($query, "OR"));
	}
	
	$query = "DELETE FROM wmidata WHERE WMI_Data = '}'";
	mysqli_query($db, $query);
	
	