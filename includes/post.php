<?php
        if($_POST['type'] == "AddNewUser"){  //this can prblem be replaced for the add/edit user script
            $salt = getSalt(40);
            $username = clean($_POST['username']);
            $password = clean($_POST['password']);
            $encryptedPassword = crypto('encrypt', $password, $salt);
            $query = "INSERT INTO users (nicename, accountType, username, password, hex) VALUES ('".$username."','Admin','".$username."', '".$encryptedPassword."', '".$salt."')";
            $results = mysqli_query($db, $query);
            $_SESSION['excludedPages'] = explode(",",$excludedPages);
            // echo mysqli_error($db);exit;
            // echo '<script>window.onload = function() { pageAlert("User Settings", "User settings changed successfully.","Success"); };</script>';
            header("location: index.php");
        }
        //init.php
        if($_POST['type'] == "init"){
            $mysqlHost = clean($_POST['mysqlHost']);
            $mysqlPort = clean($_POST['mysqlPort']);
            $mysqlUsername = clean($_POST['mysqlUsername']);
            $mysqlPassword = clean($_POST['mysqlPassword']);
            $mysqlDatabase = clean($_POST['mysqlDatabase']);
            $theme = clean($_POST['theme']);
            if($theme=="theme1"){
                    $color1="#f0f0f0";
                    $color2="#fe6f33";
                    $color3="#0ac282";
                    $color4="#eb3422";
                    $color5="#01a9ac";
            }
            if($theme=="theme2"){
                $color1="#fff";
                $color2="#333";
                $color3="#a4b0bd";
                $color4="#696969";
                $color5="#595f69";
            }
            if($theme=="theme3"){
                $color1="#f3f3f3";
                $color2="#0ac282";
                $color3="#a4b0bd";
                $color4="#333";
                $color5="#595f69";
            }
  
			$mqttHost = clean($_POST['mqttHost']);
            $mqttPort = clean($_POST['mqttPort']);
            $mqttUsername = clean($_POST['mqttUsername']);
            $mqttPassword = clean($_POST['mqttPassword']);

			$encryptionSecret = base64_encode(getSalt(40));
            $encryptionSalt = base64_encode(getSalt(40));
           
            include("includes/config_init.php");
           
            $data = $siteSettingsJson;
            $data = str_replace("[mysqlHost]",$mysqlHost,$data);
            $data = str_replace("[mysqlPort]",$mysqlPort,$data);
            $data = str_replace("[mysqlUsername]",$mysqlUsername,$data);
            $data = str_replace("[mysqlPassword]",$mysqlPassword,$data);
            $data = str_replace("[mysqlDatabase]",$mysqlDatabase,$data);

            $data = str_replace("[mqttHost]",$mqttHost,$data);
            $data = str_replace("[mqttPort]",$mqttPort,$data);
            $data = str_replace("[mqttUsername]",$mqttUsername,$data);
            $data = str_replace("[mqttPassword]",$mqttPassword,$data);

            $data = str_replace("[color1]",$color1,$data);
            $data = str_replace("[color2]",$color2,$data);
            $data = str_replace("[color3]",$color3,$data);
            $data = str_replace("[color4]",$color4,$data);
            $data = str_replace("[color5]",$color5,$data);

			$data = str_replace("[secret]",$encryptionSecret,$data);
			$data = str_replace("[salt]",$encryptionSalt,$data);

            unlink("includes/config.php");
            $_SESSION['excludedPages'] = explode(",",$excludedPages);
            file_put_contents("includes/config.php","<?php \$siteSettingsJson = '".$data."';");
            header("location: index.php");
        }
        //edit asset
        if($_POST['type'] == "EditComputer"){
			$ID = (int)$_POST['ID'];
			$name = clean($_POST['name']);
			$comment = clean($_POST['comment']);
			$phone = clean($_POST['phone']);
			$company = clean($_POST['company']);
			$type = clean($_POST['pctype']);
			$email = strip_tags($_POST['email']);
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
			$query = "UPDATE computerdata SET show_alerts='".$show_alerts."', computer_type='".$type."', comment='".$comment."', name='".$name."', phone='".$phone."', CompanyID='".$company."', email='".$email."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			header("location: index.php");
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
				if($_SESSION['accountType']!="Admin"){  
					$type="Standard";
				}
				if($password == $password2){
					if($user_ID == 0){
						$query = "INSERT INTO users (accountType, phone, username, password, hex, nicename , email)
								  VALUES ('".$type."','".$encryptedPhone."','".$username."', '".$encryptedPassword."','".$salt."','".$name."','".$email."')";
                                 
						$activity = "Technician Added Another Technician: ".ucwords($name);
					//	userActivity($activity,$_SESSION['userid']);
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
					if(!$results = mysqli_query($db, $query)){  }
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
			$query = "UPDATE users SET notes='' WHERE ID='".$_SESSION['userid']."';";
			$results = mysqli_query($db, $query);			
			$activity="Technician Deleted All Notes";		
			userActivity($activity,$_SESSION['userid']);
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

		//delete task
		if($_POST['type'] == "delTask"){
			$del=(int)$_POST['ID'];
			$query = "UPDATE tasks SET active='0' WHERE ID='".$del."';";
			$results = mysqli_query($db, $query);
			$activity="Technician Deleted Task ID: ".$del;		
			userActivity($activity,$_SESSION['userid']);
			header("location: index.php");
		}

		//Create task
		if($_POST['type'] == "newTask"){
			$name=clean($_POST['name']);
			$taskCond1=clean($_POST['taskCond1']);
			$taskCond1Comparison=clean($_POST['taskCond1Comparison']);
			$taskCond1Value=clean($_POST['taskCond1Value']);
			$taskCond2=clean($_POST['taskCond2']);
			$taskCond2Comparison=clean($_POST['taskCond2Comparison']);
			$taskCond2Value=clean($_POST['taskCond2Value']);
			$taskCond3=clean($_POST['taskCond3']);
			$taskCond3Comparison=clean($_POST['taskCond3Comparison']);
			$taskCond3Value=clean($_POST['taskCond3Value']);
			$taskAct1=clean($_POST['taskAct1']);
			$taskAct1Title=clean($_POST['taskAct1Title']);
			$taskAct1Message=clean($_POST['taskAct1Message']);

			$query = "INSERT INTO tasks (taskName,condition1Type,condition1Comparison,condition1Value,condition2Type,condition2Comparison,condition2Value,condition3Type,condition3Comparison,condition3Value,actionCommand,actionTitle,actionValue)
			VALUES ('".$name."','".$taskCond1."','".$taskCond1Comparison."','".$taskCond1Value."','".$taskCond2."','".$taskCond2Comparison."','".$taskCond2Value."','".$taskCond3."','".$taskCond3Comparison."','".$taskCond3Value."','".$taskAct1."','".$taskAct1Title."','".$taskAct1Message."')";
  			$results = mysqli_query($db, $query);
			//echo mysqli_error($db);exit;
			$activity="User Created: ".$taskCond1." task";		
			userActivity($activity,$_SESSION['userid']);
			
			header("location: index.php");
		}

		//Oneway asset message
		if($_POST['type'] == "assetOneWayMessage"){
			$ID=clean($_POST['ID']);
			$message=clean($_POST['alertMessage']);
			$title=clean($_POST['alertTitle']);
			$type=clean($_POST['alertType']);
			$script = '{"userID":"'.$_SESSION['userid'].'","data": {"Title": "'.$title.'", "Message": "'.$message.'", "Type":"'.$type.'"}}';
			MQTTpublish($ID."/Commands/setAlert",$script,$ID,false);	
			$activity="Technician Sent Asset: ".$ID." A One-way Message";		
			userActivity($activity,$_SESSION['userid']);
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
					$query = "INSERT INTO companies (name, phone, address, comments, email)
							  VALUES ('".$name."', '".$phone."', '".$address."', '".$comments."', '".$email."')";
					$activity = "Technician Added A Company: ".$name;
					userActivity($activity,$_SESSION['userid']);
				}else{
					$query = "UPDATE companies SET name='".$name."', phone='".$phone."', address='".$address."', email='".$email."', comments='".$comments."'
							  WHERE CompanyID='".$ID."' LIMIT 1";
					$activity = "Technician Edited A Company: ".$name;
					userActivity($activity,$_SESSION['userid']);
				}
				$results = mysqli_query($db, $query);
				
				header("location: index.php");
			}
		}
		//Delete Company
		if($_POST['type'] == "DeleteCompany"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['companyactive'];
			$query = "UPDATE companies SET active='".$active."' WHERE CompanyID='".$ID."';";
			$results = mysqli_query($db, $query);
			$activity = "Technician Deleted A Company: ".$ID;
			userActivity($activity,$_SESSION['userid']);
			header("location: index.php");
		}
		//Delete User
		if($_POST['type'] == "DeleteUser"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['useractive'];
			$query = "UPDATE users SET active='".$active."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			$activity = "Technician Deleted A Technician: ".$ID;
			userActivity($activity,$_SESSION['userid']);			
			header("location: index.php");
		}
		//Delete Command
		if($_POST['type'] == "DeleteCommand"){
			$ID = $_POST['ID'];
			$active = (int)$_POST['commandactive'];
			$activity = "Technician Deleted A Command: ".$ID;
			userActivity($activity,$_SESSION['userid']);
			$query = "UPDATE commands SET command='Deleted' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			header("location: index.php");
		}
		//Create Note
		if(isset($_POST['note'])){			
			$ID=$_SESSION['userid'];
			$activity = "Technician Created A Note";
			$newnote = clean($_POST['note']);
			$noteTitle = clean($_POST['noteTitle']);
			$query = "SELECT notes FROM users WHERE ID='".$ID."'";
			$results = mysqli_query($db, $query);
			$oldnote = mysqli_fetch_assoc($results);
			$note = $oldnote['notes'].$noteTitle."^".$newnote."|";
			$query = "UPDATE users SET notes='".$note."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);		
			userActivity($activity,$_SESSION['userid']);
			header("location: index.php");
		}
		//Commands
		if($_POST['type'] == "SendCommand"){
			$ID = (int)$_POST['ID'];
			$commands = $_POST['command'];
			$expire_after = (int)$_POST['expire_after'];
			$exists = 0;
			if(trim($commands)!=""){
				$query = "SELECT hostname,ID FROM computerdata WHERE ID='".$ID."'";
				$results = mysqli_query($db, $query);
				$computer = mysqli_fetch_assoc($results);
				$query = "SELECT ID, expire_time FROM commands WHERE ComputerID='".$computer['ID']."' AND status='Sent' AND command='".$commands."' AND userid='".$_SESSION['userid']."' ORDER BY ID DESC LIMIT 1";
				$results = mysqli_query($db, $query);
				$existing = mysqli_fetch_assoc($results);
				if($existing['ID'] != ""){
					if(strtotime(date("Y-m-d h:i:s")) <= strtotime($existing['expire_time'])){
						$exists = 1;
					}
				}
				if($exists == 0){
					//Generate expire time
					$expire_time = date("Y-m-d h:i:s", strtotime('+'.$expire_after.' minutes', strtotime(date("Y-m-d h:i:s"))));
					
					$query = "INSERT INTO commands (ComputerID, userid, command, expire_after, expire_time, status)
							  VALUES ('".$computer['ID']."', '".$_SESSION['userid']."', '".$commands."', '".$expire_after."', '".$expire_time."', 'Sent')";
					$results = mysqli_query($db, $query);
					$insertID = mysqli_insert_id($db);
					MQTTpublish($computer['ID']."/Commands/CMD",'{"userID":'.$_SESSION['userid'].',"commandID": "'.$insertID.'","data":"'.$commands.'"}',$computer['ID'],false);
				}
			$activity = "Technician Sent ".$commands." Command To: ".$computer['ID'];
			userActivity($activity,$_SESSION['userid']);
			}
			
			header("location: index.php");
		}

		//historical
		if(isset($_POST['historyDate'])){
			$_SESSION['date'] = clean($_POST['historyDate']);
			header("location: index.php");
		}

		//Get speedtest
		if($_POST['type'] == "refreshSpeedtest"){
			$ID = (int)$_POST['CompanyID'];
			MQTTpublish($ID."/Commands/getOklaSpeedtest",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),false);
			header("location: index.php");
		}
		//Save agent config
		if($_POST['type'] == "agentConfig"){
			$ID = (int)$_POST['ID'];
			$agent_Heartbeat = clean($_POST['agent_Heartbeat']);
			$agent_BIOS = clean($_POST['agent_BIOS']);
			$agent_Features = clean($_POST['agent_Features']);
			$agent_Processes = clean($_POST['agent_Processes']);
			$agent_Services = clean($_POST['agent_Services']);
			$agent_Users = clean($_POST['agent_Users']);
			$agent_Video = clean($_POST['agent_Video']);
			$agent_Disk = clean($_POST['agent_Disk']);
			$agent_Sound = clean($_POST['agent_Sound']);
			$agent_General = clean($_POST['agent_General']);

			$agent_Pointing = clean($_POST['agent_Pointing']);
			$agent_Keyboard = clean($_POST['agent_Keyboard']);
			$agent_Board = clean($_POST['agent_Board']);
			$agent_Monitor = clean($_POST['agent_Monitor']);
			$agent_Printers = clean($_POST['agent_Printers']);
			$agent_NetworkLogin = clean($_POST['agent_NetworkLogin']);
			$agent_Network = clean($_POST['agent_Network']);
			$agent_PnP = clean($_POST['agent_PnP']);
			$agent_SCSI = clean($_POST['agent_SCSI']);

			$agent_Products = clean($_POST['agent_Products']);
			$agent_Processor = clean($_POST['agent_Processor']);
			$agent_Firewall = clean($_POST['agent_Firewall']);
			$agent_Agent = clean($_POST['agent_Agent']);
			$agent_Battery = clean($_POST['agent_Battery']);
			$agent_Filesystem = clean($_POST['agent_Filesystem']);
			$agent_Mapped = clean($_POST['agent_Mapped']);
			$agent_Memory = clean($_POST['agent_Memory']);
			$agent_Startup = clean($_POST['agent_Startup']);
			$agent_Logs = clean($_POST['agent_Logs']);
			$agent_SharedDrives = clean($_POST['agent_SharedDrives']);

			$settings='{"interval": {"SharedDrives": '.$agent_SharedDrives.',"Heartbeat": '.$agent_Heartbeat.', "getGeneral": '.$agent_General.', "getBIOS": '.$agent_BIOS.', "getStartup": '.$agent_Startup.', "getOptionalFeatures": '.$agent_Features.', "getProcesses": '.$agent_Processes.', "getServices": '.$agent_Services.', "getUsers": '.$agent_Users.', "getVideoConfiguration": '.$agent_Video.', "getLogicalDisk": '.$agent_Disk.', "getMappedLogicalDisk": '.$agent_Mapped.', "getPhysicalMemory": '.$agent_Memory.', "getPointingDevice": '.$agent_Pointing.', "getKeyboard": '.$agent_Keyboard.', "getBaseBoard": '.$agent_Board.', "getDesktopMonitor": '.$agent_Monitor.', "getPrinters": '.$agent_Printers.', "getNetworkLoginProfile": '.$agent_NetworkLogin.', "getNetwork": '.$agent_Network.', "getPnPEntitys": '.$agent_PnP.', "getSoundDevices": '.$agent_Sound.', "getSCSIController": '.$agent_SCSI.', "getProducts": '.$agent_Products.', "getProcessor": '.$agent_Processor.', "getFirewall": '.$agent_Firewall.', "getAgent": '.$agent_Agent.', "getBattery": '.$agent_Battery.', "getFilesystem": '.$agent_Filesystem.', "getEventLogs": '.$agent_Logs.'}}';
			$query = "UPDATE computerdata SET agent_settings='".$settings."' WHERE ID=".$ID.";";
			//$results = mysqli_query($db, $query);
			//echo mysqli_error($db)."sadsada"; exit;
			MQTTpublish($ID."/Commands/setAgentSettings",$settings,getSalt(20),true);
			header("location: index.php");
		}
		//Update Company Agents
		if($_POST['type'] == "CompanyUpdateAll"){
			$ID = (int)$_POST['CompanyID'];
			$commands = "C:\\\\Open_RMM\\\\Update.bat";
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
					$query = "INSERT INTO commands (ComputerID, userid, command,  expire_after, expire_time, status)
							  VALUES ('".$computer['hostname']."', '".$_SESSION['userid']."', '".$commands."', '".$expire_after."', '".$expire_time."', 'Sent')";
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
			header("location: index.php");
		}
		//Get Site Settings
		if($_POST['type'] == "getSiteSettings"){
			exit(file_get_contents("includes/config.php"));
		}
		if($_POST['type'] == "saveSiteSettings"){
			$settings = "<?php \$siteSettingsJson = '".$siteSettingsJson."';";
			$configFile = "includes/config.php";
			file_put_contents($configFile, $settings);
			exit();
		}
		//login
		if(isset($_POST['loginusername'], $_POST['password'])){
			$count=0;
			$username = $_POST['loginusername'];
			$password = $_POST['password'];
			$query = "SELECT * FROM users where active='1' and username='".$username."'";
			$results= mysqli_query($db, $query);
			$count = mysqli_num_rows($results);
			$data = mysqli_fetch_assoc($results);
            //echo mysqli_error($db);exit;
			$dbPassword= $data['password'];
            $dbPassword=crypto('decrypt', $data['password'], $data['hex']);
			if($password!==$dbPassword or $dbPassword=="")$count=0;
				//echo $password." ".$dbPassword;
				if($count>0){
					$query = "UPDATE users SET last_login='".time()."' WHERE ID=".$data['ID'].";";
					$results = mysqli_query($db, $query);
					$_SESSION['userid']=$data['ID'];
					$_SESSION['username']=$data['username'];
					$activity="Technician Logged In";
					userActivity($activity,$data['ID']);
					
					$_SESSION['accountType']=$data['accountType'];	
					$_SESSION['showModal']="true";	
					$_SESSION['recent']=explode(",",$data['recents']);
					if($data['recents']==""){ $_SESSION['recent']=array(); }
					$_SESSION['recentedit']=explode(",",$data['recentedit']);
					if($data['recentedit']==""){ $_SESSION['recentedit']=array(); }
					
			    	header("location: index.php");
				}else{
					$_SESSION['loginMessage'] = "Incorrect Login Details";
					header("location: index.php");
				}
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
			$uploaddir = 'includes/agentFiles/bin/';
			$uploaddir2 = 'includes/update/Open_RMM.exe';
			$uploadfile = $uploaddir.$_FILES['agentUpload']['name'];
			$uploadfile2 = "includes/agentFiles/bin/Open_RMM.exe";
			if($company==""){
				move_uploaded_file($_FILES['agentUpload']['tmp_name'], $uploadfile);
				copy($uploadfile2, $uploaddir2);
			}
			ini_set('max_execution_time', 600);
			ini_set('memory_limit','1024M');
			$myfile = fopen("includes/agentFiles/company.txt", "w") or die("Unable to open file!");
			fwrite($myfile, $company);
			echo $rootPath = realpath('includes/agentFiles/');
			$zip = new ZipArchive();
			$zip->open('Open_RMM('.$agentVersion.').zip', ZipArchive::CREATE | ZipArchive::OVERWRITE );
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
			foreach ($files as $name => $file){
				if (!$file->isDir()){
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath, strlen($rootPath) + 1);
					$zip->addFile($filePath, $relativePath);
				}
			}
			$zip->close();
			copy("Open_RMM(".$agentVersion.").zip", "downloads/Open_RMM(".$agentVersion.").zip");
			unlink("Open_RMM(".$agentVersion.").zip");
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