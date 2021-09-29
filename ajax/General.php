<?php
	include("../Includes/db.php");
	
	$computerID = (int)$_GET['ID'];
	$showDate = $_GET['Date'];
	$query = "SELECT teamviewer, ID, hostname, CompanyID, name, phone, email, computerType FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$result = mysqli_fetch_assoc($results);
	
	$query = "SELECT name, phone, email,address,comments,date_added FROM companies WHERE CompanyID='".$result['CompanyID']."' LIMIT 1";
	$companies = mysqli_query($db, $query);
	$company = mysqli_fetch_assoc($companies);
	
	$json = getComputerData($computerID, array("*"), $showDate);
	
	//Update Recents
	if (in_array( $computerID, $_SESSION['recent'])){
		if (($key = array_search($computerID, $_SESSION['recent'])) !== false) {
			unset($_SESSION['recent'][$key]);
		}
		array_push($_SESSION['recent'], $result['ID']);
		$query = "UPDATE users SET recents='".implode(",", $_SESSION['recent'])."' WHERE ID=".$_SESSION['userid'].";";
		$results = mysqli_query($db, $query);
	}else{
		if(end($_SESSION['recent']) != $computerID){
			array_push($_SESSION['recent'], $result['ID']);
			$query = "UPDATE users SET recents='".implode(",", $_SESSION['recent'])."' WHERE ID=".$_SESSION['userid'].";";
			$results = mysqli_query($db, $query);
		}
	}
	if($result['hostname']==""){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='index.php'><u>Dashboard</u></a></p></center><hr>"); }
	
	$online = $json['Online'];
	$lastPing = $json['Ping'];
	
	if(!$online) {
		$alert = "This Computer Is Currently Offline";
		$alertType = "danger";
	}
	$hostname = textOnNull(strtoupper($result['hostname']),"No Device Selected");
	
	//realtimedatamode
	$exists = 0;
	$query = "SELECT ID, expire_time FROM commands WHERE ComputerID='".$result['hostname']."' AND status='Sent' AND command='realtimedatamode' AND userid='".$_SESSION['userid']."' ORDER BY ID DESC LIMIT 1";
	$results = mysqli_query($db, $query);
	$existing = mysqli_fetch_assoc($results);
	if($existing['ID'] != ""){
		if(strtotime(date("m/d/Y H:i:s")) <= strtotime($existing['expire_time'])){
			$exists = 1;
		}
	}
	if($exists == 0){
		//Generate expire time
		$expire_after = 2;
		$expire_time = date("m/d/Y H:i:s", strtotime('+'.$expire_after.' minutes', strtotime(date("m/d/y H:i:s"))));
		$query = "INSERT INTO commands (ComputerID, userid, command, arg, expire_after, expire_time, status)
				  VALUES ('".$result['hostname']."', '".$_SESSION['userid']."', 'realtimedatamode', '', '".$expire_after."', '".$expire_time."', 'Sent')";
		$results = mysqli_query($db, $query);
	}
	//log user activity
	$activity = "Technician Viewed Asset: ".$result['hostname'];
	userActivity($activity,$_SESSION['userid']);
		
?>
<style>
	.dataTables_info {margin-top:40px; }
</style>
<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
	Overview of <?php echo $result['hostname']; ?> <span style="font-size:10px;color:#333"> Last Updated: <?php echo ago($lastPing);?></span>
	<?php $alertCount = count($json['Alerts']);?>
	<?php if($alertCount > 0){?>
		<span title="<?php echo $alertCount;?> Issues" class="text-danger" data-toggle="modal" data-target="#computerAlerts" style="cursor:pointer;padding-left:15px;" onclick="computerAlertsModal('This PC','<?php echo $json['Alerts_raw'];?>');">
			<i title="<?php echo $alertCount;?> Issues" class="fa fa-exclamation-triangle" aria-hidden="true"></i>
		</span>
	<?php }else{?>
		<span class="text-success" title="No Issues" data-toggle="modal" data-target="#computerAlerts" style="cursor:pointer;padding-left:15px;" onclick="computerAlertsModal('this PC');">
			<i class="fas fa-thumbs-up"></i> 
		</span>
	<?php }?>
	<?php if($showDate != "latest"){?>
		<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			History: <?php echo date("l, F jS", strtotime($showDate));?>
		</span>
	<?php }?>
	<div style="float:right;">	
		<a href="#" title="Refresh" onclick="loadSection('General');" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Edit" onclick="loadSection('Edit');" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-pencil-alt"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</h4>
<hr>
<?php if($alert!=""){ ?>
	<div class="row alert alert-<?php echo $alertType; ?>" role="alert">
		<b><?php echo $alert; ?></b>
	</div>
<?php } 
 if($online){ ?>
<div class="row py-2">
    <div class="col-md-4 py-1">
        <div class="card">
            <div class="card-body">
                <canvas data-centerval="37.2%" id="chDonut2"></canvas>
                <h6 style="text-align:center">CPU Usage</h6>
            </div>
        </div>
    </div>
    <div class="col-md-4 py-1">
        <div class="card">
            <div class="card-body">
                <canvas data-centerval="37.1%" id="chDonut1"></canvas>
                <h6 style="text-align:center">RAM Usage</h6>
            </div>
        </div>
    </div>
    <div class="col-md-4 py-1">
        <div class="card">
            <div class="card-body">
				<?php
				//Determine Warning Level
					$freeSpace = $json['WMI_LogicalDisk'][0]['FreeSpace'];
					$size = $json['WMI_LogicalDisk'][0]['Size'];
					$used = $size - $freeSpace;
					$usedPct = round(($used/$size) * 100);
					if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
						$pbColor = "red";
					}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
						$pbColor = "#ffa500";
					}else{ $pbColor = $siteSettings['theme']['Color 4']; }
					$left = 100 - $usedPct;
				?>
                <canvas data-centerval="<?php echo $usedPct;?>%" id="chDonut3"></canvas>
                <h6 style="text-align:center">Disk Usage</h6>
            </div>
        </div>
    </div>
 <?php } ?>
