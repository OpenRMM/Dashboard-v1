<?php
	///usr/local/bin/php /home/mjyoa30cqo3p/public_html/rmm.smgunlimited.com/rmm/Includes/cronJobs/alerts.php >/dev/null 2>&1

	$debug = false;
	
	if(php_sapi_name() != 'cli' && $debug == false){
		exit("<center><h2>This script cannot be ran via the browser, if you wish to test this, please change the debug variable in cron.php</h2></center>");
	}
	
	include("../db.php");
	
	$alertSettingsPerUser = array();
	$alertsToSendPerUser = array();
	
	$date = date("m/d/Y h:i:s");
	$query = "UPDATE general SET last_cron='".$date."' WHERE ID='1'";
	$results = mysqli_query($db, $query);
	
	//Get email list
	$query = "SELECT nicename, email, alert_settings, hex FROM users WHERE active='1'";
	$results = mysqli_query($db, $query);
	while($email = mysqli_fetch_assoc($results)){
		$emailAddress = crypto("decrypt", $email['email'], $email['hex']);
		$alertSettingsPerUser[$emailAddress] = array("nicename"=>$email['nicename'], "alert_settings"=>$email['alert_settings']);
	}
	
	//Loop Trough PCs
	$query = "SELECT * FROM computerdata WHERE active='1'  ORDER BY hostname ASC";
	$results = mysqli_query($db, $query);
	while($result = mysqli_fetch_assoc($results)){
		$data = getComputerData($result['hostname']);
		$alerts = $data['Alerts'];
		
		//loop though alerts
		foreach($alerts as $alert){		
			//Loop trough users and their settings
			foreach($alertSettingsPerUser as $email=>$data){			
				//Check if user wants this alert sent
				if(strpos(strtolower($data['alert_settings']), strtolower($alert['alertName']).":1") !== false){
					$nicename = $data['Nicename'];
					$alertsToSendPerUser[$email][] = $alert;
					
					//Debug
					if($debug){
						echo "DEBUG: Adding an alert to: ".$email." with the message: ".$alert['hostname'].": ".$alert['message']."<br/>";
					}
				} //end if
			}//end foreach
		} //end foreach
	}//end while
	

	//Now that all alerts are gathered and aggrigated to send to each user, send the alerts (1 email per user)
	foreach($alertSettingsPerUser as $email=>$data){
		$alertsPerHostname = array();
		$alertsToSend = $alertsToSendPerUser[$email];
		
		//Check to see if the alerts avaliable are what the user wants
		if(count($alertsToSend) == 0){ continue; }
		
		//Debug
		if($debug){
			echo "<h1>DEBUG: Sending the following alerts to ".$data['nicename']."</h1>";
		}
		
		//Combine alerts per hostname
		foreach($alertsToSend as $alert){
			$alertsPerHostname[$alert['hostname']][] = $alert;
		}
		
		//Alerts Per Hostname
		$html = "";
		foreach($alertsPerHostname as $hostname=>$alerts){
			$html .= "<h5 style='margin:0px;'><a href='#'><b>".$hostname."</b>:</a></h5>";
			$html .= "<ul style='padding:5px;'>";
			foreach($alerts as $alert){
				$alertColors = array("warning"=>"#d4af37", "danger"=>"red");
				$html .= "<li style='margin:5px;'> <span style='color:".$alertColors[$alert['type']]."'><b>".$alert['subject']."</b></span> - ".$alert['message']."</li>";
			}
			$html .= "</ul>";
		}
		
		//Configure Email Template
		$title = "SMG Remote Management And Montitoring";
		$emailTemplate = str_replace(
			array("[your-name]", "[your-message]", "[your-title]"), 
			array(explode(" ", $data['nicename'])[0], $html, $title), 
			file_get_contents("../htmlemail.html")
		);

		//Send HTML Email
		if(trim($email) != "" && trim($emailTemplate)!=""){
			$headers = "MIME-Version: 1.0"."\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
			$headers .= 'From: SMG RMM<no-reply@smgunlimited.com>'."\r\n";
			mail($email, "You Have New Alerts", $emailTemplate, $headers);
		}
		
		//Show what is gonna be sent via email, Debug
		if($debug){
			echo $emailTemplate;
			echo "<br/><br/><br/><br/><br/>";
		}
	} //end foreach usersettings