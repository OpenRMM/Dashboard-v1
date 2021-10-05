<?php
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
    //Oneway asset message
    if($_POST['type'] == "assetOneWayMessage"){
        $ID=clean($_POST['ID']);
        $message=clean($_POST['assetMessage']);
        MQTTpublish($ID."/Commands/showAlert",$message,$ID);	
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
        $active = (int)$_POST['companyactive'];
        $query = "UPDATE companies SET active='".$active."' WHERE CompanyID='".$ID."';";
        $results = mysqli_query($db, $query);
        $activity = "Technician Deleted A Company: ".$ID;
        userActivity($activity,$_SESSION['userid']);
        header("location: index.php?page=AllCompanies");
    }
    //Delete User
    if($_POST['type'] == "DeleteUser"){
        $ID = (int)$_POST['ID'];
        $active = (int)$_POST['useractive'];
        $query = "UPDATE users SET active='".$active."' WHERE ID='".$ID."';";
        $results = mysqli_query($db, $query);
        $activity = "Technician Deleted A Technician: ".$ID;
        userActivity($activity,$_SESSION['userid']);			
        header("location: index.php?page=AllUsers");
    }
    //Delete Command
    if($_POST['type'] == "DeleteCommand"){
        $ID = $_POST['ID'];
        $active = (int)$_POST['commandactive'];
        $activity = "Technician Deleted A Command: ".$ID;
        userActivity($activity,$_SESSION['userid']);
        $query = "UPDATE commands SET command='Deleted' WHERE ID='".$ID."';";
        $results = mysqli_query($db, $query);
        header("location: index.php?page=Commands");
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
                MQTTpublish($existing['ID']."/Commands/CMD",$commands,$existing['ID']);
                $query = "INSERT INTO commands (ComputerID, userid, command, expire_after, expire_time, status)
                            VALUES ('".$computer['hostname']."', '".$_SESSION['userid']."', '".$commands."', '".$expire_after."', '".$expire_time."', 'Sent')";
                $results = mysqli_query($db, $query);
            }
        }
        $activity = "Technician Sent ".$commands." Command To: ".$ID;
        userActivity($activity,$_SESSION['userid']);
        header("location: index.php?page=General");
    }
    //Update Company Agents
    if($_POST['type'] == "CompanyUpdateAll"){
        $ID = (int)$_POST['CompanyID'];
        $commands = "C:\\\\SMG_RMM\\\\Update.bat";
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
        header("location: index.php?page=Versions");
    }
    //Get Site Settings
    if($_POST['type'] == "getSiteSettings"){
        exit(file_get_contents("Includes/config.php"));
    }
    if($_POST['type'] == "saveSiteSettings"){
        $settings = "<?php \$siteSettingsJson = '".$siteSettingsJson."';";
        $configFile = "Includes/config.php";
        file_put_contents($configFile, $settings);
        exit();
    }
    //login
    if(isset($_POST['username'], $_POST['password'])){
        $count=0;
        $username = stripslashes(mysqli_escape_string($db, clean(strip_tags(trim($_POST['username'])))));
        $password = stripslashes(clean(mysqli_escape_string($db, strip_tags(trim($_POST['password'])))));
        $query = "SELECT * FROM users where active='1' and username='".$username."'";
        $results= mysqli_query($db, $query);
        $count = mysqli_num_rows($results);
        $data = mysqli_fetch_assoc($results);
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