</div>
<div class="row">
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:5px;">
		<div class="card" style="height:85%;">
		  <div class="card-body" style="padding:15px;">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
					<h4>
						<?php echo ($result['name']!="" ? ucwords($result['name'])."<span style='font-size:14px;color:#696969'> with</span>" : ""); ?>
						<a href="#" style="color:<?php echo $siteSettings['theme']['Color 5']; ?>" data-toggle="modal" data-target="#companyMoreInfo">
							<?php echo textOnNull(($company['name']!="N/A" ? $company['name'] : ""), "No Company Info"); ?>
						</a>
					</h4>
					<span style="color:#666;font-size:14px;"><?php echo textOnNull(phone($result['phone']), "No Company Phone"); ?> &bull;
						<a href="mailto:<?php echo $result['email']; ?>">
							<?php echo textOnNull(phone($result['email']), "No Company Email"); ?>
						</a>
					</span>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align:center;"><hr>
					<?php if($online){ ?>
					<button class="btn btn-danger btn-sm" onclick='sendCommand("shutdown", "-s -t 30", "Shutdown Computer");' style="width:30%;margin:3px;">
						<i class="fas fa-power-off"></i> Shutdown
					</button>
					<button class="btn btn-warning btn-sm" onclick='sendCommand("shutdown", "-r -t 30", "Reboot Computer");' style="width:30%;margin:3px;color:#000;background:#ffa500;">
						<i class="fas fa-redo"></i> Reboot
					</button>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align:center;">
					<button class="btn btn-sm" type="button" style="width:30%;margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-dismiss="modal" data-toggle="modal" data-target="#terminalModal">
						<i class="fas fa-terminal"></i> Terminal
					</button>
					<?php } ?>
					<?php if(trim($result['teamviewer']) != ""){ ?>
					<a target="_BLANK" href="https://start.teamviewer.com/device/<?php echo $result['teamviewer'];?>/authorization/password/mode/control" class="btn btn-sm" style="width:30%;margin:3px;color:#fff;background:#dedede;" title="<?php echo $result['teamviewer'];?>">
						<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/TeamViewer_logo.svg/800px-TeamViewer_logo.svg.png" height="16px;"/>
					</a>
					<?php } ?>
				</div>
			</div>
		  </div>
		</div>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="card" style="height:85%;">
		  <div class="card-body" style="padding:5px;">
			<h5>Hardware Information</h5>
			<div class="row">
				<ul style="margin-left:30px">
					<li>Computer Type: <?php echo textOnNull($result['computerType'], "Not Set");?></li>
					<li>Processor: <?php echo textOnNull(str_replace("(R)","",str_replace("(TM)","",$json['WMI_Processor'][0]['Name'])), "N/A");?></li>
					<li>Operating System: <?php echo textOnNull(str_replace("Microsoft", "", $json['WMI_OS'][0]['Caption']), "N/A");?></li>
					<li>Architecture: <?php echo textOnNull($json['WMI_ComputerSystem'][0]['SystemType'], "N/A");?></li>
					<li>Asset Model: <?php echo textOnNull($json['WMI_ComputerSystem'][0]['Manufacturer']." ".$json['WMI_ComputerSystem'][0]['Model'], "N/A");?></li>
					<li>BIOS Version: <?php echo textOnNull($json['WMI_BIOS'][0]['Version'], "N/A");?></li>
					<li>Local IP Address: <?php echo textOnNull($json['IPAddress']['Value'], "N/A");?></li>
					<li></li>
					<?php if((int)$json['WMI_Battery'][0]['BatteryStatus']>0){ ?>
					<li>Battery Status: <?php 								
						$statusArray = [
						"1" => ["Text" => "Discharging", "Color" => "red"],
						"2" => ["Text" => "Unknown", "Color" => "red"],
						"3" => ["Text" => "Fully Charged", "Color" => "green"],
						"4" => ["Text" => "Low", "Color" => "red"],
						"5" => ["Text" => "Critical", "Color" => "red"],
						"6" => ["Text" => "Charging", "Color" => "green"],
						"7" => ["Text" => "Charging And High", "Color" => "green"],
						"8" => ["Text" => "Charging And Low", "Color" => "green"],
						"9" => ["Text" => "Charging And Critical", "Color" => "yellow"],
						"10" =>["Text" => "Undefined", "Color" => "red"],
						"11" =>["Text" => "Partially Charged", "Color"=>"yellow"]];
						$statusInt = $json['WMI_Battery'][0]['BatteryStatus'];						
					?>
					<?php echo textOnNull($json['WMI_Battery'][0]['EstimatedChargeRemaining'], "Unknown");?>%
					(<span style="color:<?php echo $statusArray[$statusInt]['Color']; ?>"><?php echo $statusArray[$statusInt]['Text']; ?></span>)	
					</li>
					<?php } ?>
				</ul>
			</div>
		  </div>
		</div>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="card" style="height:85%;">
		  <div class="card-body" style="padding:5px;">
			<h5>Asset Details</h5>
			<div class="row">
				<ul style="margin-left:30px">
					<li>Current User: <?php echo textOnNull(basename($json['WMI_ComputerSystem'][0]['UserName']), "Unknown");?></li>
					<li>Domain: <?php echo textOnNull($json['WMI_ComputerSystem'][0]['Domain'], "N/A");?></li>
					<?php
						$lastBoot = explode(".", $json['WMI_OS'][0]['LastBootUpTime'])[0];
						$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
					?>
					<li>Asset Uptime: <?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
					<?php if(count($json['Firewall']) > 0) {
						$status = $json['Firewall']['Status'];
						$color = (($status == "True" || $status == "Enabled") ? "text-success" : "text-danger");
					?>
						<li>Firewall Status: <span class="<?php echo $color; ?>"><?php echo $status; ?></span></li>
					<?php } 
					if(count($json['WindowsActivation']) > 0) {
						$status = $json['WindowsActivation']['Value'];
						$color = ($status == "Activated" ? "text-success" : "text-danger");
					?>
						<li>Windows Activation: <span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
					<?php } 
					if(count($json['Antivirus']) > 0) {
						$status = $json['Antivirus']['Value'];
						$color = ($status == "No Antivirus" ? "text-danger" : "text-success");
					?>
						<li>Antivirus: <span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
					<?php } ?>
					<?php
					$agentVersion = $json['AgentVersion']['Value'];
					if($agentVersion < $siteSettings['general']['agent_latest_version']){ ?>
						<button class="btn-sm btn" onclick='sendCommand("C:\\\\OpenRMM\\\\Update.bat", "", "Update Agent", 2);' style="color:#fe9365;background:#fff;float:right;display:inline;margin-top:-8px;font-size:18px;margin-right:-50px" title="Update to <?php echo $siteSettings['general']['agent_latest_version'];?>">
							<i class="fas fa-cloud-upload-alt"></i>
						</button>
					<?php }?>
					<li>Agent Version: <?php echo $agentVersion; ?></li>
				</ul>
			</div>
		  </div>
		</div>
	</div>
