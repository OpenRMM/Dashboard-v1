<?php
	include("config.php");
	$siteSettings = json_decode($siteSettingsJson, true);
	require('phpMQTT.php');
	//The max amount of entries for user activity, lowering the number deletes the old entries
	$userActivityLimit = 50;
	$excludedPages = "Asset_Portal,Service_Desk_New_Ticket,Service_Desk_Ticket,Service_Desk_Home,Init,Login,Logout,Asset_Alerts,Asset_Commands,Dashboard,Profile,Asset_Edit,Technicians,Customers,Assets,Versions"; 
	$allPages = "Asset_Portal,Service_Desk_New_Ticket,Service_Desk_Ticket,Service_Desk_Home,Asset_Agent_Settings,Asset_File_Manager,Init,Asset_Alerts,Customers,Technicians,Assets,Asset_Attached_Devices,Asset_Commands,Dashboard,Asset_Disks,Asset_Edit,Asset_Event_Logs,Asset_General,Login,Logout,Asset_Memory,Asset_Network,Asset_Optional_Features,Asset_Printers,Asset_Processes,Profile,Asset_Programs,Asset_Services,Asset_Users,Versions";
	$adminPages = "Asset_Agent_Settings,Technicians,Customers";
	$taskCondtion_max = 5;
###########################################################################################################################################
######################################################## DEV ONLY DO NOT PASS #############################################################
###########################################################################################################################################
	//Set Timezone
	date_default_timezone_set("America/Chicago");
	if($siteSettings==""){
		session_write_close();
		exit("There is a problem with you config.json file");
	}
	$serverPages = array("cron.php", "LoadHistorical.php");
	if(!isset($_SESSION['excludedPages'])){
		$_SESSION['excludedPages'] = explode(",", $excludedPages);
	}
	$allPages = explode(",", $allPages);
	$allAdminPages = explode(",", $adminPages);
	if(!in_array(basename($_SERVER['SCRIPT_NAME']), $serverPages)){
		ini_set('session.gc_maxlifetime', 3600);
		ini_set('display_errors', 0);
	
		$session_name = 'sec_session_id'; 
		$secure = false;
		$httponly = true;
		if (ini_set('session.use_only_cookies', 1) === FALSE) {
			exit("Could not initiate a safe session (ini_set)");
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
	function MQTTpublish($topic,$message,$computerID,$retain){
		global $MQTTserver, $MQTTport, $MQTTusername, $MQTTpassword, $mqttConnect;;
		$mqtt = new Bluerhinos\phpMQTT($MQTTserver, $MQTTport, $computerID);
		if ($mqtt->connect_auto(true, NULL, $MQTTusername, $MQTTpassword)) {
			$mqtt->publish($topic, $message, 1, $retain);
			$mqtt->close();
		} else {
			$mqttConnect="timeout";
			return "Time out!\n";
		}
	}
	$mqtt = new Bluerhinos\phpMQTT($MQTTserver, $MQTTport, $computerID);
	if ($mqtt->connect_auto(true, NULL, $MQTTusername, $MQTTpassword)) { }else{
		$mqttConnect="timeout";
	}

	//Connect to DB
	$db = mysqli_connect($siteSettings['MySQL']['host'], $siteSettings['MySQL']['username'], $siteSettings['MySQL']['password'], $siteSettings['MySQL']['database']);
	if(!$db and file_exists("config.php")){
		//exit("<center><h3 style='color:maroon;'>An error has occured. Please try again in a few moments.</h3><a href='#' onclick='location.reload();'>Retry</a><hr></center>");
	}
    if($createDatabase=="true"){
        $templine = '';
        $lines = file("databaseStructure.sql");
        foreach ($lines as $line)
        {
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;
            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';')
            {
                mysql_query($db, $templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error($db) . '<br /><br />');
                $templine = '';
            }
        }
    }

	//Get user data
	$query = "SELECT username,nicename,account_type,hex,user_color,allowed_pages,notifications FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$pages = crypto("decrypt",$user['allowed_pages'],$user['hex']);
	$allowed_pages = explode(",",$pages);
	$username=$user['username'];
	$_SESSION['notifications']= explode("||",$user['notifications']);

	//redirect standard users
	function checkAccess($page,$computerID="null"){
		GLOBAL $allAdminPages,$siteSettings, $db;
		$_SESSION['dbRows']=strtotime(date("Y-m-d H:i:s"));
		if($_SESSION['userid']==""){ 
			if($page!="Asset_Portal"){
				return exit("
				<script>		
					toastr.error('Session timed out.');
					setTimeout(function(){
						setCookie('section', btoa('Login'), 365);	
						window.location.replace('..//');
					}, 3000);		
				</script>
				<center><h5>Session timed out. You will be redirected to the login page in just a moment.</h5><br><h6>Redirecting</h6></center>");
			}
		}else{
			if($page=="Asset_Portal" and $_SESSION['userid']!=""){
				return exit("<center><br><br><h5>Sorry, you do not have permission to access this page!</h5><p>If you believe this is an error please contact a site administrator.</p><hr><a href='#' onclick='loadSection(\"Dashboard\");' style='background:#0c5460;color:".$siteSettings['theme']['Color 2']."' class='btn btn-sm'>Back To Dashboard</a></center><div style='height:100vh'>&nbsp;</div>");					
			}
			
			if($_SESSION['accountType']=="Standard" or $_SESSION['accountType']==""){
				if(!in_array($page, $allowed_pages) and $page != "Dashboard" and $page != "Init"){
					$activity="Technician Attempted To Access: ".str_replace("_"," ",$page);
					userActivity($activity,$_SESSION['userid']);
					return exit("<center><br><br><h5>Sorry, you do not have permission to access this page!</h5><p>If you believe this is an error please contact a site administrator.</p><hr><a href='#' onclick='loadSection(\"Dashboard\");' style='background:#0c5460;color:".$siteSettings['theme']['Color 2']."' class='btn btn-sm'>Back To Dashboard</a></center><div style='height:100vh'>&nbsp;</div>");					
				}
				if(in_array($page, $allAdminPages)){
					$activity="Technician Attempted To Access: ".str_replace("_"," ",$page);
					userActivity($activity,$_SESSION['userid']);
					return exit("<center><br><br><h5>Sorry, you do not have permission to access this page!</h5><p>If you believe this is an error please contact a site administrator.</p><hr><a href='#' onclick='loadSection(\"Dashboard\");' style='background:#0c5460;color:".$siteSettings['theme']['Color 2']."' class='btn btn-sm'>Back To Dashboard</a></center><div style='height:100vh'>&nbsp;</div>");					
				}
			}
			if(!in_array($page,$_SESSION['excludedPages'])){ 
				$query = "SELECT ID FROM computers WHERE ID='".$computerID."'";
				$results =  mysqli_num_rows(mysqli_query($db, $query));
				if($results<1){
					return exit("
					<br><center><h4>No Asset Selected</h4><p>To Select An Asset, Please Visit The <a class='text-dark' style='cursor:pointer' onclick='loadSection(\'Assets\');'><u>Assets page</u></a></p></center><hr>");
				}
			}
			
		}
	}
	if($siteSettings['theme']['MSP']=="true"){
		$msp="Customer";
	}else{
		$msp="Group";
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
	function getComputerData($ID, $fields = array("*")){
		global $db, $siteSettings;
		$retResult = array();
		

		$query4 = "SELECT name, data, last_update FROM computer_data WHERE computer_id='".$ID."' AND name LIKE 'screenshot_%'";
		$Counts = mysqli_num_rows(mysqli_query($db, $query4));
		for ($x = 0; $x <= $Counts; $x++) {
			array_push($fields,"screenshot_".$x);
		}
		$fields2 = implode("','",$fields);
		$query = "SELECT name, data, last_update FROM computer_data WHERE computer_id='".$ID."' AND name IN('".$fields2."') ORDER BY ID DESC";
		$results = mysqli_query($db, $query);
	
		while($row = mysqli_fetch_assoc($results)){
			//$row = mysqli_real_escape_string($db,$row);
			if(isset($retResult[$row['name']])){continue;}
			if(!in_array($row['name'], $fields)){
				continue;
			}
			if(strpos($row['name'], "screenshot_") !== false){
				$decoded =($row['data']);
				$retResult[$row['name']] = $decoded;
			}else{
				$decoded = jsonDecode(computerDecrypt($row['data']), true);
				$retResult[$row['name']] = $decoded['json'];
				$retResult[$row['name']."_raw"] = computerDecrypt($row['data']);
				$retResult[$row['name']."_error"] = $decoded['error'];
				$retResult[$row['name']."_lastUpdate"] = $row['last_update'];	
			}	
		}
		$getAlerts = getComputerAlerts($ID, $retResult);
		$retResult["Alerts"] = $getAlerts[0];
		$retResult["Alerts_raw"] = $getAlerts[1];

		return $retResult;	
	}

	//Alerts
	function getComputerAlerts($ID, $json){
        global $db, $siteSettings;
		$query = "SELECT * FROM computers WHERE ID='".$ID."'";
        $result = mysqli_query($db, $query);
        $computer = mysqli_fetch_assoc($result);

		$alertArray = array();
		$alertDelimited = "";

		$getWMI = array("general","logical_disk","bios","processor","agent","battery","windows_activation","agent_log","firewall","okla_speedtest");
		$query4 = "SELECT name, data, last_update FROM computer_data WHERE computer_id='".$ID."' AND name LIKE 'screenshot_%'";
		$Counts = mysqli_num_rows(mysqli_query($db, $query4));
		for ($x = 0; $x <= $Counts; $x++) {
			array_push($getWMI,"screenshot_".$x);
		}
		$getWMI2 = implode("','",$getWMI);
		$query2 = "SELECT name, data, last_update FROM computer_data WHERE computer_id='".$ID."' AND name IN('".$getWMI2."') ORDER BY ID DESC";
		$results2 = mysqli_query($db, $query2);
		while($row2 = mysqli_fetch_assoc($results2)){
			if(isset($retResult[$row2['name']])){continue;}
			if(!in_array($row2['name'], $getWMI)){
				continue;
			}		
			if(strpos($row2['name'], "screenshot_") !== false){
				$decoded =($row2['data']);
				$retResult[$row2['name']] = $decoded;
			}else{
				$decoded = jsonDecode(computerDecrypt($row2['data']), true);
				$retResult[$row2['name']] = $decoded['json'];
				$retResult[$row2['name']."_raw"] = computerDecrypt($row2['data']);
				$retResult[$row2['name']."_error"] = $decoded['error'];
				$retResult[$row2['name']."_lastUpdate"] = $row2['last_update'];	
			}			
		}
		$hostname = textOnNull($retResult['general']['Response'][0]['csname'],"Unavailable");

		if($computer['show_alerts']=="1"){ 
			$query = "SELECT * FROM alerts WHERE active='1' ORDER BY ID ASC";
			$results = mysqli_query($db, $query);
			$resultCount = mysqli_num_rows($results);	
			while($result = mysqli_fetch_assoc($results)){
				if($result['computer_id']==$computer['ID'] or $result['company_id']==$computer['company_id']){
				
				}else{
					if($result['computer_id']!="0" and $result['company_id']!="0"){
						continue;
					}
				}
				
				$details=jsonDecode($result['details'],true)['json'];
				$show="false";
				switch ($details['Details']['Condition']) {
					case "Total Alert Count":
					$currentValue="0";
					break;
					case "Total Ram/Memory":
					$currentValue=formatBytes($retResult['general']['Response'][0]['Totalphysicalmemory'],0);
					break;
					case "Available Disk Space":
						$currentValue=formatBytes($retResult['logical_disk']['Response']['C:']['FreeSpace']);
					break;
					case "Total Disk Space":
						$currentValue=formatBytes($retResult['logical_disk']['Response']['C:']['Size']);
					break;
					case "Domain":
						$currentValue=$retResult['general']['Response'][0]['Domain'];
					break;
					case "Public IP Address":
						$currentValue=$retResult['general']['Response'][0]['ExternalIP']["ip"];
					break;
					case "Antivirus":
						$currentValue=$retResult['general']['Response'][0]['Antivirus'];
					break;
					case "Agent Version":
						$currentValue=$retResult['agent']['Response'][0]['Version'];
					break;
					case "Total User Accounts":
						$currentValue="0";
					break;
					case "Command Received":
						$currentValue="0";
					break;
					case "Agent Comes Online":
						if($computer['online']=="0")$status="Offline";
						if($computer['online']=="1")$status="Online";
						$currentValue=$status;
					break;
					case "Agent Goes Offline":
						if($computer['online']=="0")$status="Offline";
						if($computer['online']=="1")$status="Online";
						$currentValue=$status;
					break;
					case "Windows Activation":
						$status = $retResult['windows_activation']['Response'][0]['LicenseStatus'];
						if($status!="Licensed")$status="Not activated";
						$currentValue=$status;
					break;
					case "Local IP Address":
						$currentValue=$retResult['general']['Response'][0]['PrimaryLocalIP'];
					break;
					case "Last Update":
						$currentValue=$retResult['Ping'];
					break;

					default:
					$currentValue="unknown";
				}
				switch ($details['Details']['Comparison']) {
					case "=":
						if($currentValue == $details['Details']['Value']){
							$show='true';	
						}
					break;
					case "!=":
						if($currentValue != $details['Details']['Value']){
							$show='true';	
						}
					break;
					case ">":
						if($currentValue > $details['Details']['Value']){
							$show='true';	
						}
					break;
					case "<":
						if($currentValue < $details['Details']['Value']){
							$show='true';	
						}
					break;
					case ">=":
						if($currentValue >= $details['Details']['Value']){
							$show='true';	
						}
					break;
					case "<=":
						if($currentValue <= $details['Details']['Value']){
							$show='true';	
						}
					break;
					case "contain":
						if (strpos($details['Details']['Value'], $currentValue) !== false) {
							$show='true';	
						}
					break;
					case "notcontain":
						if (strpos($details['Details']['Value'], $currentValue) !== false) { }else{
							$show='true';	
						} 
					break;

				}
				if($show=="true"){
				
					$alertName = $details['Name'];
					$newAlert = array(
						"subject"=> $details['Name']."<br>",
						"message"=>"If ".$details['Details']['Condition']." ".$details['Details']['Comparison']." ".$details['Details']['Value']."<br>Current Value: ".$currentValue,
						"type"=>"danger",
						"hostname"=>$hostname,
						"alertName"=>$alertName
					);
					
					array_push($alertArray,$newAlert);
					$alertDelimited .= implode("|", $newAlert).",";
					
				}
				
			}
		}
	
		//Disk Space
		$disks = $json['logical_disk'];
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
		$string =  htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$string = stripslashes(htmlentities(strip_tags($string)));
		//$string = filter_input($string, FILTER_SANITIZE_STRING);
		return $string;
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
	$salt = base64_decode($siteSettings['Encryption']['salt']);
	function crypto($action, $string, $salt) {
		$output = false;
		global $siteSettings;
		$encrypt_method = "AES-256-CBC";
		$secret_key = base64_decode($siteSettings['Encryption']['secret']);
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
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*()-';
		$randomString = '';
		for ($i = 0; $i < $n; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}
		return $randomString;
	}
	function formatBytes($bytes, $precision = 0) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision);
	} 
	//Custom JsonDecode with error handling
	function jsonDecode($json, $assoc = false) {
		for ($i = 0; $i <= 31; ++$i) { 
			$json = str_replace(chr($i), "", $json); 
		}
		$json = str_replace(chr(127), "", $json);
		if (0 === strpos(bin2hex($json), 'efbbbf')) {
		   $json = substr($json, 3);
		}
		$json = str_replace("\\", "\\\\", $json);
		$json = stripslashes($json);

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
	
	function welcome(){
		if(date("H") < 12){
			return "Good Morning";
		}elseif(date("H") > 11 && date("H") < 18){
			return "Good Afternoon";
		}elseif(date("H") > 17){
			return "Good Evening";
		}
	}
	//save notifications
	function saveNotification($notification){
		GLOBAL $db;
		$current = explode("||", $_SESSION['notifications']);
		array_push($current,$notification);
		$new = implode("||",$current);
		$query = "UPDATE users SET notifications='".$new."' WHERE ID='".$_SESSION['userid']."';";
		$results = mysqli_query($db, $query);		
		$_SESSION['notifications']= explode("||", $new);
	}

	//log user activity	
	function userActivity($activity2,$IDuser){
		global $db, $siteSettings, $userActivityLimit;	
		$salt=getSalt(40);
		$query = "SELECT * FROM users WHERE ID='".$IDuser."' LIMIT 1";
		$users = mysqli_query($db, $query);
		$user = mysqli_fetch_assoc($users);
		if (strpos(clean($activity2), 'Admin') !== false) {
			$type=" for ";
		}else{
			$type=" by ";
		}
		$activity=clean($activity2).$type.ucwords(crypto('decrypt',$user['nicename'],$user['hex']));

		$query = "SELECT * FROM user_activity WHERE user_id='".$user['ID']."' ORDER BY ID DESC LIMIT 1";
		$count =  mysqli_fetch_assoc(mysqli_query($db, $query));
		if(crypto('decrypt',$count['activity'],$count['hex'])!=$activity){
			$active = crypto('encrypt',$activity,$salt);

			$query = "INSERT INTO user_activity (user_id, activity,date,hex) VALUES ('".$user['ID']."','".$active."', '".time()."','".$salt."')";
			$results = mysqli_query($db, $query);
		}

	}

		function computerEncrypt(array $data): string
		{
			GLOBAL $passphrase;
			$data_json_64 = base64_encode(json_encode($data));
			$secret_key = hex2bin($siteSettings['agentEncryption']['secret']);
			$iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));
			$tag = '';
			$encrypted_64 = openssl_encrypt($data_json_64, 'aes-256-gcm', $secret_key, 0, $iv, $tag);
			$json = new stdClass();
			$json->iv = base64_encode($iv);
			$json->data = $encrypted_64;
			$json->tag = base64_encode($tag);
			return base64_encode(json_encode($json));
		}
		function computerDecrypt($data)
		{
			GLOBAL $siteSettings;
			$secret_key = hex2bin($siteSettings['agentEncryption']['secret']);
			$json = json_decode(base64_decode($data), true);
			$iv = base64_decode($json['iv']);
			$tag = base64_decode($json['tag']);
			$encrypted_data = base64_decode($json['data']);
			$decrypted_data = openssl_decrypt($encrypted_data, 'aes-256-gcm', $secret_key, OPENSSL_RAW_DATA, $iv, $tag);
			return json_decode(base64_decode($decrypted_data),True);	
		}
	
?>