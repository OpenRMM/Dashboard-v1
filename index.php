<?php
	require("Includes/db.php");
	require('Includes/phpMQTT.php');

	$search = strip_tags(urldecode($_GET['search']));
	
	$messageTitle = "New Ideas/Bug Fixes";
	$messageText .= "Add Site Alert For Conflicting Hostnames. <br><br> Version 1.0.1.6, updates are broke";
	
	$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];
	
	$MQTTclient_id = $username; // make sure this is unique for connecting to sever - you could use uniqid()

	function mQTTpublish($topic,$message){
		global $MQTTserver, $MQTTport, $MQTTclient_id, $MQTTusername, $MQTTpassword;
		$mqtt = new Bluerhinos\phpMQTT($MQTTserver, $MQTTport, $MQTTclient_id);
		if ($mqtt->connect(true, NULL, $MQTTusername, $MQTTpassword)) {
			$mqtt->publish($topic, $message, 0, false);
			$mqtt->close();
		} else {
			return "Time out!\n";
		}
	}

	if(isset($_POST)){
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		//Edit Computer (Edit.php)
		if($_POST['type'] == "EditComputer"){
			$ID = (int)$_POST['ID'];
			$name = clean($_POST['name']);
			$comment = clean($_POST['comment']);
			$phone = clean($_POST['phone']);
			$company = clean($_POST['company']);
			$type = clean($_POST['pctype']);
			$email = strip_tags($_POST['email']);
			$TeamID = (int)$_POST['TeamID'];
			$show_alerts = (int)$_POST['show_alerts'];
			//Edit Recents
			$activity = "Technician Edited Asset: ".$ID;
			userActivity($activity,$_SESSION['userid']);
			$query = "UPDATE users SET recentedit='".implode(",", $_SESSION['recentedit'])."' WHERE ID=".$_SESSION['userid'].";";
			if (in_array($ID, $_SESSION['recentedit'])){
				if (($key = array_search($ID, $_SESSION['recentedit'])) !== false) {
					unset($_SESSION['recentedit'][$key]);
				}
				array_push($_SESSION['recentedit'],$ID);
				$results = mysqli_query($db, $query); //Update
			}else{
				if(end($_SESSION['recentedit']) != $ID){
					array_push($_SESSION['recentedit'], $ID);
					$results = mysqli_query($db, $query); //Update
				}
			}
			//Update Computer Data
			$query = "UPDATE computerdata SET show_alerts='".$show_alerts."', teamviewer='".$TeamID."', computerType='".$type."', comment='".$comment."', name='".$name."', phone='".$phone."', CompanyID='".$company."', email='".$email."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			//header("location: index.php?page=General&ID=".$ID);
		}
		//Delete computer on edit.php
		if($_POST['type'] == "DeleteComputer"){
			$ID = (int)$_POST['ID'];
			$hostname = clean($_POST['hostname']);
			if($ID > 0){
				$query = "UPDATE computerdata SET active='0' WHERE ID='".$ID."';";
				$results = mysqli_query($db, $query);
				$query = "DELETE FROM wmidata WHERE ComputerID='".$ID."';";
				$results = mysqli_query($db, $query);
				$activity = "Technician Deleted Asset: ".$ID;
				userActivity($activity,$_SESSION['userid']);
				header("location: index.php");
			}
		}
		//Add Computers To Company
		if($_POST['type'] == "CompanyComputers"){
			$computers = $_POST['computers'];
			$companies = $_POST['companies'];
			foreach($computers as $computer) {
				$query = "UPDATE computerdata SET CompanyID='".$companies."' WHERE ID='".$computer."';";
				$results = mysqli_query($db, $query);
				echo $computer;
			}
			header("location: index.php");
		}
		//Add Edit/User
		if($_POST['type'] == "AddEditUser"){
			if(isset($_POST['username'])){
				$salt = getSalt(40);
				$user_ID = (int)$_POST['ID'];
				$username = clean($_POST['username']);
				$name = clean($_POST['name']);
				$phone = clean($_POST['phone']);
				$type = ucwords(clean($_POST['accountType']));
				$email = crypto('encrypt', $_POST['email'], $salt);
				$password = clean($_POST['password']);
				$password2 = clean($_POST['password2']);
				$encryptedPhone = $encryptedPhone = crypto('encrypt', $phone, $salt);
				$encryptedPassword = crypto('encrypt', $password, $salt);
				if($password === $password2){
					if($user_ID == 0){
						$query = "INSERT INTO users (accountType, phone, username, password, hex, nicename , email)
								  VALUES ('".$type."','".$encryptedPhone."','".$username."', '".$encryptedPassword."','".$salt."','".$name."','".$email."')";
						$activity = "Technician Added Another Technician: ".ucwords($name);
						userActivity($activity,$_SESSION['userid']);
					}else{
						$query = "SELECT password, hex FROM users WHERE ID='".$user_ID."' LIMIT 1";
						$results = mysqli_query($db, $query);
						$result = mysqli_fetch_assoc($results);
						if($password==""){
							$encryptedPassword = crypto('decrypt', $result['password'], $result['hex']);
							$encryptedPassword = crypto('encrypt', $encryptedPassword, $salt);
						}
						$query = "UPDATE users SET accountType='".$type."',phone='".$encryptedPhone."',username='".$username."',nicename='".$name."', email='".$email."', password='".$encryptedPassword."', hex='".$salt."' WHERE ID='".$user_ID."'";
						$activity = "Technician Edited Another Technician: ".ucwords($name);
						userActivity($activity,$_SESSION['userid']);
					}
					$results = mysqli_query($db, $query);
					echo '<script>window.onload = function() { pageAlert("User Settings", "User settings changed successfully.","Success"); };</script>';
				}else{ //passwords do not match
					echo '<script>window.onload = function() { pageAlert("User Settings", "Password change failed, passwords do not match.","Danger"); };</script>';
				}
				//header("location: index.php?page=AllUsers&danger=".base64_encode($error));
			}
		}
		//delete note
		if(isset($_POST['delNote'])){
			$delnote=(int)$_POST['delNote'];
			$query = "UPDATE users SET notes='' WHERE ID='".$delnote."';";
			$results = mysqli_query($db, $query);
			
			$activity="Technician Deleted User: ".$delnote." Notes";		
			userActivity($activity,$_SESSION['userid']);
			
			$activity="Admin Deleted All Notes";		
			userActivity($activity,$delnote);
			
			header("location: index.php");
		}
		//delete user activity
		if(isset($_POST['delActivity'])){
			$delActivity=(int)$_POST['delActivity'];
			$query = "UPDATE users SET userActivity='' WHERE ID='".$delActivity."';";
			$results = mysqli_query($db, $query);
			if($delActivity!=$_SESSION['userid']){
				$activity="Technician Deleted User: ".$delActivity." Activity Logs";		
				userActivity($activity,$_SESSION['userid']);
			}
			$activity="Admin Deleted All Activity Logs For This Technician";		
			userActivity($activity,$delActivity);
			header("location: index.php");
		}
		//Add Edit/Company
		if($_POST['type'] == "AddEditCompany"){
			if(isset($_POST['name'], $_POST['phone'], $_POST['address'], $_POST['email'])){
				$ID = (int)$_POST['ID'];
				$name = clean($_POST['name']);
				$phone = clean($_POST['phone']);
				$address = clean($_POST['address']);
				$comments = clean($_POST['comments']);
				$email = str_replace("'", "", $_POST['email']);
				if($ID == 0){
					$query = "INSERT INTO companies (name, phone, address, comments, email, date_added)
							  VALUES ('".$name."', '".$phone."', '".$address."', '".$comments."', '".$email."','".time()."')";
					$activity = "Technician Added A Company: ".$name;
					userActivity($activity,$_SESSION['userid']);
				}else{
					$query = "UPDATE companies SET name='".$name."', phone='".$phone."', address='".$address."', email='".$email."', comments='".$comments."'
							  WHERE CompanyID='".$ID."' LIMIT 1";
					$activity = "Technician Edited A Company: ".$name;
					userActivity($activity,$_SESSION['userid']);
				}
				$results = mysqli_query($db, $query);
				header("location: index.php?page=AllCompanies");
			}
		}
		//Delete Company
		if($_POST['type'] == "DeleteCompany"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['active'];
			$query = "UPDATE companies SET active='".$active."' WHERE CompanyID='".$ID."';";
			$results = mysqli_query($db, $query);
			$activity = "Technician Deleted A Company: ".$ID;
			userActivity($activity,$_SESSION['userid']);
			header("location: index.php?page=AllCompanies");
		}
		//Delete User
		if($_POST['type'] == "DeleteUser"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['active'];
			$query = "UPDATE users SET active='".$active."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			$activity = "Technician Deleted A Technician: ".$ID;
			userActivity($activity,$_SESSION['userid']);			
			header("location: index.php?page=AllUsers");
		}
		//Delete Command
		if($_POST['type'] == "CompanyUpdateAll"){
			$ID = $_POST['ID'];
			$active = (int)$_POST['active'];
			$activity = "Technician Deleted A Command: ".$ID;
			userActivity($activity,$_SESSION['userid']);
			$query = "UPDATE commands SET command='Deleted' WHERE ComputerID='".$ID."';";
			$results = mysqli_query($db, $query);
			header("location: index.php?page=AllCompanies");
		}
		//Create Note
		if(isset($_POST['note'])){
			$adminnote=(int)$_POST['adminnote'];
			if($adminnote==""){
				$ID=$_SESSION['userid'];
				$activity = "Technician Created A Note";
			}else{
				$ID=$adminnote;
				$activity = "Technician Created A Note For Technician: ".$adminnote;
				$activity2 = "Admin Created A Note For Technician";;
				userActivity($activity2,$adminnote);
			}
			$newnote = clean($_POST['note']);
			$query = "SELECT notes FROM users WHERE ID='".$ID."'";
			$results = mysqli_query($db, $query);
			$oldnote = mysqli_fetch_assoc($results);
			$note = $oldnote['notes'].$newnote."|";
			$query = "UPDATE users SET notes='".$note."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);		
			userActivity($activity,$_SESSION['userid']);
			header("location: index.php");
		}
		//Commands
		if($_POST['type'] == "SendCommand"){
			$ID = (int)$_POST['ID'];
			$commands = $_POST['command'];
			$args = $_POST['args'];
			$expire_after = (int)$_POST['expire_after'];
			$exists = 0;
			if(trim($commands)!=""){
				$query = "SELECT hostname FROM computerdata WHERE ID='".$ID."'";
				$results = mysqli_query($db, $query);
				$computer = mysqli_fetch_assoc($results);
				$query = "SELECT ID, expire_time FROM commands WHERE ComputerID='".$computer['hostname']."' AND status='Sent' AND command='".$commands."' AND userid='".$_SESSION['userid']."' ORDER BY ID DESC LIMIT 1";
				$results = mysqli_query($db, $query);
				$existing = mysqli_fetch_assoc($results);
				if($existing['ID'] != ""){
					if(strtotime(date("m/d/Y H:i:s")) <= strtotime($existing['expire_time'])){
						$exists = 1;
					}
				}
				if($exists == 0){
					//Generate expire time
					$expire_time = date("m/d/Y H:i:s", strtotime('+'.$expire_after.' minutes', strtotime(date("m/d/y H:i:s"))));
					$query = "INSERT INTO commands (ComputerID, userid, command, arg, expire_after, expire_time, status)
							  VALUES ('".$computer['hostname']."', '".$_SESSION['userid']."', '".$commands."', '".$args."', '".$expire_after."', '".$expire_time."', 'Sent')";
					$results = mysqli_query($db, $query);
				}
			}
			$activity = "Technician Sent ".$commands." ".$args." Command To: ".$ID;
			userActivity($activity,$_SESSION['userid']);
			header("location: index.php?page=General");
		}
		//Update Company Agents
		if($_POST['type'] == "CompanyUpdateAll"){
			$ID = (int)$_POST['CompanyID'];
			$commands = "C:\\\\SMG_RMM\\\\Update.bat";
			$args = "";
			$expire_after = 5;
			$exists = 0;
			$query = "SELECT ID, hostname FROM computerdata WHERE CompanyID='".$ID."' AND active='1'";
			$results = mysqli_query($db, $query);
			while($computer = mysqli_fetch_assoc($results)){
				$query = "SELECT ID, expire_time FROM commands WHERE ComputerID='".$computer['hostname']."' AND status='Sent' AND command='".$commands."' AND userid='".$_SESSION['userid']."' ORDER BY ID DESC LIMIT 1";
				$results = mysqli_query($db, $query);
				$existing = mysqli_fetch_assoc($results);
				if(isset($existing['ID'])){
					if(strtotime(date("m/d/Y H:i:s")) <= strtotime($existing['expire_time'])){
						$exists = 1;
					}
				}
				if($exists == 0){
					//Generate expire time
					$expire_time = date("m/d/Y H:i:s", strtotime('+'.$expire_after.' minutes', strtotime(date("m/d/y H:i:s"))));
					$query = "INSERT INTO commands (ComputerID, userid, command, arg, expire_after, expire_time, status)
							  VALUES ('".$computer['hostname']."', '".$_SESSION['userid']."', '".$commands."', '".$args."', '".$expire_after."', '".$expire_time."', 'Sent')";
					$results = mysqli_query($db, $query);
				}
			}
		}
		//Alert Config Modal
		if($_POST['type'] == "AlertSettings"){
			$alert_settings = "";
			$email = $_POST['alert_settings_email'];
			foreach($siteSettings['Alert Settings'] as $type=>$alert){
				foreach($alert as $option=>$options){
					 if(count($options) > 1){ //Contains Sub Options
						 foreach($options as $subOptionKey=>$subOptionValue){
							$keyName = $type."_".$option."_".$subOptionKey;
							$alert_settings .= $keyName.":".(int)$_POST['alert_settings_'.$keyName].",";
						}
					 }else{
						$keyName = $type."_".$option;
						$alert_settings .= $keyName.":".(int)$_POST['alert_settings_'.$keyName].",";
					 }
				}
			}
			$alert_settings = trim($alert_settings, ",");
			$query = "UPDATE users SET alert_settings='".$alert_settings."' WHERE ID='".$_SESSION['userid']."';";
			$results = mysqli_query($db, $query);
			if($results){
				echo '<script>window.onload = function() { pageAlert("Alert Settings", "Alert Settings Saved Successfully","Success"); };</script>';
			}
		}
		//Delete Version
		if(isset($_POST['version'])){
			$version=clean($_POST['version']);
			unlink("downloads/".$version);
			$activity = "Technician Deleted An Agent Version: ".$version;
			userActivity($activity,$_SESSION['userid']);
			header("location: index.php?page=Versions");
		}
		//Get Site Settings
		if($_POST['type'] == "getSiteSettings"){
			exit(file_get_contents("Includes/config.php"));
		}
		if($_POST['type'] == "saveSiteSettings"){
			$settings = "$siteSettingsJson = '".trim($_POST['settings'])."';";
			$configFile = "Includes/config.php";
			file_put_contents($configFile, $settings);
			exit();
		}
		//Upload or download new agent file
		if(isset($_POST['agentFile']) or isset($_POST['companyAgent'])){
			$agentVersion = clean($_POST['agentVersion']);
			if($_POST['agentVersion']==""){
				$agentVersion= $siteSettings['general']['agent_latest_version'];
			}else{
				$activity = "Technician Updated Latest Agent Version Number: ".$agentVersion;
				userActivity($activity,$_SESSION['userid']);	
			}
			$company = $_POST['companyAgent'];
			$uploaddir = 'Includes/agentFiles/bin/';
			$uploaddir2 = 'Includes/update/SMG_RMM.exe';
			$uploadfile = $uploaddir.$_FILES['agentUpload']['name'];
			$uploadfile2 = "Includes/agentFiles/bin/SMG_RMM.exe";
			if($company==""){
				move_uploaded_file($_FILES['agentUpload']['tmp_name'], $uploadfile);
				copy($uploadfile2, $uploaddir2);
			}
			ini_set('max_execution_time', 600);
			ini_set('memory_limit','1024M');
			$myfile = fopen("Includes/agentFiles/company.txt", "w") or die("Unable to open file!");
			fwrite($myfile, $company);
			echo $rootPath = realpath('Includes/agentFiles/');
			$zip = new ZipArchive();
			$zip->open('SMG_RMM('.$agentVersion.').zip', ZipArchive::CREATE | ZipArchive::OVERWRITE );
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
			foreach ($files as $name => $file){
				if (!$file->isDir()){
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath, strlen($rootPath) + 1);
					$zip->addFile($filePath, $relativePath);
				}
			}
			$zip->close();
			copy("SMG_RMM(".$agentVersion.").zip", "downloads/SMG_RMM(".$agentVersion.").zip");
			unlink("SMG_RMM(".$agentVersion.").zip");
			$activity = "Technician Downloaded Agent: ".$agentVersion;
			userActivity($activity,$_SESSION['userid']);
			if($company==""){
				$query = "UPDATE general SET agent_latest_version='".$agentVersion."' WHERE ID='1';";
				$results = mysqli_query($db, $query);
				$activity = "Technician Uploaded Agent File";
				userActivity($activity,$_SESSION['userid']);
				echo '<script>window.onload = function() { pageAlert("File Upload", "File Uploaded Successfully","Success"); };</script>';
			}else{
				$activity = "Technician Configured Customer: ".$company." Agent Files";
				userActivity($activity,$_SESSION['userid']);
				echo '<script>window.onload = function() { pageAlert("File Upload", "Download Started For Customer Agent","Default"); };</script>';
				header("location: ../../download/index.php?company=".$company);
			}
		}
	}
	//Get Stats
	$query = "SELECT CompanyID FROM companies where active='1'";
	$results = mysqli_query($db, $query);
	$companyCount = mysqli_num_rows($results);
	$query = "SELECT ID FROM users where active='1'";
	$results = mysqli_query($db, $query);
	$userCount = mysqli_num_rows($results);
	$query = "SELECT ID,teamviewer FROM computerdata where active='1'";
	$results = mysqli_query($db, $query);
	$resultCount = mysqli_num_rows($results);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>OpenRMM | Management</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--- Font Awesome --->
		<link rel="stylesheet" href="css/all.min.css"/>
		<script src="js/all.min.js"></script>
		<link rel="icon" href="images/favicon.ico" type="image/ico" sizes="16x16">
		<!-- jquery-->
		<script src="js/tagsinput.js"></script>
		<script src="js/jquery.js" ></script>
		<!--- Bootstap --->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css"/>
		<link rel="stylesheet" href="css/tagsinput.css"/>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">

		<link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css" rel="stylesheet">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="css/custom.css"/>
		<link rel="stylesheet" href="css/style2.css"/>
	</head>
	<style>
		a { color:#003366; }
		.calert { margin-left:5px;font-size:12px;width:46%;margin-right:5px;float:left;min-height:60px }
		@media screen and (max-width: 850px) {
			.calert { height: 120px; }
			.headall { display: none; }
		}
	</style>
	<body style="background-color:#f3f3f3;height:100%; position: relative;min-height: 100vh;">
		<div style="background-color:#fff;color:#fff;text-align:center;padding-top:4px;padding-left:20px;position:fixed;top:0px;width:100%;z-index:99;box-shadow: 0 0 11px rgba(0,0,0,0.13);">
			<h5>
				<div style="float:left;">
					<button type="button" style="display:inline-block;" class="btn-sm sidebarCollapse btn" title="Show/Hide Sidebar">
						<i style="font-size:16px" class="fas fa-align-left"></i>
					</button>
					
					<div style="display:inline-block;">
						<a style="color:#333;font-size:22px;cursor:pointer" onclick="loadSection('Dashboard');" >Open<span style="color:#fd7e14">RMM</span></a>
					</div>
				</div>
				<div style="float:right;">
					<div>
						<button type="button" onclick='pageAlert("<?php echo $messageTitle; ?>", "<?php echo textOnNull($messageText ,"No Messages"); ?>");' class="btn-md btn" title="Configure Alerts">
							<i style="font-size:16px" class="fas fa-bell"></i>
							<span style="margin-top" class="text-white badge bg-c-pink"><?php if($messageText==""){ echo "0"; }else{ echo "1"; } ?></span>
						</button>
					</div>
				</div>
			</h5>
		</div>
		<div class="wrapper">
			<!-- Sidebar -->
			<nav id="sidebar">
				<ul class="list-unstyled components" style="padding:20px;margin-top:25px;">
					<div style="text-align:center;width:100%">
						<a style="cursor:pointer" onclick="loadSection('Profile','<?php echo $_SESSION['userid']; ?>');">
							<i style="font-size:88px;text-align:center" class="fa fa-user" ></i>
							<h6 style="color:#fff;margin-top:10px"><?php echo ucwords($user['nicename']); ?></h6>
						</a>
						<a onclick="loadSection('Profile');"  style="cursor:pointer;color:#d3d3d3">Profile</a>
						<span style="color:#fff"> &#8226; </span> 					
						<a href="logout.php" style="color:#fd7e14">Logout</a>
						<hr>
					</div>					
					<li onclick="loadSection('Dashboard');" id="secbtnDashboard" class="secbtn">
						<i class="fas fa-home"></i>&nbsp;&nbsp;&nbsp; Dashboard
					</li>
					<li onclick="loadSection('Assets');" id="secbtnAgents" class="secbtn">
						<i class="fa fa-desktop" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp; Assets
					</li>
					<li>
						<h6 style="color:#d3d3d3" data-toggle="collapse" data-target="#navConfig"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;&nbsp;Configuration <i class="fa fa-angle-down" aria-hidden="true"></i></h6>
					</li>
					<ul style="margin-left:20px" class="nav nav-list collapse" id="navConfig">
					<?php if($_SESSION['accountType']=="Admin"){ ?>
						<li onclick="loadSection('AllCompanies');" id="secbtnAllCompanies" style="width:100%" class="secbtn">
							<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Customers
						</li>
						<li onclick="loadSection('AllUsers');" id="secbtnAllUsers" style="width:100%" class="secbtn">
							<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Technicians
						</li>
					<?php } ?>
						<li onclick="loadSection('Versions');" id="secbtnVersions" class="secbtn" style="width:100%">
							<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Agent
						</li>
					<?php if($_SESSION['accountType']=="Admin"){ ?>
						<!--li onclick="loadSection('SiteSettings');" id="secbtnSiteSettings" style="width:100%" class="secbtn">
							<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Settings
						</li-->
					<?php } ?>
					</ul>
					<hr>
					<div id="sectionList" style="display:none;">
						<h5 class="sidebarComputerName"></h5>
						<li onclick="loadSection('General');" id="secbtnGeneral" class="secbtn">
							<i class="fas fa-stream"></i>&nbsp;&nbsp;&nbsp; Overview
						</li>
						<li onclick="loadSection('Network');" id="secbtnNetwork" class="secbtn">
							<i class="fas fa-network-wired"></i>&nbsp;&nbsp;&nbsp; Network
						</li>
						<li onclick="loadSection('Programs');" id="secbtnPrograms" class="secbtn">
							<i class="fab fa-app-store-ios"></i>&nbsp;&nbsp;&nbsp; Programs
						</li>
						<li onclick="loadSection('DefaultPrograms');" id="secbtnDefaultPrograms" class="secbtn">
							<i class="fab fa-app-store-ios"></i>&nbsp;&nbsp;&nbsp; Default Programs
						</li>
						<li onclick="loadSection('Services');" id="secbtnServices" class="secbtn">
							<i class="fas fa-cogs"></i>&nbsp;&nbsp;&nbsp; Services
						</li>
						<li onclick="loadSection('Proccesses');" id="secbtnProccesses" class="secbtn">
							<i class="fas fa-microchip"></i>&nbsp;&nbsp;&nbsp; Processes
						</li>
						<li onclick="loadSection('Printers');" id="secbtnPrinters" class="secbtn">
							<i class="fas fa-edit"></i>&nbsp;&nbsp;&nbsp; Printers
						</li>
						<li onclick="loadSection('Disks');" id="secbtnDisks" class="secbtn">
							<i class="fas fa-hdd"></i>&nbsp;&nbsp;&nbsp; Disks
						</li>
						<li onclick="loadSection('Memory');" id="secbtnMemory" class="secbtn">
							<i class="fas fa-memory"></i>&nbsp;&nbsp;&nbsp; Memory
						</li>
						<li onclick="loadSection('AttachedDevices');" id="secbtnAttachedDevices" class="secbtn">
							<i class="fab fa-usb"></i>&nbsp;&nbsp;&nbsp; Attached Devices
						</li>
						<li onclick="loadSection('OptionalFeatures');" id="secbtnOptionalFeatures" class="secbtn">
							<i class="fas fa-list"></i>&nbsp;&nbsp;&nbsp; Optional Features
						</li>
						<li onclick="loadSection('Users');" id="secbtnUsers" class="secbtn">
							<i class="fas fa-users"></i>&nbsp;&nbsp;&nbsp; User Accounts
						</li>
						<li></li>
					</div>				
					<div class="recents" id="recents" style="margin-top:20px;"></div>	
					
					<div style="height:100px">&nbsp;</div>		
				</ul>
			</nav>
			<!-- Page Content -->
			<div id="content" style="margin-top:15px;padding:30px;width:100%;">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
						<div style="width:100%;background: -webkit-gradient(linear, left top, right top, from(#fe9365), to(#feb798));box-shadow: 0 0 11px rgba(0,0,0,0.13);
						background:#fe9365;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
							<a style="color:#fff;cursor:pointer;" onclick="loadSection('Assets');">
								<div style="padding:10px 10px 0px 20px;">
									<i class="fas fa-desktop" style="font-size:28px;float:right;"></i>
									<span style="font-size:20px;" ><?php echo $resultCount; ?></span><br>
									<span style="font-size:20px;">Assets</span>
								</div>
															
							</a>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
						<div style="width:100%;background: -webkit-gradient(linear, left top, right top, from(#0ac282), to(#0df3a3));box-shadow: 0 0 11px rgba(0,0,0,0.13);
						background:#0ac282;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
							<a style="color:#fff;cursor:pointer;" onclick="loadSection('AllCompanies');">
								<div style="padding:10px 10px 0px 20px;">
									<i class="fas fa-building" style="font-size:28px;float:right;"></i>
									<span style="font-size:20px;"><?php echo $companyCount;?></span><br>
									<span style="font-size:20px;">Customers</span>
								</div>
							</a>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
						<div style="width:100%;background: -webkit-gradient(linear, left top, right top, from(#eb3422), to(#ef5f51));box-shadow: 0 0 11px rgba(0,0,0,0.13);
						background:#eb3422;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
							<a style="color:#fff;cursor:pointer;" onclick="loadSection('AllUsers');">
								<div style="padding:10px 10px 0px 20px;">
									<i class="fas fa-user" style="font-size:28px;float:right;"></i>
									<span style="font-size:20px;"><?php echo $userCount;?></span><br>
									<span style="font-size:20px;">Technicians</span>
								</div>
							</a>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
						<div style="width:100%;background: -webkit-gradient(linear, left top, right top, from(#01a9ac), to(#01dbdf));box-shadow: 0 0 11px rgba(0,0,0,0.13);
						background:#01a9ac;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;">
							<a style="color:#fff;cursor:pointer;" onclick="loadSection('Tickets');">
								<div style="padding:10px 10px 0px;">
									<i class="fas fa-ticket-alt" style="font-size:28px;float:right;"></i>
									<span style="font-size:20px;"><?php echo $userCount;?></span><br>
									<span style="font-size:20px;">Tickets</span>
								</div>
							</a>
						</div>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-top:20px;">
						<div class="loadSection">
							<!------ Loads main data from jquery ------>
							<center>
								<h1 style="margin-top:40px;">
									<i class="fas fa-spinner fa-spin"></i>
								</h1>
							</center>
						</div>
						<div style="height:50px;" class="clearfix">&nbsp;</div>						
					</div>
					<footer style="display:none;" class="page-footer font-small black">
						<div class="footer-copyright text-center">© <?php echo date('Y');?> Copyright
							<a style="color:#fff;" target="_blank" href="https://github.com/OpenRMM"> OpenRMM</a>
							<a style="font-size:12px;cursor:pointer;float:left;padding-right:10px;color:#fff" onclick="loadSection('Versions');"><u>Previous Agent Versions</u></a>
						</div>
					</footer>
				</div>
			</div>
		</div>
		<!-------------------------------MODALS------------------------------------>
		<!--------------- Notification Modal ------------->
		<div id="alertModal" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-md">
			<div class="modal-content" >
			  <div class="modal-header">
				<h6 class="modal-title"><b>Notifications</b></h6>
			  </div>
				  <div class="modal-body">
					<ul>					
					<li>No New Notifications</li>
					</ul>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">Close</button>
				  </div>
			</div>
		  </div>
		</div>
		<!--------------- User Modal ------------->
		<div id="userModal" class="modal fade" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h6><b>Add/Edit User</b></h6>
			  </div>
			  <form id="user" method="POST">
				  <input type="hidden" name="type" value="AddEditUser"/>
				  <input type="hidden" name="ID" id="editUserModal_ID"/>
				  <div class="modal-body">
					<p>This will configure a new user and will allow them access to this platform.</p>
					<div class="form-group">
						<input placeholder="Name" type="text" name="name" class="form-control" id="editUserModal_name"/>
					</div>
					<div class="form-group">
						<input placeholder="Email"  type="email" name="email" class="form-control" id="editUserModal_email"/>
					</div>
					<div class="form-group">
						<input placeholder="Username"  required type="text" name="username" class="form-control" id="editUserModal_username"/>
					</div>
					<div class="form-group">
						<input placeholder="Phone" type="text" name="phone" class="form-control" id="editUserModal_phone"/>
					</div>
					<div class="form-group">
						<label for="editUserModal_type">Access Type</label>
						<select required name="accountType" class="form-control">
						  <option id="editUserModal_type" value="">Select Option</option>
						   <option value="Standard">Standard</option>
						  <option value="Admin">Admin</option>
						</select>
					  </div>
					<div class="input-group">
						<input placeholder="Password" style="display:inline" type="password" id="editUserModal_password" name="password" class="form-control"/>
						  <span class="input-group-btn">
							<a style="border-radius:0px;padding:6px;pointer:cursor;color:#fff;" class="btn btn-md btn-success" onclick="generate();" >Generate</a>
					   </span>
					</div>		<br>
					<div class="form-group">
						<input placeholder="Confirm Password" type="password" id="editUserModal_password2" name="password2" class="form-control"/>
					</div>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-sm btn-warning">
						<i class="fas fa-check"></i> Save
					</button>
				  </div>
			  </form>
			</div>
		  </div>
		</div>
		<!--------------- Version Modal ------------->
		<div id="versionModal" class="modal fade" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h6><b>Delete Version</b></h6>
			  </div>
			  <form id="user" method="POST">
				  <input type="hidden" name="version" value="" id="delVersion_ID"/>
				  <div class="modal-body">
					<p>This will delete this agent version. Are you sure?</p>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
					<button type="submit" style="color:#fff" class="btn btn-sm btn-danger">
						<i class="fas fa-trash"></i> Delete
					</button>
				  </div>
			  </form>
			</div>
		  </div>
		</div>
			<!--------------- Note Modal ------------->
		<div id="noteModal" class="modal fade" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h6><b>Create A New Note</b></h6>
			  </div>
			  <form id="note" method="POST">
				  <div class="modal-body">
					<p>This Will Create A New Note That Only You Can See.</p>
					<textarea required maxlength="300" name="note" class="form-control"></textarea>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
					<button type="submit"  class="btn btn-sm btn-warning">
						<i class="fas fa-save"></i> Save
					</button>
				  </div>
			  </form>
			</div>
		  </div>
		</div>
		<!---------- Company Modal ------------>
		<div id="companyModal" class="modal fade" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h6><b>Add/Edit Company</b></h6>
			  </div>
			  <form method="POST">
				  <input type="hidden" name="type" value="AddEditCompany"/>
				  <input type="hidden" name="ID" value="" id="editCompanyModal_ID"/>
				  <div class="modal-body">
					<p>This Will Add Company Information. To Better Assist And Organize Content.</p>
					<div class="form-group">
						<input placeholder="Name" type="text" name="name" class="form-control" id="editCompanyModal_name"/>
					</div>
					<div class="form-group">
						<input placeholder="Address" type="text" name="address" class="form-control" id="editCompanyModal_address"/>
					</div>
					<div class="form-group">
						<input placeholder="Phone" type="phone" name="phone" class="form-control" id="editCompanyModal_phone"/>
					</div>
					<div class="form-group">
						<input placeholder="Email" type="email" name="email" class="form-control" id="editCompanyModal_email"/>
					</div>
					<div class="form-group">
						<textarea placeholder="Additional Info" style="resize:vertical" name="comments" class="form-control" placeholder="Optional" id="editCompanyModal_comments"></textarea>
					</div>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
					<button type="submit" class="btn btn-sm btn-warning">
						<i class="fas fa-check"></i> Save
					</button>
				  </div>
			  </form>
			</div>
		  </div>
		</div>
		<!----------- Terminal ---------------->
		<div id="terminalModal" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<h6><b>Terminal</b></h6>
			  </div>
			  <div class="modal-body" style="background-color:#000;color:#fff;font-family: 'Courier New', Courier, monospacepadding:20px;">
				<div style="max-height:400px;margin-bottom:10px;min-height:100px;overflow:auto;">
					<div id="terminalResponse" style="color:#fff;font-family:font-family:monospace;">
						Microsoft Windows [Version 10.0.<?php echo rand(100000,9999999);?>]<br/>
						(c) <?php echo date("Y");?> Microsoft Corporation. All rights reserved.
						<br/><br/>
					</div>
				</div>
				<div style="min-height:50px;">
					<?php echo strtoupper($data['hostname']);?>> <input type="text" id="terminaltxt" style="outline: none;border:none;background:#000;width:300px;color:#fff;font-family:font-family:monospace;"/>
				</div>
			  </div>
			</div>
		  </div>
		</div>
			<!------------- Alerts ------------------->
		<div id="confirm" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<h6 id="computerAlertsHostname"><b>Confirm Action</b></h6>
			  </div>
			  <div class="modal-body">
				<p>Are You Sure You Would Like To Complete This Action></p>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 4']; ?>;color:#fff;" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-sm btn-warning"  data-dismiss="modal">Confirm</button>
			  </div>
			</div>
		  </div>
		</div>
		<!------------- Alerts ------------------->
		<div id="computerAlerts" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<h6 id="computerAlertsHostname"><b>Alerts</b></h6>
			  </div>
			  <div class="modal-body">
				<div  id="computerAlertsModalList"></div>
							  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">Close</button>
			  </div>
			</div>
		  </div>
		</div>
		<!------------- Page Errors ------------------->
		<div id="pageAlert" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-md">
			<div class="modal-content">
			  <div class="modal-header">
				 <h6 class="modal-title" id="pageAlert_title">Message From Webpage</h6>
			  </div>			 
			  <div class="modal-body">			
				<div id="pageAlert_message" class="alert">No Message</div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-sm btn-warning"  data-dismiss="modal">Close</button>
			</div>
		  </div>
		</div>
	</div>
		<!------------- Historical ------------------->
		<div id="historicalData_modal" class="modal fade" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title">Historical Data</h5>
			  </div>
			  <div class="modal-body">
				<div id="historicalData" style="overflow:auto;max-height:400px;"></div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-sm btn-warning"  data-dismiss="modal">Close</button>
			  </div>
			</div>
		  </div>
		</div>
		<!------------- Historical Date Selection  ------------------->
		<div id="historicalDateSelection_modal" class="modal fade" role="dialog">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title">Historical Data</h5>
			  </div>
			  <div class="modal-body" style="overflow:auto;max-height:400px;">
				<table class="table table-striped">
					<tr>
						<td>Latest</td>
						<td>
							<button type="button" onclick="loadSectionHistory();" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 1']; ?>;color:#fff;">
								Select
							</button>
						</td>
					</tr>
					<?php
						$showLast = $siteSettings['Max_History_Days']; //Show last 31 days
						$count = 0;
						while($count <= $showLast){ $count++;
						 $niceDate = date("l, F jS", strtotime("-".$count." day"));
						 $formatedDate = date("n/j/Y", strtotime("-".$count." day"));
					?>
					<tr>
						<td><?php echo $niceDate; ?></td>
						<td>
							<button type="button" onclick="loadSectionHistory('<?php echo $formatedDate;?>');" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 1']; ?>;color:#fff;">Select</button>
						</td>
					</tr>
					<?php }?>
				</table>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-sm btn-warning"  data-dismiss="modal">Close</button>
			  </div>
			</div>
		  </div>
		</div>
		<!------------- Upload .exe File ------------------->
		<div id="agentUpload" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-lg">
			<div class="modal-content">
			  <div class="modal-header">
				<h6><b>Upload New Agent (.exe)</b></h6>
			  </div>
			 <form enctype="multipart/form-data" method="POST">
			  <div class="modal-body">
			  <p>This Will Create A Downloadable .Zip File. It Will Also Rewrite The Existing Update Directory.</p>
				<div class="input-group">
				  <div class="input-group-prepend">
					<span class="input-group-text" id="inputGroupFileAddon01">Agent Version</span>
				  </div>
				  <input style="padding:20px" type="text" name="agentVersion" required minlength=7 class="form-control" placeholder="ex. 1.0.0.4" value="<?php echo $siteSettings['general']['agent_latest_version']; ?>"/>&nbsp;
				  <div class="input-group-prepend">
					<span class="input-group-text" id="inputGroupFileAddon01">Upload .exe</span>
				  </div>
				  <div class="custom-file" >
				    <input required="" type="hidden" value="true" name="agentFile">
					<input  required="" accept=".exe" type="file" name="agentUpload" class="custom-file-input" id="agentUpload"/>
					<label style="padding:10px;padding-bottom:30px" class="custom-file-label" for="agentUpload">Choose file</label>
				  </div>
				</div>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-sm"  data-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-sm btn-warning" >Upload</button>
			  </div>
			</form>
			</div>
		  </div>
		</div>
		<!---------------------------------End MODALS------------------------------------->
	</body>
	<script src="js/extra.js" ></script>
	<script>
		//Load Page
		if (document.cookie.indexOf('section') === -1 ) {
			setCookie("section", "Dashboard", 365);
		}
		var computerID = getCookie("ID");
		var currentSection = getCookie("section");
		var sectionHistoryDate = "latest";
		//Load Pages
		function loadSection(section=currentSection, ID=computerID, date=sectionHistoryDate){
			document.cookie = "section=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
			document.cookie = "ID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
			setCookie("section", section, 365);
			setCookie("ID", ID, 365);
			computerID = ID;
			currentSection = section;
			$(".loadSection").html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow text-primary'></div><div class='spinner-grow text-success'></div><div class='spinner-grow text-info'></div><div class='spinner-grow text-warning'></div><div class='spinner-grow text-danger'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3>");
			$(".loadSection").load("ajax/"+section+".php?ID="+ID+"&Date="+date);

			$(".recents").load("ajax/recent.php?ID="+ID);
			if(section == "Profile" || section == "Assets" || section == "Dashboard" || section == "AllUsers" || section == "AllCompanies" || section == "NewComputers" || section == "Versions" || section == "SiteSettings"){
				$('#sectionList').slideUp(400);
			}else if($('#sectionList').css("display")=="none"){
				$('#sectionList').slideDown(400);
			}
			if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
				$('#sidebar').removeClass('active');
			}
		}
		//Load historical section, Network, Programs...
		function loadSectionHistory(date="latest"){
			sectionHistoryDate = date;
			$(".loadSection").html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow text-primary'></div><div class='spinner-grow text-success'></div><div class='spinner-grow text-info'></div><div class='spinner-grow text-warning'></div><div class='spinner-grow text-danger'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3>");
			$(".loadSection").load("ajax/"+currentSection+".php?ID="+computerID+"&Date="+date);
			$("#historicalDateSelection_modal").modal("hide");
		}
		
		<?php if($_GET['page']==""){ ?>
			loadSection(currentSection, computerID);
		<?php }else{ ?>
			loadSection("<?php echo ucfirst($_GET['page']);?>", "<?php echo (int)$_GET['ID'];?>");
		<?php }?>
		
		//Sidebar
		$(document).ready(function () {
			$('.sidebarCollapse').on('click', function () {
				$('#sidebar').toggleClass('active');
			});
		});
		//Search
		function search(text, page="Dashboard", ID=0, filters="", limit=25){
			$('body').removeClass('modal-open');
			$('.modal-backdrop').remove();
			$(".loadSection").html("<center><h3 style='margin-top:40px;'><i class='fas fa-spinner fa-spin'></i> Loading Results</h3></center>");
			$(".loadSection").load("ajax/"+page+".php?limit="+limit+"&search="+encodeURI(text)+"&ID="+ID+"&filters="+encodeURI(filters)+"&Date="+sectionHistoryDate);
		}
		//Terminal
		$('#terminaltxt').keypress(function(event){
			var keycode = (event.keyCode ? event.keyCode : event.which);
			if(keycode == '13'){
				$("#terminalResponse").html("Sending Command: "+$('#terminaltxt').val()+" <i class='fas fa-spinner fa-spin'></i>");
				$.post("ajax/terminal.php", {
				  id: computerID,
				  command: $('#terminaltxt').val()
				},
				function(data, status){
				  $("#terminalResponse").html(data);
				});
			}
		});
		//Alerts Modal
		function computerAlertsModal(title, delimited='none', showHostname = false){
			$("#computerAlertsHostname").html("<b>Alerts for "+title+"</b>");
			if(delimited=="none"){
				$("#computerAlertsModalList").html("<div class='alert alert-success' style='font-size:12px' role='alert'><b><i class='fas fa-thumbs-up'></i> No Issues</b></div>");
				return;
			}
			$("#computerAlertsModalList").html("")
			var alerts = delimited.split(",");
			var hostname = "";
			for(alert in alerts){
				var alertData = alerts[alert].split("|");
				if(alertData[0].trim()==""){
					continue;
				}
				if(showHostname == true){
					hostname = alertData[3];
				}
				$("#computerAlertsModalList").html($("#computerAlertsModalList").html() + "<div class='calert alert alert-"+alertData[2]+"' role='alert'><b><i class='fas fa-exclamation-triangle text-"+alertData[2]+"'></i> "+ hostname + " " + alertData[0]+"</b> - " + alertData[1] + "</div>");
			}
		}
		//Random password
		function randomPassword(length) {
			var chars = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";
			var pass = "";
			for (var x = 0; x < length; x++) {
				var i = Math.floor(Math.random() * chars.length);
				pass += chars.charAt(i);
			}
			return pass;
		}
		//Set random passwords to inputs
		function generate() {
			var pass = randomPassword(8);
			$('#editUserModal_password').prop('type', 'text').val(pass);
			$('#editUserModal_password2').prop('type', 'text').val(pass);
		}
		//Page Alerts, replaces alert()
		function pageAlert(title, message, type="Default"){
			var types = {Default:"alert-primary", Success:"alert-success", Warning:"alert-warning", Danger:"alert-danger"};
			if(title.trim() == ""){
				title = "Message From Webpage";
			}
			$("#pageAlert_message").removeClass().addClass("alert").addClass(types[type]);
			$("#pageAlert").modal("show");
			$("#pageAlert_title").text(title);
			$("#pageAlert_message").html(message);
		}
		//Load Historical Data
		function loadHistoricalData(hostname, type){
			$("#historicalData").html("<center><h3 style='margin-top:40px;'><i class='fas fa-spinner fa-spin'></i></h3></center>");
			$("#historicalData_modal").modal("show");
			$.post("ajax/LoadHistorical.php", {
			  hostname: hostname,
			  type: type
			},
			function(data, status){
			  $("#historicalData").html(data);
			});
		}
		function sendCommand(command, args, prompt, expire_after=5){
			if(confirm("Are you sure you would like to "+prompt+"?")){
				$.post("index.php", {
				  type: "SendCommand",
				  ID: computerID,
				  command: command,
				  args: args,
				  expire_after: expire_after
				},
				function(data, status){
					$("#pageAlert").modal("show");
					$("#pageAlert_title").text("Request Action");
					$("#pageAlert_message").html("Your Request Has Been Sent.");
				});
			}
		}
		function toggle(source) {
		  checkboxes = document.getElementsByName('computers[]');
		  for(var i=0, n=checkboxes.length;i<n;i++) {
		    checkboxes[i].checked = source.checked;
		  }
		}
		<?php
			if($_SESSION['showModal']=="true" && 1==1){
				//show modal once after login
				echo 'pageAlert("'.$messageTitle.'", "'.$messageText.'");';
				$_SESSION['showModal'] = "";
			}
		?>
		
	</script>
	
</html>