</div>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
	<ul class="navbar-nav mr-auto nav nav-tabs" id="myTabMD" role="tablist">
	  <li style="margin-right:10px" class="nav-item">
	    <a class="btn text-white nav-link active" style="background:#2d364b;padding:5px;width:100px" id="contact-tab-md" data-toggle="tab" href="#contact-md" role="tab" aria-controls="contact-md"
	      aria-selected="false">Commands</a>
	  </li>
	  <li style="margin-right:10px" class="nav-item">
	    <a class="btn text-white nav-link" style="background:#2d364b;padding:5px;width:100px" id="home-tab-md" data-toggle="tab" href="#home-md" role="tab" aria-controls="home-md"
	      aria-selected="true">Logs</a>
	  </li>
	  <li style="margin-right:10px" class="nav-item">
	    <a class="btn text-white nav-link" style="background:#2d364b;padding:5px;width:100px" id="profile-tab-md" data-toggle="tab" href="#profile-md" role="tab" aria-controls="profile-md"
	      aria-selected="false">Monitor</a>
	  </li>	
	</ul>
</nav>
<div class="tab-content card pt-5" id="myTabContentMD">
  <div class="tab-pane fade active show" id="contact-md" style="padding:20px" role="tabpanel" aria-labelledby="contact-tab-md">
  	<h5>Commands</h5>
 	<p>View & Execute Commands On This Asset.</p>
	<hr>
	<div class="row">
		  <div style="border-right:1px solid #dedede" class="col-sm-5">
		  <h5>Execute A Command</h5>
		  <?php if($online){ ?>
			<div style="height:200px">		
				<br>			
				<button class="btn btn-sm btn-warning" data-dismiss="modal" type="button" style="margin:5px;width:45%;border:none" data-toggle="modal" data-target="#terminalModal">
					<i class="fas fa-terminal" style="margin-top:3px;float:left"></i> Terminal
				</button>
				<button data-dismiss="modal" class="btn btn-success btn-sm" type="button" style="display:inline;margin:5px;width:45%;border:none" onclick='sendCommand("reg", "add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 0 /f", "Enable Remote Desktop");'>
					<i class="fas fa-desktop" style="float:left;margin-top:3px"></i> Enable Remote Desktop
				</button>
				<button data-dismiss="modal" class="btn btn-primary btn-sm" type="button" style="display:inline;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;width:45%;border:none" onclick='sendCommand("reg", "add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 1 /f", "Disable Remote Desktop");'>
					<i class="fas fa-desktop" style="float:left;margin-top:3px"></i> Disable Remote Desktop
				</button>
				<button data-dismiss="modal" class="btn btn-primary btn-sm" type="button" style="display:inline;margin:5px;width:45%;border:none" onclick="sendCommand('Netsh', 'Advfirewall set allprofiles state on', 'Enable Firewall');">
					<i class="fas fa-fire-alt" style="float:left;margin-top:3px"></i> Enable Firewall
				</button>
				<button data-dismiss="modal" class="btn btn-primary btn-sm" type="button" style="color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;display:inline;margin:5px;color:#fff;width:45%;border:none" onclick="sendCommand('Netsh', 'Advfirewall set allprofiles state off', 'Disable Firewall');">
					<i class="fas fa-fire-alt" style="float:left;margin-top:3px"></i> Disable Firewall
				</button>
			</div>
			<br>
			<h5>Run A Custom Script</h5><br>
				<form method="post">
					<div class="form-group">
						<label for="langscript">Script Language</label>
						<select required name="scriptType" class="form-control" id="langscript">
						  <option value="0">Batch</option>
						  <option value="1">VB Script</option>
						</select>
					</div>	
					<textarea required name="customScript" class="form-control" style="width:100%;height:310px">
	:: This batch file checks for network connection problems.
	ECHO OFF
	:: View network connection details
	ipconfig /all
	:: Check if google.com is reachable
	ping google.com
	:: Run a traceroute to check the route to google.com
	tracert google.com
	PAUSE
					</textarea>
					<br>
					<button type="submit" class="btn btn-success btn-sm" style="float:right">Run Script <i class="fas fa-play"></i></button>
			</form>
			<?php }else{ ?>
				<br><br>
				<h6 style="text-align:center">Computer Must Be Online To Execute Commands</h6>
			<?php } ?>
		</div>
		<div class="col-sm-7">
			<?php 
				$query = "SELECT ID, time_received,command, arg, expire_after,status,time_sent FROM commands WHERE status='Sent' or status='Received' AND ComputerID='".$result['hostname']."' ORDER BY ID DESC LIMIT 100";
				$results = mysqli_query($db, $query);
				$commandCount = mysqli_num_rows($results);
			?>
			<table id="dataTable2" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">				
			  <thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
				  <th scope="col">Command</th>
				  <th scope="col">Argument</th>
				  <!--<th scope="col">Expire Time</th>-->
				  <th scope="col">Time Sent</th>
				  <th scope="col">Status</th>
				  <th scope="col"></th>
				</tr>
			  </thead>
			  <tbody>
				<?php
					//Fetch Results
					while($command = mysqli_fetch_assoc($results)){
						$count++;
					?>
					<tr>
					  <td><b><?php echo $command['command'];?></b></td>
					  <td><?php echo textOnNull($command['arg'],"None");?></td>
					  <!--<td><?php echo strtolower($command['expire_after']);?> Minutes</td>-->
					  <td><?php echo $command['time_sent'];?></td>
					 
						  <?php if($command['time_received']!=""){
									$timer = $command['time_received'];
							   }else{
								  $timer = "Not Received";
							   } ?>
					 
					  <td title="<?php echo $timer; ?>" ><b><?php echo $command['status'];?></b></td>
					   <td>
						   <form action="index.php" method="POST">
								<input type="hidden" name="type" value="DeleteCommand"/>
								<input type="hidden" name="ID" value="<?php echo $command['ID']; ?>"/>
									<button type="submit" title="Delete Command" style="border:none;" class="btn btn-danger btn-sm">
										<i class="fas fa-trash" ></i>
									</button>
							</form>
						</td>
					</tr>
				<?php }?>
				<?php if($count==0){ ?>
					<tr>
						<td colspan=30><center><h5>No Commands Found.</h5></center></td>
					</tr>
				<?php } ?>
			   </tbody>
			</table>
		</div>
	</div>
  </div>
  <div class="tab-pane fade " style="padding:20px" id="home-md" role="tabpanel" aria-labelledby="home-tab-md">
	<h5>Logs</h5>
	<p>View The Application Event Log For This Asset. This May Help You Diagnose Any Issues That May Occur.</p>	
	<hr>
	<div style="padding:0px;">
		<table id="dataTable" style="width:125%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover  table-borderless">
		  <thead>
			<tr style="border-bottom:2px solid #d3d3d3;">
			  <th scope="col">#</th>
			  <th scope="col">Type</th>
			  <th scope="col">Title</th>
			  <th scope="col">Details</th>
			  <th scope="col">Application</th>
			  <th scope="col">Date/Time</th>
			</tr>
		  </thead>
		  <tbody>			
				<tr>
					<td colspan=6><center><h6>No Logs Found</h6></center></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				
		   </tbody>
		</table>
	</div>
  </div>
  <div class="tab-pane fade" id="profile-md" style="padding:20px" role="tabpanel" aria-labelledby="profile-tab-md">
		<h5>Monitor</h5><hr>
		<p>Configure Notifications For This Asset.</p>	
		<form action="index.php" method="post">
		<div class="row">			
			<div class="col-sm-6" style="padding:5px;">
				<?php
				$count = 0;
				foreach($siteSettings['Alert Settings'] as $type=>$alert){
					$count++;
					if($count % 2 == 0){continue;}else{}
				?>
					<div class="card" style="margin-bottom:5px;padding:5px;">
					   <div class="form-gsroup">
						<h6><b><?php echo $type; ?>:</b></h6>
						<div style="font-size:12px" class="col-sm-offset-2 col-sm-10">
							<?php foreach($alert as $option=>$options){ ?>
								<?php if(count($options) > 1){ //Contains Sub Options?>
									<b><?php echo $option;?></b>
									<?php foreach($options as $subOptionKey=>$subOptionValue){ ?>
										<div class="checkbox" style="margin-left:15px;font-size:12px">
											<label>
												<input type="checkbox" name="alert_settings_<?php echo $type."_".$option."_".$subOptionKey;?>" value="1"> <?php echo $subOptionKey; ?>
											</label>
										</div>
									<?php }?>
								<?php }else{?>
									<div class="checkbox"style="font-size:12px">
										<label>
											<input type="checkbox" name="alert_settings_<?php echo $type."_".$option;?>" value="1"> <?php echo $option; ?>
										</label>
									</div>
								<?php }?>
							<?php }?>
						</div>
					  </div>
					</div>
				<?php } ?>
			</div>
			<div class="col-sm-6" style="padding:5px;">
				<?php
				$count = 0;
				foreach($siteSettings['Alert Settings'] as $type=>$alert){
					$count++;
					if($count % 2 == 0){  }else{continue;}
				?>
					<div class="card" style="margin-bottom:5px;padding:5px;">
					   <div class="form-gsroup">
						<h6><b><?php echo $type; ?>:</b></h6>
						<div class="col-sm-offset-2 col-sm-10">
							<?php foreach($alert as $option=>$options){ ?>
								<?php if(count($options) > 1){ //Contains Sub Options?>
									<b><?php echo $option;?></b>
									<?php foreach($options as $subOptionKey=>$subOptionValue){ ?>
										<div class="checkbox" style="margin-left:15px;font-size:12px">
											<label>
												<input type="checkbox" name="alert_settings_<?php echo $type."_".$option."_".$subOptionKey;?>" value="1"> <?php echo $subOptionKey; ?>
											</label>
										</div>
									<?php }?>
								<?php }else{?>
									<div class="checkbox" style="font-size:12px">
										<label>
											<input type="checkbox" name="alert_settings_<?php echo $type."_".$option;?>" value="1"> <?php echo $option; ?>
										</label>
									</div>
								<?php }?>
							<?php }?>
						</div>
					  </div>
					</div>
				<?php } ?>
			</div>
		</div>
		<button type="submit" style="float:right" class="btn btn-warning btn-sm">Save</button>
  </form>
  </div>
