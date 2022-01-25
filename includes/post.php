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
            header("location: /");
        }
		
		//custom command
		if(isset($_POST['customCommand'])){
			if($cmdButtons==""){ $_SESSION['customCommands']=array(); }else{
				//$_SESSION['customCommands']=$cmdButtons;
			}
			$title = clean($_POST['title']);
			$color = clean($_POST['btnColor']);
			$command = clean($_POST['customCommand']);
			$data = $title."(||)".$color."(||)".$command;
			if (in_array( $data, $_SESSION['customCommands'])){
				if (($key = array_search($data, $_SESSION['customCommands'])) !== false) {
					unset($_SESSION['customCommands'][$key]);
				}
				array_push($_SESSION['customCommands'], $data);
				$query = "UPDATE users SET Command_Buttons='".implode("{|}", $_SESSION['customCommands'])."' WHERE ID=".$_SESSION['userid'].";";
				$results = mysqli_query($db, $query);
			}else{
				if(end($_SESSION['customCommands']) != $data){
				
					array_push($_SESSION['customCommands'], $data);
				
					$query = "UPDATE users SET Command_Buttons='".implode("{|}", $_SESSION['customCommands'])."' WHERE ID=".$_SESSION['userid'].";";
					$results = mysqli_query($db, $query);
				}
			}
			header("location: /");
		}
        //init.php
        if($_POST['type'] == "init"){
            $mysqlHost = clean($_POST['mysqlHost']);
            $mysqlPort = clean($_POST['mysqlPort']);
            $mysqlUsername = clean($_POST['mysqlUsername']);
            $mysqlPassword = clean($_POST['mysqlPassword']);
            $mysqlDatabase = clean($_POST['mysqlDatabase']);
			$rand = random_bytes(64); // chiper = AES-256-CBC ? 32 : 16
			$agentSecret=base64_encode($rand);
			$mqttHost = clean($_POST['mqttHost']);
            $mqttPort = clean($_POST['mqttPort']);
            $mqttUsername = clean($_POST['mqttUsername']);
            $mqttPassword = clean($_POST['mqttPassword']);

			$encryptionSecret = base64_encode(getSalt(40));
            $encryptionSalt = base64_encode(getSalt(40));
           
            include("includes/config_init.php");
           
            $data = $siteSettingsJson;
			$data = str_replace("[agentSecret]",$agentSecret,$data);
            $data = str_replace("[mysqlHost]",$mysqlHost,$data);
            $data = str_replace("[mysqlPort]",$mysqlPort,$data);
            $data = str_replace("[mysqlUsername]",$mysqlUsername,$data);
            $data = str_replace("[mysqlPassword]",$mysqlPassword,$data);
            $data = str_replace("[mysqlDatabase]",$mysqlDatabase,$data);

            $data = str_replace("[mqttHost]",$mqttHost,$data);
            $data = str_replace("[mqttPort]",$mqttPort,$data);
            $data = str_replace("[mqttUsername]",$mqttUsername,$data);
            $data = str_replace("[mqttPassword]",$mqttPassword,$data);

			$data = str_replace("[secret]",$encryptionSecret,$data);
			$data = str_replace("[salt]",$encryptionSalt,$data);

            unlink("includes/config.php");
            $_SESSION['excludedPages'] = explode(",",$excludedPages);
            file_put_contents("includes/config.php","<?php \$siteSettingsJson = '".$data."';");
            header("location: /");
        }
        //edit asset
        if($_POST['type'] == "EditComputer"){
			$ID = (int)$_POST['ID'];
			
			$query = "SELECT ID, hex FROM computers WHERE ID='".$ID."' LIMIT 1";
			$results = mysqli_query($db, $query);
			$result = mysqli_fetch_assoc($results);
			$salt=$result['hex'];
			if($salt==""){ $salt = getSalt(40); }
			$name = crypto('encrypt', clean($_POST['name']),$salt);
			$comment = crypto('encrypt', clean($_POST['comment']),$salt);
			$phone = crypto('encrypt', clean($_POST['phone']),$salt);
			$company = clean($_POST['company']);
			$type = clean($_POST['pctype']);
			$email = crypto('encrypt',strip_tags($_POST['email']),$salt);
			$show_alerts = (int)$_POST['show_alerts'];
			//Edit Recents
			$activity = "Asset ".$ID." Edited";
			userActivity($activity,$_SESSION['userid']);
			$query = "UPDATE users SET recent_edit='".implode(",", $_SESSION['recentedit'])."' WHERE ID=".$_SESSION['userid'].";";
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
			$query = "UPDATE computers SET hex='".$salt."', show_alerts='".$show_alerts."', computer_type='".$type."', comment='".$comment."', name='".$name."', phone='".$phone."', company_id='".$company."', email='".$email."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			header("location: /");
		}
		//Delete computer on edit.php
		if($_POST['type'] == "DeleteComputer"){
			$ID = (int)$_POST['ID'];
			$hostname = clean($_POST['hostname']);
			if($ID > 0){
				$query = "UPDATE computers SET active='0' WHERE ID='".$ID."';";
				$results = mysqli_query($db, $query);
				$query = "DELETE FROM wmidata WHERE computer_id='".$ID."';";
				$results = mysqli_query($db, $query);
				$activity = "Asset: ".$ID." Deleted";
				userActivity($activity,$_SESSION['userid']);
				header("location: /");
			}
    	}
		//new ticket
		if($_POST['type'] == "NewTicket"){
			$ID = (int)$_POST['ID'];
			$title = clean($_POST['title']);
			$description = clean($_POST['description']);
			$requester = clean($_POST['requester']);
			$category = clean($_POST['category']);
			$subcategory = clean($_POST['subcategory']);
			$assigned = (int)clean($_POST['assigned']);
			$priority = clean($_POST['priority']);
			$due = clean($_POST['due']);
			$cc = clean($_POST['cc']);
			$asset = (int)clean($_POST['asset']);
			$tags = clean($_POST['tags']);
			$customer = (int)clean($_POST['company']);
			
			$query = "INSERT INTO tickets (tags,user_id, title, description, requester, category, subcategory, assignee, priority, due, cc, computer_id, company_id)
			VALUES ('".$tags."','".$_SESSION['userid']."','".$title."','".$description."','".$requester."', '".$category."','".$subcategory."','".$assigned."','".$priority."','".$due."','".$cc."','".$asset."','".$customer."')";
			$results = mysqli_query($db, $query);
		//	echo mysqli_error($db); exit;
			$ID = mysqli_insert_id($db);
			$activity = "Ticket: ".$ID." Created";
			userActivity($activity,$_SESSION['userid']);
		
			header("location: /");
			
    	}	
		if($_POST['type']=="updateTicket"){
			$ID = (int)$_POST['ID'];
			$type = strtolower(clean($_POST['tkttype']));
			$data = clean($_POST['tktdata']);		
			$query = "UPDATE tickets SET ".$type."='".$data."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			header("location: /");
		}
		//ticket message
		if(isset($_POST['message'])){
			$ID = (int)$_POST['ID'];
			$message = clean($_POST['message']);
			$type = clean($_POST['messageType']);
			$query = "INSERT INTO ticket_messages (ticket_id, user_id, message, type)
			VALUES ('".$ID."','".$_SESSION['userid']."','".$message."','".$type."')";
			$results = mysqli_query($db, $query);
		//	echo mysqli_error($db); exit;
			$activity = "Message Sent On Ticket: ".$ID;
			userActivity($activity,$_SESSION['userid']);		
			header("location: /");				
		}

		//asset_message
		if($_POST['type']=="asset_message"){
			$ID = (int)$_POST['ID'];
			
				$user = (int)$_POST['user_id'];
				$message = clean($_POST['message']);
				$query = "INSERT INTO asset_messages (computer_id, userid, message)
				VALUES ('".$ID."','".$user."','".$message."')";
				$results = mysqli_query($db, $query);
			//	echo mysqli_error($db); exit;
			if($ID!=0){
				$activity = "Message Sent On Asset: ".$ID;
				userActivity($activity,$_SESSION['userid']);
			}		
			header("location: /");				
		}

		//asset_message
		if($_POST['type']=="asset_viewed"){
			$ID = (int)$_POST['ID'];
			if($ID!=0){
				$query = "UPDATE asset_messages SET chat_viewed='1' WHERE computer_id='".$ID."';";
				$results = mysqli_query($db, $query);
				$activity = "Messages for asset ".$ID." marked as read";
				userActivity($activity,$_SESSION['userid']);
			}		
			header("location: /");				
		}

		//Add Computers To Company
		if($_POST['type'] == "CompanyComputers"){
			$computers = ($_POST['computers']);
			$companies = clean($_POST['companies']);
			$companyID=(int)$_POST['companyID'];

			foreach($computers as $computer) {
				$query = "UPDATE computers SET company_id='".$companyID."' WHERE ID='".(int)$computer."';";
				$results = mysqli_query($db, $query);
			}
			header("location: /");
		}

		//Delete Selected assets
		if($_POST['type'] == "deleteAssets"){
			$computers = ($_POST['computers']);
			foreach($computers as $computer) {
				$query = "UPDATE computers SET active='0' WHERE ID='".(int)$computer."';";
				$results = mysqli_query($db, $query);
			}
			header("location: /");
		}
		//general settings
		if($_POST['type'] == "initGeneral"){
			$msp = '"MSP": "'.clean($_POST['msp']).'"';
			$serviceDesk = '"Service_Desk": "'.clean($_POST['serviceDesk']).'"';

			$data = $siteSettingsJson;
			$data = str_replace('"Service_Desk": "'.$siteSettings['Service_Desk'].'"',$serviceDesk,$data);
			$data = str_replace('"MSP": "'.$siteSettings['theme']['MSP'].'"',$msp,$data);    

            unlink("includes/config.php");
            $_SESSION['excludedPages'] = explode(",",$excludedPages);
            file_put_contents("includes/config.php","<?php \$siteSettingsJson = '".$data."';");

			header("location: /");
		}
		//Add Edit/User
		if($_POST['type'] == "AddEditUser"){
			if(isset($_POST['username'])){
				$accountType = ucwords(clean($_POST['accountType']));
				
				$user_ID = (int)$_POST['ID'];
				if($user_ID == 0){
					$salt = getSalt(40);
					$settings = crypto('encrypt', implode(",",$_POST['settings']), $salt);
				}else{
					$query = "SELECT password, hex, account_type FROM users WHERE ID='".$user_ID."' LIMIT 1";
					$results = mysqli_query($db, $query);
					$result = mysqli_fetch_assoc($results);
					$salt=$result['hex'];
					if($accountType=="Admin"){
						$settings =  crypto('encrypt', implode(",",$allPages).",AssetChat", $salt);
					}else{
						$settings = crypto('encrypt', implode(",",$_POST['settings']), $salt);
					}
				}
				$username = clean($_POST['username']);
				$name2 = clean($_POST['name']);
				$color = clean($_POST['color']);
				$name = crypto('encrypt', $name2, $salt);
				$phone = clean($_POST['phone']);
				$accountType = ucwords(clean($_POST['accountType']));
				$email = crypto('encrypt', strip_tags($_POST['email']), $salt);
				$password = clean($_POST['password']);
				$password2 = clean($_POST['password2']);
				
				
				$encryptedPhone = $encryptedPhone = crypto('encrypt', $phone, $salt);
				$encryptedPassword = password_hash($password, PASSWORD_DEFAULT);
				if($_SESSION['accountType']!="Admin"){  
					//$type="Standard";
				}
				$type = crypto('encrypt', $accountType, $salt);
				if($password == $password2){
					if($user_ID == 0){
						if($password==""){
							$active="0";
						}else{
							$active="1";
						}
						
						$query = "INSERT INTO users (allowed_pages,active,user_color,account_type, phone, username, password, hex, nicename , email)
								  VALUES ('".$settings."','".$active."','".$color."','".$type."','".$encryptedPhone."','".$username."', '".$encryptedPassword."','".$salt."','".$name."','".$email."')";
                                 
						$activity = "New technician: ".ucwords($name)." created";
						userActivity($activity,$_SESSION['userid']);
					}else{
					
						if($password==""){
							$encryptedPassword = $result['password'];
						}
						$query = "UPDATE users SET allowed_pages='".$settings."',user_color='".$color."',account_type='".$type."',phone='".$encryptedPhone."',username='".$username."',nicename='".$name."', email='".$email."', password='".$encryptedPassword."', hex='".$salt."' WHERE ID='".$user_ID."'";
						$activity = "Technician ".ucwords($name2)." Edited";
						userActivity($activity,$_SESSION['userid']);
					}
					if(!$results = mysqli_query($db, $query)){  }
					echo '<script>window.onload = function() { pageAlert("Technician Settings", "Technician settings changed successfully.","Success"); };</script>';
				}else{ //passwords do not match
					echo '<script>window.onload = function() { pageAlert("Technician Settings", "Password change failed, passwords do not match.","Danger"); };</script>';
				}
				//header("location: /");
			}
		}
		//delete note
		if(isset($_POST['delNote'])){
			$delnote=(int)$_POST['delNote'];
			$query = "UPDATE users SET notes='' WHERE ID='".$_SESSION['userid']."';";
			$results = mysqli_query($db, $query);			
			$activity="All Notes Deleted";		
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}
		//delete user activity
		if(isset($_POST['delActivity'])){
			$delActivity=(int)$_POST['delActivity'];
			$query = "UPDATE user_activity SET active='0' WHERE user_id='".$delActivity."';";
			$results = mysqli_query($db, $query);
			if($delActivity!=$_SESSION['userid']){
				$activity="Activity Logs Deleted";		
				userActivity($activity,$_SESSION['userid']);
			}
			$activity="Admin Deleted All Activity Logs";		
			userActivity($activity,$delActivity);
			header("location: /");
		}

		//delete task
		if($_POST['type'] == "delTask"){
			$del=(int)$_POST['ID'];
			$query = "UPDATE tasks SET active='0' WHERE ID='".$del."';";
			$results = mysqli_query($db, $query);
			$activity="Task ID: ".$del." Deleted";		
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}

		//delete alert
		if($_POST['type'] == "delAlert"){
			$del=(int)$_POST['ID'];
			$query = "UPDATE alerts SET active='0' WHERE ID='".$del."';";
			$results = mysqli_query($db, $query);
			$activity="Alert ID: ".$del." Deleted";		
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}

		//Create task
		if($_POST['type'] == "newTask"){
			$name=clean($_POST['name']);
			$taskAct1=clean($_POST['taskAct1']);
			$taskAct1Message=clean($_POST['taskAct1Message']);

			$taskDetails='{"Name":"'.$name.'","Conditions":{';
			for ($x = 1; $x <= $taskCondtion_max; $x++) {
				$task = "taskCond".$x;
				$taskDetails.= '"'.$x.'": {"Type":"'.clean($_POST[$task]).'","Comparison":"'.clean($_POST[$task.'Comparison']).'","';
				$taskDetails .= 'Value":"'.clean($_POST[$task.'Value']).'"},';
			}
			$taskDetails = rtrim($taskDetails,",");
			$taskDetails.= '},"Actions":{"Type":"'.$taskAct1.'","Argument":"'.$taskAct1Message.'"}}';
			$query = "INSERT INTO tasks (user_id,name,details)VALUES ('".$_SESSION['userid']."','".$name."','".$taskDetails."')";
  			$results = mysqli_query($db, $query);
			 // echo mysqli_error($db); exit;
			$activity="Task: ".mysqli_insert_id($db)." Created";		
			userActivity($activity,$_SESSION['userid']);	
			header("location: /");
		}

		//Create alert
		if($_POST['type'] == "newAlert"){
			$name=clean($_POST['name']);
			$id=clean((int)$_POST['ID']);
			$alertComparison=($_POST['alertComparison']);
			$alertCondition=clean($_POST['alertCondition']);
			$alertValue=clean($_POST['alertValue']);
			$alertCompany=clean((int)$_POST['alertCompany']);
			$details='"Condition":"'.$alertCondition.'","Comparison":"'.$alertComparison.'","Value":"'.$alertValue.'"';
			$alertDetails='{"Name":"'.$name.'","Company":"'.$alertCompany.'","Details":{'.$details.'}}';
			
			$query = "INSERT INTO alerts (computer_id,company_id,user_id,name,details)VALUES ('".$id."','".$alertCompany."','".$_SESSION['userid']."','".$name."','".$alertDetails."')";
  			$results = mysqli_query($db, $query);
			//echo mysqli_error($db); exit;
			$activity="Alert: ".mysqli_insert_id($db)." Create";		
			userActivity($activity,$_SESSION['userid']);	
			header("location: /");
		}

		//Oneway asset message
		if($_POST['type'] == "assetOneWayMessage"){
			$ID=clean($_POST['ID']);
			$message=clean($_POST['alertMessage']);
			$title=clean($_POST['alertTitle']);
			$type=clean($_POST['alertType']);
			$script = '{"userID":"'.$_SESSION['userid'].'","data": {"Title": "'.$title.'", "Message": "'.$message.'", "Type":"'.$type.'"}}';
			MQTTpublish($ID."/Commands/set_alert",$script,$ID,false);	
			$activity="Asset: ".$ID." Was Sent A One-way Message";		
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}
		//Add Edit/Company
		if($_POST['type'] == "AddEditCompany"){
			if(isset($_POST['name'], $_POST['phone'], $_POST['address'], $_POST['email'])){
				$ID = (int)$_POST['ID'];
				$salt = getSalt(40);
				$name2 = clean($_POST['name']);
				$name = crypto('encrypt', $name2, $salt);
				$owner = crypto('encrypt', clean($_POST['owner']), $salt);
				$phone = clean($_POST['phone']);
				$phone = crypto('encrypt', $phone, $salt);
				$address = clean($_POST['address']);
				$address = crypto('encrypt', $address, $salt);
				$comments = clean($_POST['comments']);
				$comments = crypto('encrypt', $comments, $salt);
				$email = crypto('encrypt', clean($_POST['email']), $salt);
				$query = "SELECT ID, default_agent_settings FROM general WHERE ID='1'";
				$results = mysqli_query($db, $query);
				$computer = mysqli_fetch_assoc($results);
				$settings=$computer['default_agent_settings'];
				if($ID == 0){
					$query = "INSERT INTO companies (owner,default_agent_settings,hex,name, phone, address, comments, email)
							  VALUES ('".$owner."','".$settings."','".$salt."','".$name."', '".$phone."', '".$address."', '".$comments."', '".$email."')";
					$activity = "Company: ".$name." Added";
					userActivity($activity,$_SESSION['userid']);
				}else{
					$query = "UPDATE companies SET owner='".$owner."',hex='".$salt."',name='".$name."', phone='".$phone."', address='".$address."', email='".$email."', comments='".$comments."'
							  WHERE ID='".$ID."' LIMIT 1";
					$activity = "Company: ".$name2." Edited";
					userActivity($activity,$_SESSION['userid']);
				}
				$results = mysqli_query($db, $query);
				
				header("location: /");
			}
		}
		//Delete Company
		if($_POST['type'] == "DeleteCompany"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['companyactive'];
			$query = "UPDATE companies SET active='".$active."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			if($active=="1"){
				$action=' enabled';
			}else{
				$action=' disabled';
			}
			$activity = "Company: ".$ID.$action;
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}		
		//Disable TFA
		if($_POST['type'] == "DisableTFA"){
			if($_SESSION['accountType']=="Admin"){
				$ID = (int)$_POST['ID'];
			}else{
				$ID = $_SESSION['userid'];
			}
			$query = "UPDATE users SET tfa_secret='' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			header("location: /");
		}
		//Delete User
		if($_POST['type'] == "DeleteUser"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['useractive'];
			$query = "UPDATE users SET active='".$active."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			if($active=="1"){
				$action=' enabled';
			}else{
				$action=' disabled';
			}
			$activity = "Technician: ".$ID.$action;
			userActivity($activity,$_SESSION['userid']);			
			header("location: /");
		}
		//Delete Server
		if($_POST['type'] == "deleteServer"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['action'];
			$query = "UPDATE servers SET active='".$active."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			if($active=="1"){
				$action=' enabled';
			}else{
				$action=' disabled';
			}
			$activity = "Server: ".$ID.$action;
			userActivity($activity,$_SESSION['userid']);			
			header("location: /");
		}
		if($_POST['twofaCode']!=""){
			$ID = $_POST['twofaCode'];
			$tfaSecret = $_SESSION['tfaSecret'];
			if ($tfa->verifyCode($_SESSION['tfaSecret'], $ID) === true) {
				$query = "UPDATE users SET tfa_secret='".$tfaSecret."' WHERE ID='".$_SESSION['userid']."';";
				$results = mysqli_query($db, $query);
				$_SESSION['sitenotification']="Two Factor Authentication has been enabled";
			}else{
				$_SESSION['sitenotification']="Authenticator code did not match";				
			}
			header("location: /");
		}
		//server status
		if($_POST['type'] == "serverStatus"){
			$ID = (int)$_POST['ID'];
			$active = clean($_POST['action']);
			if($active=="restart"){
				$action=' restarted server';
			}elseif($active=="shutdown"){
				$action=' shutdown server';
			}elseif($active=="restart service"){
				$action=' restarted server service';
			}elseif($active=="stop service"){
				$action=' stopped server service';
			}elseif($active=="update service"){
				$action=' updated server service';
				$active="update";
			}else{
				$active="";
			}
			MQTTpublish($ID."/Server/Command",'{"userID":'.$_SESSION['userid'].',"payload":"'.$active.'"}',$ID,false);
			$activity = "Server: ".$ID.$action;
			userActivity($activity,$_SESSION['userid']);			
			header("location: /");
		}
		//Delete Command
		if($_POST['type'] == "DeleteCommand"){
			$ID = (int)$_POST['ID'];
			$active = (int)$_POST['commandactive'];
			$activity = "Command: ".$ID." Deleted";
			userActivity($activity,$_SESSION['userid']);
			$query = "UPDATE commands SET status='Deleted' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);
			$activity = "Command ".$ID." Deleted";
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}
		//Create Note
		if(isset($_POST['note'])){			
			$ID=(int)$_SESSION['userid'];
			$salt = getSalt(40);
			$activity = "Note Created";
			$newnote = clean($_POST['note']);
			$noteTitle = clean($_POST['noteTitle']);
			$query = "SELECT notes,hex FROM users WHERE ID='".$ID."'";
			$results = mysqli_query($db, $query);
			$oldnote = mysqli_fetch_assoc($results);
			$note = crypto('decrypt',$oldnote['notes'],$oldnote['hex']).$noteTitle."^".$newnote."|";
			$note = crypto('encrypt', $note, $oldnote['hex']);
			$query = "UPDATE users SET notes='".$note."' WHERE ID='".$ID."';";
			$results = mysqli_query($db, $query);		
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}
		//Commands
		if($_POST['type'] == "SendCommand"){
			$ID = (int)$_POST['ID'];
			$commands = clean($_POST['command']);
			$expire_after = (int)$_POST['expire_after'];
			$exists = 0;
			if(trim($commands)!=""){
				$query = "SELECT ID FROM computers WHERE ID='".$ID."'";
				$results = mysqli_query($db, $query);
				$computer = mysqli_fetch_assoc($results);
				$query = "SELECT ID, expire_time FROM commands WHERE computer_id='".$computer['ID']."' AND status='Sent' AND user_id='".$_SESSION['userid']."' ORDER BY ID DESC LIMIT 1";
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
					$salt=getSalt(40);
					$query = "INSERT INTO commands (hex,computer_id, user_id, command, expire_after, expire_time, status)
							  VALUES ('".$salt."','".$computer['ID']."', '".$_SESSION['userid']."', '".crypto('encrypt', $commands, $salt)."', '".$expire_after."', '".$expire_time."', 'Sent')";
					$results = mysqli_query($db, $query);
					$insertID = mysqli_insert_id($db);
					MQTTpublish($computer['ID']."/Commands/CMD",'{"userID":'.$_SESSION['userid'].',"commandID": "'.$insertID.'","data":"'.$commands.'"}',$computer['ID'],false);
				}
			$activity = "Command ".$commands." Was Sent To ".$computer['ID'];
			userActivity($activity,$_SESSION['userid']);
			}			
			header("location: /");
		}


		//Get speedtest
		if($_POST['type'] == "refreshSpeedtest"){
			$ID = (int)$_POST['computer_id'];
			MQTTpublish($ID."/Commands/get_okla_speedtest",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),false);
			header("location: /");
		}
		//restart/stop agent
		if($_POST['type'] == "agentStatus"){
			$ID = (int)$_POST['ID'];
			$action = clean($_POST['action']);
			MQTTpublish($ID."/Commands/act_".$action."_agent",'{"userID":'.$_SESSION['userid'].'}',getSalt(20),false);
			$activity = "Agent ".$ID." ".$action."ed";
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}

		//Save agent config
		if($_POST['type'] == "agentConfig"){
			$ID = (int)$_POST['ID'];
			
			$attribute = clean($_POST['attribute']);
			$attributes = $attribute."_raw";
			$json = getComputerData($ID, array("$attribute"));
			$_SESSION['raw_data_value_raw']=$json[$attributes];
			$_SESSION['raw_data_value']=$json[$attribute];
			$_SESSION['raw_data_title']=$attribute;


			$autoUpdates = (int)$_POST['autoUpdate'];
			$updateURL = clean($_POST['updateURL']);
			$updateInterval = (int)$_POST['updateInterval'];

			$getWMI = array("agent_settings");
			$json = getComputerData($ID, $getWMI);
			$agent_settings = $json['agent_settings']['Response']["Interval"];
			$settings='{"Interval": {';
			$count=0;
			foreach ($agent_settings as $setting => $val) {
				$value = (int)$_POST["agent_$setting"];
				if($count>0){
					$settings .= ',';
				}
				$settings .= '"'.$setting.'": '.$value;
				$count++;
			}
			$settings.='},"Updates":{"auto_update":'.$autoUpdates.', "update_url":"'.$updateURL.'", "check_interval":'.$updateInterval.'}';
			$settings .= '}';
			MQTTpublish($ID."/Commands/set_agent_settings",$settings,getSalt(20),false);
			sleep(2);
			$activity = "Agent configuration updated";
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}
		
		if($_POST['type'] == "assetChat_typing"){
			$ID = (int)$_POST['ID'];
			$user = (int)$_POST['userid'];
			$typing =  (int)$_POST['is_typing'];
			$query = "UPDATE asset_messages SET is_typing='".$typing."' WHERE computer_id='".$ID."' and userid='".$user."';";
			$results = mysqli_query($db, $query);
			header("location: /");
		}
		//Save company config
		if($_POST['type'] == "defaultAgentConfig"){
			$ID = (int)$_POST['ID'];
			$autoUpdate = (int)$_POST['defaultAutoUpdate'];
			$updateInterval = (int)$_POST['defaultUpdateInterval'];
			$updateURL = clean($_POST['defaultUpdateURL']);

			$query = "SELECT ID, default_agent_settings FROM general WHERE ID='1'";
			$results = mysqli_query($db, $query);
			$computer = mysqli_fetch_assoc($results);

			$agent_settings = json_decode($computer['default_agent_settings'],true)['Interval'];
			$settings='{"Interval": {';
			$count=0;
			foreach ($agent_settings as $setting => $val) {
				$value = (int)$_POST["agent_$setting"];
				if($count>0){
					$settings .= ',';
				}
				$settings .= '"'.$setting.'": '.$value;
				$count++;
			}
			$settings.='},"Updates":{"auto_update":'.$autoUpdate.', "update_url":"'.$updateURL.'", "check_interval":'.$updateInterval.'}';
			$settings .= '}';
			if($ID==0){
				$query = "UPDATE general SET default_agent_settings='".$settings."' WHERE ID='1';";
			}else{
				$query = "UPDATE companies SET default_agent_settings='".$settings."' WHERE ID='".$ID."';";
			}
			$results = mysqli_query($db, $query);
			$activity = $msp." ".$ID." agent configuration updated";
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}

		//Update Company Agents
		if($_POST['type'] == "CompanyUpdateAll"){
			$ID = (int)$_POST['ID'];
			$query = "SELECT ID, online FROM computers WHERE company_id='".$ID."' AND active='1'";
			$results = mysqli_query($db, $query);			
			while($computer = mysqli_fetch_assoc($results)){
				$getWMI = array("agent_settings","agent");			
				$json = getComputerData($computer['ID'], $getWMI);
				$old = preg_replace('/\D/', '', $json['agent']['Response'][0]['Version']);
				$new = preg_replace('/\D/', '', $siteSettings['general']['agent_latest_version']);
				if($old != $new){
					$message='{"userID":'.$_SESSION['userid'].'}';
					MQTTpublish($computer['ID']."/Commands/act_update_agent",$message,getSalt(20),false);
					$activity = "All ".strtolower($msp)." ".$ID." agents updated";
					userActivity($activity,$_SESSION['userid']);
				}
			}
			header("location: /");
		}
		//Delete Version
		if(isset($_POST['version'])){
			$version=clean($_POST['version']);
			unlink("downloads/".$version);
			$activity = "Agent Version ".$version." Deleted";
			userActivity($activity,$_SESSION['userid']);
			header("location: /");
		}
		if($_POST['type'] == "updateAgent"){
			$ID = (int)$_POST['ID'];
			$message='{"userID":'.$_SESSION['userid'].'}';
			MQTTpublish($ID."/Commands/act_update_agent",$message,getSalt(20),false);
			$activity = "Agent ".$ID." updated";
			userActivity($activity,$_SESSION['userid']);
		}
		//2fa verify
		if(isset($_POST['loginusername'], $_POST['tfaLoginpassword'])){
			$code = clean($_POST['tfaLoginpassword']);
			$username = clean($_POST['loginusername']);
			$query = "SELECT * FROM users where active='1' and username='".$username."'";
			$results= mysqli_query($db, $query);
			$data = mysqli_fetch_assoc($results);

			if($tfa->verifyCode($data['tfa_secret'], $code) === true) {
				$_SESSION['userid']=$data['ID'];
				$_SESSION['username']=$data['username'];
				$activity="Logged In";
				userActivity($activity,$data['ID']);
				
				$_SESSION['accountType']= crypto('decrypt', $data['account_type'] , $data['hex']);;
				$_SESSION['showModal']="true";	
				$_SESSION['recent']=explode(",",$data['recents']);
				if($data['recents']==""){ $_SESSION['recent']=array(); }
				$_SESSION['customCommands']=explode("{|}",$data['Command_Buttons']);
				if($data['Command_Buttons']==""){ $_SESSION['customCommands']=array(); }
				$_SESSION['recentTickets']=explode(",",$data['recentTickets']);
				if($data['recentTickets']==""){ $_SESSION['recentTickets']=array(); }
				$_SESSION['recentedit']=explode(",",$data['recent_edit']);
				if($data['recent_edit']==""){ $_SESSION['recentedit']=array(); }
			}else{
				$_SESSION['loginMessage'] = "Incorrect Two Factor Authentication Details";
			}
			header("location: /");
		}
		//login
		if(isset($_POST['loginusername'], $_POST['password'])){
			$username = clean($_POST['loginusername']);
			$password = clean($_POST['password']);
			$_SESSION['loginusername']="";
			$query = "SELECT * FROM users where active='1' and username='".$username."'";
			$results= mysqli_query($db, $query);
			$count = mysqli_num_rows($results);
			$data = mysqli_fetch_assoc($results);
			$dbPassword= $data['password'];
			$_SESSION['tfa_pass']="";
			if(password_verify($password,$dbPassword)) { 
				if($data['tfa_secret']!=""){
					$_SESSION['tfa_pass']="false";
					$_SESSION['loginusername'] = $username;
				}else{
					$_SESSION['tfa_pass']="true";
				}
				if($_SESSION['tfa_pass']=="true"){
					$_SESSION['userid']=$data['ID'];
					$_SESSION['username']=$data['username'];
					$activity="Logged In";
					userActivity($activity,$data['ID']);
					
					$_SESSION['accountType']= crypto('decrypt', $data['account_type'] , $data['hex']);;
					$_SESSION['showModal']="true";	
					$_SESSION['recent']=explode(",",$data['recents']);
					if($data['recents']==""){ $_SESSION['recent']=array(); }
					$_SESSION['customCommands']=explode("{|}",$data['Command_Buttons']);
					if($data['Command_Buttons']==""){ $_SESSION['customCommands']=array(); }
					$_SESSION['recentTickets']=explode(",",$data['recentTickets']);
					if($data['recentTickets']==""){ $_SESSION['recentTickets']=array(); }
					$_SESSION['recentedit']=explode(",",$data['recent_edit']);
					if($data['recent_edit']==""){ $_SESSION['recentedit']=array(); }
				}
			    header("location: /");
			}else{
				$_SESSION['loginusername'] = $username;

				$_SESSION['loginMessage'] = "Incorrect Login Details";
				header("location: /");
			}
		}
		//Upload or download new agent file
		if(isset($_POST['agentFile']) or isset($_POST['companyAgent'])){
			$agentVersion = clean($_POST['agentVersion']);
			if($agentVersion==""){
				$agentVersion= $siteSettings['general']['agent_latest_version'];
			}else{
				$activity = "Latest Agent Version Number ".$agentVersion." Updated";
				userActivity($activity,$_SESSION['userid']);	
			}
			$company = $_POST['companyAgent'];
			$updateURL = clean($_POST['updateURL']);
			
			$uploaddir = 'includes/agentFiles/Source/';
			$uploaddir2 = 'includes/update/Open_RMM.exe';
			$uploadfile = $uploaddir.$_FILES['agentUpload']['name'];
			$uploadfile2 = "includes/agentFiles/Source/Open_RMM.exe";
			if($updateURL!=""){
				$url = json_decode($siteSettings['general']['default_agent_settings'],true)['Updates']['update_url'];
				$py = file_get_contents($url);
				$myfile = fopen($uploaddir."OpenRMM.py", "w") or die("Unable to open file!");
				fwrite($myfile, $py);
				fclose($myfile);
			}
			if($company==""){
				move_uploaded_file($_FILES['agentUpload']['tmp_name'], $uploadfile);
				copy($uploadfile2, $uploaddir2);
			}
			ini_set('max_execution_time', 600);
			ini_set('memory_limit','1024M');
			$myfile = fopen("includes/agentFiles/OpenRMM.json", "w") or die("Unable to open file!");
			$data = '{"MQTT": {"Server": "'.$siteSettings['MQTT']['host'].'", "Username": "'.$siteSettings['MQTT']['username'].'", "Password": "'.$siteSettings['MQTT']['password'].'", "Port": '.$siteSettings['MQTT']['port'].'}}'; 
			fwrite($myfile, $data);
			$rootPath = realpath('includes/agentFiles/');
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
			$activity = "Agent ".$agentVersion." Downloaded";
			userActivity($activity,$_SESSION['userid']);
			if($company==""){
				$query = "UPDATE general SET agent_latest_version='".$agentVersion."' WHERE ID='1';";
				$results = mysqli_query($db, $query);
				$activity = "Agent File Uploaded";
				userActivity($activity,$_SESSION['userid']);
				if($uploadfile==""){
					echo '<script>window.onload = function() { pageAlert("File Upload", "File Uploaded Successfully","Success"); };</script>';
				}else{
					echo '<script>window.onload = function() { pageAlert("File Upload", "Agent Version Updated Successfully","Success"); };</script>';
				}
			}else{
				$activity = $msp." ".$company." Agent Files Configured";
				userActivity($activity,$_SESSION['userid']);
				echo '<script>window.onload = function() { pageAlert("File Upload", "Download Started For Customer Agent","Default"); };</script>';
				header("location: ../../download//?company=".$company);
			}
		}

		//needs tested, then combined and strings need cleaned
		if($_POST['fs_act_type']=="rename"){
			$path = $_POST['filepath'];
			$filename = $_POST['fileFolder'];
			$id = (int)$_POST['ID'];
			$commands = "rename ".$filename." ".$path;
			MQTTpublish($id."/Commands/CMD",'{"userID":'.$_SESSION['userid'].',"commandID": "'.$insertID.'","data":"'.$commands.'"}',$id,false);
		}
		if($_POST['fs_act_type']=="move"){ 
			$path = $_POST['filepath'];
			$filename = $_POST['fileFolder'];
			$id = (int)$_POST['ID'];
			$commands = "move ".$filename." ".$path;
			MQTTpublish($id."/Commands/CMD",'{"userID":'.$_SESSION['userid'].',"commandID": "'.$insertID.'","data":"'.$commands.'"}',$id,false);
		}
		if($_POST['fs_act_type']=="copy"){
			$path = $_POST['filepath'];
			$filename = $_POST['fileFolder'];
			$id = (int)$_POST['ID'];
			$commands = "copy ".$filename." ".$path;
			MQTTpublish($id."/Commands/CMD",'{"userID":'.$_SESSION['userid'].',"commandID": "'.$insertID.'","data":"'.$commands.'"}',$id,false);
		}
		if($_POST['fs_act_type']=="delete"){

		}