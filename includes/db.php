<?php
	include("config.php");
	$siteSettings = json_decode($siteSettingsJson, true);
	require('phpMQTT.php');
	//The max amount of entries for user activity, lowering the number deletes the old entries
	$userActivityLimit = 50;
	$excludedPages = "Init,Login,Logout,EventLogs,Alerts,Commands,Dashboard,SiteSettings,Profile,Edit,AllUsers,AllCompanies,Assets,NewComputers,Versions"; 
	$allPages = "Init,Alerts,AllCompanies,AllUsers,Assets,AttachedDevices,Commands,Dashboard,DefaultPrograms,Disks,Edit,EventLogs,General,Login,Logout,Memory,Network,NewComputers,OptionalFeatures,Printers,Proccesses,Profile,Programs,Services,Users,Versions";
	$adminPages = "AllUsers.php,AllCompanies.php,SiteSettings.php";
###########################################################################################################################################
######################################################## DEV ONLY DO NOT PASS #############################################################
###########################################################################################################################################
	//Set Timezone
	date_default_timezone_set("America/Chicago");
	$serverPages = array("cron.php", "LoadHistorical.php");
	$_SESSION['excludedPages'] = explode(",", $excludedPages);
	$allPages = explode(",", $allPages);
	$adminPages = explode(",", $allAdminPages);
	if(!in_array(basename($_SERVER['SCRIPT_NAME']), $serverPages)){
		ini_set('session.gc_maxlifetime', 3600);
		ini_set('display_errors', 0);
		$session_name = 'sec_session_id'; 
		$secure = false;
		$httponly = true;
		if (ini_set('session.use_only_cookies', 1) === FALSE) {
			header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
			exit();
		}
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params($cookieParams["lifetime"],
			$cookieParams["path"], 
			$cookieParams["domain"], 
			$secure,
			$httponly);
		session_name($session_name);
		session_start(); 
	}

	//Connect to MQTT
	$MQTTserver = $siteSettings['MQTT']['host'];
	$MQTTport = $siteSettings['MQTT']['port'];
	$MQTTusername = $siteSettings['MQTT']['username']; 
	$MQTTpassword = $siteSettings['MQTT']['password']; 
	//MQTT Subscribe
	function MQTTpublish($topic,$message,$computerID){
		global $MQTTserver, $MQTTport, $MQTTusername, $MQTTpassword, $mqttConnect;;
		$mqtt = new Bluerhinos\phpMQTT($MQTTserver, $MQTTport, $computerID);
		if ($mqtt->connect(true, NULL, $MQTTusername, $MQTTpassword)) {
			$mqtt->publish($topic, $message, 0, false);
			$mqtt->close();
		} else {
			$mqttConnect="timeout";
			return "Time out!\n";
		}
	}
	$mqtt = new Bluerhinos\phpMQTT($MQTTserver, $MQTTport, $computerID);
	if ($mqtt->connect(true, NULL, $MQTTusername, $MQTTpassword)) { }else{
		$mqttConnect="timeout";
	}

	//Connect to DB
	$db = mysqli_connect($siteSettings['MySQL']['host'], $siteSettings['MySQL']['username'], $siteSettings['MySQL']['password'], $siteSettings['MySQL']['database']);
	if(!$db and file_exists("config.php")){
		//exit("<center><h3 style='color:maroon;'>An error has occured. Please try again in a few moments.</h3><a href='#' onclick='location.reload();'>Retry</a><hr></center>");
	}
    if($createDatabase=="true"){
        //create db strucrure
        $templine = '';
        $lines = file("databaseStructure.sql");
        foreach ($lines as $line)
        {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';')
            {
                mysql_query($db, $templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error($db) . '<br /><br />');y
                $templine = '';
            }
        }
    }
	//redirect standard users
	if($_SESSION['accountType']=="Standard"){
		if(in_array(basename($_SERVER['SCRIPT_NAME']), $allAdminPages)){
			$activity="Technician Attempted Access To: ".basename($_SERVER['SCRIPT_NAME']);
			userActivity($activity);
			exit("<center><br><br><h4>Sorry, You Do Not Have Permission To Access This Page!</h4><p>If you believe this is an error please contact a site administrator.</p><hr><a href='#' onclick='loadSection(\"Dashboard\");' class='btn btn-warning btn-sm'>Back To Dashboard</a></center><div style='height:100vh'>&nbsp;</div>");	
		}
	}
	//Load general settings from DB
	function loadGeneralFromDB(){
		global $db;
		$query = "SELECT * FROM general WHERE ID='1' LIMIT 1";
		$results = mysqli_query($db, $query);
		$general = mysqli_fetch_assoc($results);
		return $general;
	}
	$siteSettings['general'] = loadGeneralFromDB();
	//Function to aggrigate data from pc
	function getComputerData($ID, $fields = array("*"), $date = "latest"){
		global $db, $siteSettings;
		$retResult = array();
		if(in_array("*", $fields)){
			$query = "SELECT WMI_Name, WMI_Data, last_update FROM wmidata WHERE ComputerID='".$ID."'";
		}else{
			$query = "SELECT WMI_Name, WMI_Data, last_update FROM wmidata WHERE (";
			//Only get wanted fields
			foreach($fields as $field){
				$query .= " WMI_Name = '".$field."' OR"; 
			}
			$query = trim($query, "OR");
			$query .= ") AND ComputerID='".$ID."'";
		}
		
		//DateTime 
		if($date != "latest"){
			$query .= " AND last_update LIKE '".clean($date)."%'";
		}
		$query .= " ORDER BY ID DESC";
		$results = mysqli_query($db, $query);
		while($row = mysqli_fetch_assoc($results)){
		
			if(isset($retResult[$row['WMI_Name']])){continue;}
			if($row['WMI_Name']!="Ping"){
				$decoded = jsonDecode($row['WMI_Data'], true);
				$retResult[$row['WMI_Name']] = $decoded['json'];
				$retResult[$row['WMI_Name']."_raw"] = $row['WMI_Data'];
				$retResult[$row['WMI_Name']."_error"] = $decoded['error'];
			}else{
				$retResult[$row['WMI_Name']] = $row['WMI_Data'];
			}
			$retResult[$row['WMI_Name']."_lastUpdate"] = $row['last_update'];
		}
   
		$getAlerts = getComputerAlerts($ID, $retResult);
		$retResult["Alerts"] = $getAlerts[0];
		$retResult["Alerts_raw"] = $getAlerts[1];
		return $retResult;
	}
	
	//Alerts
	function getComputerAlerts($ID, $json){
        global $db, $siteSettings;

        $query = "SELECT * FROM computerdata WHERE ID='".$ID."'";
        $result = mysqli_query($db, $query);
        $computer = mysqli_fetch_assoc($result);
        $hostname = $computer['hostname'].": ";

		$alertArray = array();
		$alertDelimited = "";
		//Memory
		//Total
		$totalMemory = round((int)$json['WMI_ComputerSystem'][0]['TotalPhysicalMemory'] /1024 /1024 /1024,1); //GB
		if($totalMemory < $siteSettings['Alert Settings']['Memory']['Total']['Danger']){
			$alertName = "memory_total_danger";
			$newAlert = array(
				"subject"=>"Memory",
				"message"=>"Total memory is real low (Current: ".$totalMemory." GB)",
				"type"=>"danger",
				"hostname"=>$hostname,
				"alertName"=>$alertName
			);
			$alertArray[] = $newAlert;
			$alertDelimited .= implode("|", $newAlert).",";
		}elseif($totalMemory < $siteSettings['Alert Settings']['Memory']['Total']['Warning']){
			$alertName = "memory_total_warning";
			$newAlert = array("subject"=>"Memory",
				"message"=>"Total memory is getting low (Current: ".$totalMemory." GB)",
				"type"=>"warning",
				"hostname"=>$hostname,
				"alertName"=>$alertName
			);
			$alertArray[] = $newAlert;
			$alertDelimited .= implode("|", $newAlert).",";
		}
		//Free
		$freeMemory = round($json['WMI_OS'][0]['FreePhysicalMemory'] / 1024,1); //MB
		if($freeMemory < $siteSettings['Alert Settings']['Memory']['Free']['Danger']){
			$alertName = "memory_free_danger";
			$newAlert = array(
				"subject"=>"Memory",
				"message"=>"Free memory is real low (Current: ".$freeMemory." MB)",
				"type"=>"danger",
				"hostname"=>$hostname,
				"alertName"=>$alertName
			);
			$alertArray[] = $newAlert;
			$alertDelimited .= implode("|", $newAlert).",";
		}elseif($freeMemory < $siteSettings['Alert Settings']['Memory']['Free']['Warning']){
			$alertName = "memory_free_warning";
			$newAlert = array(
				"subject"=>"Memory",
				"message"=>"Free memory is getting low (Current: ".$freeMemory." MB)",
				"type"=>"warning",
				"hostname"=>$hostname,
				"alertName"=>$alertName
			);
			$alertArray[] = $newAlert;
			$alertDelimited .= implode("|", $newAlert).",";
		}
		//Disk Space
		$disks = $json['WMI_LogicalDisk'];
		foreach($disks as $disk){
			$freeSpace = $disk['FreeSpace'];
			$size = $disk['Size'];
			$used = $size - $freeSpace ;
			$usedPct = round(($used/$size) * 100);
			if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger']){
				$alertName = "disk_warning";
				$newAlert = array(
					"subject"=>"Disk",
					"message"=>$disk['Caption']." is real low on space (".(100-$usedPct)." GB free)",
					"type"=>"danger",
					"hostname"=>$hostname,
					"alertName"=>$alertName
				);
				$alertArray[] = $newAlert;
				$alertDelimited .= implode("|", $newAlert).",";
			}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
				$alertName = "disk_danger";
				$newAlert = array(
					"subject"=>"Disk",
					"message"=>$disk['Caption']." is getting low on space (".(100-$usedPct)." GB free)",
					"type"=>"warning",
					"hostname"=>$hostname,
					"alertName"=>$alertName
				);
				$alertArray[] = $newAlert;
				$alertDelimited .= implode("|", $newAlert).",";
			}
		}
		
		//Check agent version
		if($siteSettings['general']['agent_latest_version'] != $json['AgentVersion']['Value']){
			$alertName = "agent_version";
			$newAlert = array(
				"subject"=>"Agent Version",
				"message"=>"Agent is out of date. Currently installed: ".textOnNull($json['AgentVersion']['Value'], "Unknown"),
				"type"=>"warning",
				"hostname"=>$hostname,
				"alertName"=>$alertName
			);
			$alertArray[] = $newAlert;
			$alertDelimited .= implode("|", $newAlert).",";
		}
		
		//Windows Activation
		if($json['WindowsActivation']['Value'] != "Activated"){
			$alertName = "windows_activation";
			$newAlert = array(
				"subject"=>"Windows Activation",
				"message"=>"Not Activated",
				"type"=>"warning",
				"hostname"=>$hostname,
				"alertName"=>$alertName
			);
			$alertArray[] = $newAlert;
			$alertDelimited .= implode("|", $newAlert).",";
		}
		
		return array($alertArray, trim($alertDelimited, ","));
	}
	
	//Fix Empty Text
	function textOnNull($text, $onnull=""){
		return (trim($text)=="" ? $onnull : $text);
	}
	
	//For DB use
	function clean($string) {
		$remove = array("'");
		$replaceWith = array("");
		return strip_tags(str_replace($remove, $replaceWith, $string));
	}
	
	//Clean Phone
	function phone($number) {
		if(ctype_digit($number) && strlen($number) == 10) {
		$number = "(".substr($number, 0, 3) .') '. substr($number, 3, 3) .'-'. substr($number, 6);
		} else {
			if(ctype_digit($number) && strlen($number) == 7) {
				$number = substr($number, 0, 3) .'-'. substr($number, 3, 4);
			}
		}
		return $number;
	}
	
	//Time Ago
	function ago($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
	
	//Encrypt And Decrypt With Salt
	$salt = base64_decode("R1pxNEU1aXBBc21rWW5GQ3dWVjdrQ1F4cUVabGppTk9aWXEzdE1ZRQ==");
	function crypto($action, $string, $salt) {
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$secret_key = base64_decode('JE1HX1VuMWltSXQzZCE=');
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $salt), 0, 16);
		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else if( $action == 'decrypt' ) {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
		return $output;
	} 
	
	//Get Random Salt
	function getSalt($n=40) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $n; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}
		return $randomString;
	}
	//Custom JsonDecode with error handling
	function jsonDecode($json, $assoc = false) {
		$json = str_replace("\\", "\\\\", $json);
		$ret = json_decode(utf8_encode($json), $assoc);
		if ($error = json_last_error()){
			$errorReference = [
				JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
				JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
				JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
				JSON_ERROR_SYNTAX => 'Syntax error.',
				JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
				JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
				JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
				JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.'
			];
			$err = isset($errorReference[$error]) ? $errorReference[$error] : "Unknown error ($error)";
		}
		return array("json"=>$ret, "error"=>$err);
	}  
	
	//log user activity	
	function userActivity($activity2,$IDuser){
		global $db, $siteSettings, $userActivityLimit;	
		$query = "SELECT * FROM users WHERE ID='".$IDuser."' LIMIT 1";
		$users = mysqli_query($db, $query);
		$user = mysqli_fetch_assoc($users);
		$activity=clean($activity2);
		if($user['userActivity']==""){
			$active = $activity."@".time();
		}else{
			$activeFix = clean(explode("|",$user['userActivity']));
			$fix = end($activeFix);
			$activeFix2 = explode("@",$fix);
			if($activeFix2[0]==clean($activity)){
				$active= $user['userActivity'];				
			}else{
				$activeFix = explode("|",$user['userActivity']."|".$activity."@".time());
				$active = implode("|",array_slice($activeFix,0,$userActivityLimit));
			}
		}
		$query = "UPDATE users SET userActivity='".$active."' WHERE ID=".$IDuser.";";
		$results = mysqli_query($db, $query);	
	}

?>