</div>
<!-------------------------------MODALS------------------------------------>
<div id="companyMoreInfo" class="modal fade" role="dialog">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<h4 class="modal-title"><?php echo textOnNull($company['name'], "No Company Info"); ?></h4>
	  </div>
	  <div class="modal-body">
		<ul class="list-group">
			<li class="list-group-item">
				<b>Phone:</b>
				<?php echo textOnNull(phone($company['phone']), "No Company Phone"); ?>
			</li>
			<li class="list-group-item">
				<b>Email:</b>
				<a href="mailto:<?php echo $company['email']; ?>">
					<?php echo textOnNull($company['email'], "No Company Email"); ?>
				</a>
			</li>
			<li class="list-group-item">
				<b>Address:</b>
				<?php echo textOnNull($company['address'], "No Company Address"); ?>
			</li>
			<li class="list-group-item">
				<b>Comments:</b><br>
				<span style="margin-left:10px;">
					<?php echo textOnNull($company['Comments'], "No Comments"); ?>
				</span>
			</li>
		</ul>
		<span style="color:#696969;float:right;font-size:10px;">
			Added <?php echo gmdate("m/d/Y\ h:i:s", $company['date_added']); ?>
		</span>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn" style="background:<?php echo $siteSettings['theme']['Color 4']; ?>;color:#fff;" data-dismiss="modal">Close</button>
	  </div>
	</div>
  </div>
</div>
	<script>
		$(".sidebarComputerName").text("<?php echo strtoupper($result['hostname']);?>");
	
	var data = {
	  labels: [
	    "Ram Usage","Unused"
	  ],
	  datasets: [
	    {
	      data: [20,80],
	      backgroundColor: [
	        "#fd7e14"
	      ],
	      hoverBackgroundColor: [
	        "#FF6384"
	      ]
	    }]
	};
	var data2 = {
	  labels: [
	    "CPU Usage","Unused"
	  ],
	  datasets: [
	    {
	      data: [72,38],
	      backgroundColor: [
	        "#007bff"
	      ],
	      hoverBackgroundColor: [
	        "#FF6384"
	      ]
	    }]
	};
	var data3 = {
	  labels: [
	    "Disk Usage","Unused"
	  ],
	  datasets: [
	    {
	      data: [<?php echo $usedPct.",".$left; ?>],
	      backgroundColor: [
	        "#20c997"
	      ],
	      hoverBackgroundColor: [
	        "#FF6384"
	      ]
	    }]
	};
	
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut1'), {
	  type: 'doughnut',
	  data: data,
	  options: {
	  	responsive: true,
	    legend: {
	      display: false
	    }
	  }
	});
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut2'), {
	  type: 'doughnut',
	  data: data2,
	  options: {
	  	responsive: true,
	    legend: {
	      display: false
	    }
	  }
	});
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut3'), {
	  type: 'doughnut',
	  data: data3,
	  options: {
	  	responsive: true,
	    legend: {
	      display: false
	    }
	  }
	});
	
	Chart.pluginService.register({
	  beforeDraw: function(chart) {
	    var width = chart.chart.width,
	        height = chart.chart.height,
	        ctx = chart.chart.ctx;
	
	    ctx.restore();
	    var fontSize = (height / 114).toFixed(2);
	    ctx.font = fontSize + "em sans-serif";
	    ctx.textBaseline = "middle";
	
			var text = $('#'+chart.canvas.id).attr('data-centerval');        
			textX = Math.round((width - ctx.measureText(text).width) / 2),
	        textY = height / 2;
	
	    ctx.fillText(text, textX, textY);
	    ctx.save();
	  }
	});
	</script>
<script>
	$(document).ready(function() {
		  $('#dataTable').DataTable();
		  $('#dataTable2').DataTable();
	});
</script>