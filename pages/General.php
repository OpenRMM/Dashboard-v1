<?php 
	if($_SESSION['userid']==""){ 
?>
	<script>		
		toastr.error('Session timed out.');
		setTimeout(function(){
			setCookie("section", "Login", 365);	
			window.location.replace("../index.php");
		}, 3000);		
	</script>
<?php 
		exit("<center><h5>Session timed out. You will be redirected to the login page in just a moment.</h5><br><h6>Redirecting</h6></center>");
	}
	$computerID = (int)$_GET['ID'];
	$showDate = $_SESSION['date'];
	if($computerID<0){ 
		?>
		<br>
		<center>
			<h4>No Computer Selected</h4>
			<p>
				To Select A Computer, Please Visit The <a class='text-dark' style="cursor:pointer" onclick='loadSection("Assets");'><u>Assets page</u></a>
			</p>
		</center>
		<hr>
		<?php
		exit;
	}
	
	$query = "SELECT teamviewer, online, ID, hostname, CompanyID, name, phone, email, computer_type FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$result = mysqli_fetch_assoc($results);

	//get update
	//MQTTpublish($computerID."/Commands/getOklaSpeedtest","true",getSalt(20),false);
	MQTTpublish($computerID."/Commands/getScreenshot","true",getSalt(20),false);


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
	
	$online = $result['online'];
	$lastPing = $json['Ping'];
	
	if($online=="0") {
		//$alert = "This Computer Is Currently Offline";
		//$alertType = "danger";
	}

	//log user activity
	$activity = "Technician Viewed Asset: ".$result['hostname'];
	userActivity($activity,$_SESSION['userid']);		
?>
<style>
	.dataTables_info {margin-top:40px; }
</style>
<h4 style="color:#333">
	Overview of <?php echo $result['hostname']; ?>
		<a href="javascript:void(0)" title="Edit Asset" onclick="loadSection('Edit');" style="position:absolute;padding-left:15px;font-size:22px">
			<i class="fas fa-pencil-alt"></i>
		</a>

	<br>	
	<?php if($showDate != "latest"){?>
		<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			History: <?php echo date("l, F jS", strtotime($showDate));?>
		</span>
	<?php }else{ ?>
		<span style="font-size:12px;color:#333"> Last Updated: <?php echo ago($lastPing);?></span>
	<?php } ?>
	<div class="" style="margin-top:-45px">
		<center>
			<?php $alertCount = count($json['Alerts']);?>
			<?php if($alertCount > 0){?>
				<button onclick="computerAlertsModal('This PC','<?php echo $json['Alerts_raw'];?>');" data-toggle="modal" data-target="#computerAlerts" style="margin-left:10px;" class="btn btn-sm btn-danger">	
					<i title="<?php echo $alertCount;?> Issues" class="fa fa-exclamation-triangle" aria-hidden="true"></i>
			</button>
			<?php }else{?>
				<span class="text-success" title="No Issues" data-toggle="modal" data-target="#computerAlerts" style="cursor:pointer;padding-left:15px;" onclick="computerAlertsModal('this PC');">
					<i class="fas fa-thumbs-up"></i> 
				</span>
			<?php }?>
			<?php
				$agentVersion = $json['Agent'][0]['Version'];
				if($agentVersion < $siteSettings['general']['agent_latest_version']){ ?>
					<button onclick='sendCommand("C:\\\\OpenRMM\\\\Update.bat", "Update Agent", 2);' title="Agent Update Available" style="margin-left:10px;" class="btn btn-sm btn-danger">
						<i style="color:#fff;" class="fas fa-cloud-upload-alt"></i>		
					</button>			
			<?php }?>
			
			<div style="float:right;">	
				<a href="javascript:void(0)" title="Refresh" onclick="loadSection('General');" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
					<i class="fas fa-sync"></i>
				</a>
				<?php if($_SESSION['accountType']=="Admin"){  ?>
					<a href="javascript:void(0)" title="Agent Configuration" onclick="loadSection('AgentSettings');" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
						<i class="fas fa-cogs"></i>
					</a>
				<?php } ?>
				<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
					<i class="far fa-calendar-alt"></i>
				</a>
			</div>
		</center>
	</div>
</h4>
<hr>
<?php if($alert!=""){ ?>
	<div class="row alert alert-<?php echo $alertType; ?>" role="alert">
		<b><?php echo $alert; ?></b>
	</div>
<?php } 
 if($online=="1"){ 
	$query = "SELECT * FROM screenshots WHERE ComputerID='".$computerID."' order by ID desc LIMIT 1";
	$computers = mysqli_query($db, $query);
	$computer = mysqli_fetch_assoc($computers);
	if(base64_encode($computer['image'])!=""){
		$size="3";
	}else{
		$size="4";
	}
	?>
<style>
	.zoom:hover {
		overflow:auto;
		z-index:9999; 
		background-size: cover;		
	}
	.zoom2:hover {
		transform: scale(2.5);
		z-index:9999; 	
	}
</style>
<div class="row py-2">
	<?php if($size=="3"){ ?>
	<div class="col-md-3 py-1">
        <div style="padding:0px;cursor:zoom-in;overflow:hidden;height:58%" class="zoom2 card shadow-md">
            <img class="zoom" style="background-position: 50% 50%; background-size: 100vw" src="data:image/jpeg;base64,<?php echo base64_encode($computer['image']);?>"/>              
        </div>
    </div>
	<?php } ?>
    <div class="col-md-<?php echo $size; ?> py-1">
        <div class="card">
            <div class="card-body">
                <canvas data-centerval="37.2%" id="chDonut2"></canvas>
                <h6 style="text-align:center">CPU Usage</h6>
            </div>
        </div>
    </div>
    <div class="col-md-<?php echo $size; ?> py-1">
        <div class="card">
            <div class="card-body">
                <canvas data-centerval="37.1%" id="chDonut1"></canvas>
                <h6 style="text-align:center">RAM Usage</h6>
            </div>
        </div>
    </div>
    <div class="col-md-<?php echo $size; ?> py-1">
        <div class="card">
            <div class="card-body">
				<?php
				//Determine Warning Level
					$freeSpace = $json['WMI_LogicalDisk'][0]['FreeSpace'];
					$size2 = $json['WMI_LogicalDisk'][0]['Size'];
					$used = $size2 - $freeSpace;
					$usedPct = round(($used/$size2) * 100);
					if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
						$pbColor = "red";
					}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
						$pbColor = "#ffa500";
					}else{ $pbColor = "#03925e"; }
					$left = 100 - $usedPct;
				?>
                <canvas data-centerval="<?php echo $usedPct;?>%" id="chDonut3"></canvas>
                <h6 style="text-align:center">Disk Usage</h6>
            </div>
        </div>
    </div>
 <?php } ?>
</div>
<div <?php if($size=="3"){ echo 'style="margin-top:-10%"'; } ?> class="row">
	<div class="col-xs-6 col-sm-6 col-md-3 col-lg-4" style="padding:5px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5  style="padding:7px" class="panel-title">
					Asset Overview
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">
				<div class="rsow">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<a href="javascript:void(0)" style="color:<?php echo $siteSettings['theme']['Color 5']; ?>" data-toggle="modal" data-target="#companyMoreInfo">
							<h5>
								<?php echo ($result['name']!="" ? ucwords($result['name'])." at" : ""); ?>
								<?php echo textOnNull(($company['name']!="N/A" ? $company['name'] : ""), "No ".$msp." Name"); ?>
							</h5>
						</a>
						<span style="color:#666;font-size:14px;"><?php echo textOnNull(phone($result['phone']), "No ".$msp." Phone"); ?> &bull;
							<a href="mailto:<?php echo $result['email']; ?>">
								<?php echo textOnNull(phone($result['email']), "No ".$msp." Email"); ?>
							</a>
						</span>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align:center;"><hr>
						<?php if($online=="1"){ ?>
							<button class="btn btn-danger btn-sm" onclick='sendCommand("shutdown -s -t 0", "Shutdown Computer");' style="width:40%;margin:3px;">
								<i class="fas fa-power-off"></i> Shutdown
							</button>
							<button class="btn btn-warning btn-sm" onclick='sendCommand("shutdown -r -t 0", "Reboot Computer");' style="width:40%;margin:3px;color:#000;background:#ffa500;border:#ffa500">
								<i class="fas fa-redo"></i> Reboot
							</button><br>
						
						<?php } ?>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align:center;">
						<?php if($online=="1"){ ?>
								<button class="btn btn-sm btn-warning" data-dismiss="modal" type="button" style="background:#0ac282;border:#0ac282;margin:3px;width:50%;" data-toggle="modal" data-target="#agentMessageModal">
									<i class="fas fa-comment" style=""></i> One-way Message
								</button>
								<button class="btn btn-sm" onclick='$("#terminaltxt").delay(3000).focus();' type="button" style="width:30%;margin:3px;color:#fff;background:#333;" data-dismiss="modal" data-toggle="modal" data-target="#terminalModal">
									<i class="fas fa-terminal"></i> Terminal
								</button>
						<?php } ?>
					</div>
				</div>
		  </div>
		</div>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5  style="padding:7px" class="panel-title">
					Hardware Details
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">	
				<div class="roaw">
					<ul class="list-group" style="margin-left:20px">
						<li class="list-group-item" style="padding:6px"><b>Processor: </b><?php echo textOnNull(str_replace("(R)","",str_replace("(TM)","",$json['WMI_Processor'][0]['Name'])), "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>Operating System: </b><?php echo textOnNull(str_replace("Microsoft", "", $json['WMI_ComputerSystem'][0]['Caption']), "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>Architecture: </b><?php echo textOnNull($json['WMI_ComputerSystem'][0]['SystemType'], "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>BIOS Version: </b><?php echo textOnNull($json['WMI_BIOS'][0]['Version'], "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>Public IP Address: </b><?php echo textOnNull($json['WMI_ComputerSystem'][0]['ExternalIP']["ip"], "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><span style="margin-left:0px"><b>Local IP Address: </b><?php echo textOnNull($json['WMI_ComputerSystem'][0]['InternalIP'], "N/A");?></span></li>
						<?php if((int)$json['WMI_Battery'][0]['BatteryStatus']>0){ ?>
						<li class="list-group-item" style="padding:6px"><b>Battery Status: </b><?php 								
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
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 style="padding:7px" class="panel-title">
					Asset Details
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">
				<div class="rsow">
					<ul class="list-group" style="margin-left:20px">
						<li class="list-group-item" style="padding:6px"><b>Current User: </b><?php echo textOnNull(basename($json['WMI_ComputerSystem'][0]['UserName']), "Unknown");?></li>
						<li class="list-group-item" style="padding:6px"><b>Domain: </b><?php echo textOnNull($json['WMI_ComputerSystem'][0]['Domain'], "N/A");?></li>
						<?php
							$lastBoot = explode(".", $json['WMI_ComputerSystem'][0]['LastBootUpTime'])[0];
							$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
						?>
						<li class="list-group-item" style="padding:6px"><b>Uptime: </b><?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
						<?php if(count($json['WMI_Firewall']) > 0) {

							$public = $json['WMI_Firewall'][0]['publicProfile'];
							if($public=="OFF"){ $public="Disabled"; }else{ $public="Enabled"; }
							$color1 = (($public == "Enabled") ? "text-success" : "text-danger");

							$private = $json['WMI_Firewall'][0]['privateProfile'];
							if($private=="OFF"){ $private="Disabled"; }else{ $private="Enabled"; }
							$color2 = (($private == "Enabled") ? "text-success" : "text-danger");

							$domain = $json['WMI_Firewall'][0]['domainProfile'];
							if($domain=="OFF"){ $domain="Disabled"; }else{ $domain="Enabled"; }
							$color3 = (($domain == "Enabled") ? "text-success" : "text-danger");
						?>
							<li class="list-group-item" style="padding:6px"><b>Firewall Status: </b><br>
								<center>
									<span style="margin-left:40px">Public: <span style="padding-right:20px" class="<?php echo $color1; ?>"><?php echo $public; ?></span></span>
									Private: <span style="padding-right:20px" class="<?php echo $color2; ?>"><?php echo $private; ?></span>
									Domain: <span class="<?php echo $color3; ?>"><?php echo $domain; ?></span>
								</center>
							
							</li>
						<?php } 
						if(count($json['WindowsActivation']) > 0) {
							$status = $json['WindowsActivation']['Value'];
							$color = ($status == "Activated" ? "text-success" : "text-danger");
						?>
							<li class="list-group-item" style="padding:6px"><b>Windows Activation: </b><span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
						<?php } 
						if(count($json['Antivirus']) > 0) {
							$status = $json['Antivirus']['Value'];
							$color = ($status == "No Antivirus" ? "text-danger" : "text-success");
						?>
							<li class="list-group-item" style="padding:6px"><b>Antivirus: </b><span title="<?php echo textOnNull($status, "N/A"); ?>" class="<?php echo $color; ?>"><?php echo mb_strimwidth(textOnNull($status, "N/A"), 0, 30, "...");?></span></li>
						<?php } ?>
						<li class="list-group-item" title="Path: <?php echo $json['Agent'][0]['Path']; ?>" style="padding:6px"><b>Agent Version: </b><?php echo $json['Agent'][0]['Version']; ?></li>
					</ul>
				</div>
		  </div>
		</div>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 style="padding:7px" class="panel-title">
					Geolocation Details
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">
				<div class="row">
					<?php $loc = $json['WMI_ComputerSystem'][0]['ExternalIP']["loc"]; ?>
					<div style="width: 100%">
						<iframe width="100%" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=<?php echo $loc; ?>&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
							<a href="http://www.gps.ie/">vehicle gps</a>
						</iframe>
					</div>
				</div>
		  </div>
		</div>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 style="padding:7px" class="panel-title">
					Agent Error Log
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">
				<div class="row">
					<table id="datsaTable" style="width:125%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover table-borderless">
						<thead>
							<tr style="border-bottom:2px solid #d3d3d3;">
							<th scope="col">Title</th>
							<th scope="col">Details</th>
							</tr>
						</thead>
						<tbody>			
							<tr>
								<td colspan=2><center><h6>No Logs Found</h6></center></td>
								<td>&nbsp;</td>
							</tr>					
						</tbody>
					</table>
				</div>
		  	</div>
		</div>
	</div>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 style="padding:7px" class="panel-title">
					Speedtest
				</h5>
			</div>
			<div class="panel-body" style="background:#1D1D35;height:285px;">
				<div class="row">
					<a target="_blank" href="<?php echo str_replace(".png","",$json['OklaSpeedtest']['share']); ?>">
						<form method="post" action="index.php">
						<?php if($json['OklaSpeedtest']['share']!=""){ ?>
							<center><img width="80%" style="margin-top:-10px" height="80%" src="<?php echo $json['OklaSpeedtest']['share']; ?>"/></center>
						<?php }else{ echo "<center><h5 style='padding:30px;color:#fff'>Refresh to get the latest Speedtest</h5></center><br><br>"; } ?>
							<input type="hidden" value="refreshSpeedtest" name="type">
							<input type="hidden" value="<?php echo $computerID; ?>" name="CompanyID">
							<center>
								<button class="btn btn-md btn-secondary" style="width:95%;bottom:0" type="submit">Refresh Results</button>
							</center>
						</form>
					</a>
				</div>
		 	 </div>
		</div>
	</div>
</div>
<!-------------------------------MODALS------------------------------------>
<div id="companyMoreInfo" class="modal fade" role="dialog">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<h4 class="modal-title"><?php if($company['name']!="N/A"){ echo textOnNull($company['name'], "No ".$msp." Info"); }else{ echo $msp." Information";} ?></h4>
	  </div>
	  <div class="modal-body">
		<ul class="list-group">
			<li class="list-group-item">
				<b>Phone:</b>
				<?php echo textOnNull(phone($company['phone']), "No ".$msp." Phone"); ?>
			</li>
			<li class="list-group-item">
				<b>Email:</b>
				<a href="mailto:<?php echo $company['email']; ?>">
					<?php echo textOnNull($company['email'], "No ".$msp." Email"); ?>
				</a>
			</li>
			<li class="list-group-item">
				<b>Address:</b>
				<?php echo textOnNull($company['address'], "No ".$msp." Address"); ?>
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
		<button type="button" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;color:#fff;" data-dismiss="modal">Close</button>
	  </div>
	</div>
  </div>
</div>
<!-- onne way message -->
<div id="agentMessageModal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">One-way Message to Agent: <?php echo $_SESSION['ComputerHostname']; ?></h5>
			</div>
			<form method="post" action="index.php">
				<div class="modal-body">
					<input type="hidden" name="type" value="assetOneWayMessage"/>
					<input type="hidden" name="ID" value="<?php echo $computerID; ?>">
					<div class="form-group">
						<label>Title</label>
						<input type="text" id="#inputTitle" class="form-control" name="alertTitle"/>
					</div>
					<div class="form-group">
						<textarea id="inputMessage" placeholder="Your message here..." name="alertMessage" class="form-control"></textarea>
					</div>
					<center>
						<label class="radio-inline">
							<input type="radio" id="#inputType" class="form-control" name="alertType" value="alert" checked>Alert
						</label>
						<label class="radio-inline">
							<input type="radio" id="#inputType" class="form-control" name="alertType" value="confirm" >Confirm
						</label>
						<label class="radio-inline">
							<input type="radio" id="#inputType" class="form-control" name="alertType" value="password" >Password
						</label>
						<label class="radio-inline">
							<input type="radio" id="#inputType" class="form-control" name="alertType" value="prompt" >Prompt
						</label>
					<center>
				</div>
				<div class="modal-footer">
					<button type="button" onclick='sendMessage()' data-dismiss="modal" class="btn btn-primary btn-sm">
						Send <i class="fas fa-paper-plane" ></i>
					</button>
					<button type="button" class="btn btn-sm btn-default"  data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	function sendMessage(){  
		var type = $("input[name='alertType']:checked").val();
		$("#inputTitle").val();
		$("#inputMessage").val();
		$.post("index.php", {
		type: "assetOneWayMessage",
		ID: computerID,
		alertType: alertType,
		alertTitle: alertTitle,
		alertMessage: alertMessage,
		},
		function(data, status){
			toastr.options.progressBar = true;
			toastr.success("Your Message Has Been Sent");
		});  
	}
	$(".sidebarComputerName").text("<?php echo strtoupper($result['hostname']);?>");
	var data = {
	  labels: [
	    "Ram Usage","Unused"
	  ],
	  datasets: [
	    {
	      data: [20,80],
	      backgroundColor: [
	        "<?php echo $siteSettings['theme']['Color 2']; ?>"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
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
	        "<?php echo $siteSettings['theme']['Color 3']; ?>"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
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
	        "<?php echo $siteSettings['theme']['Color 5']; ?>"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
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
<script>
	<?php if($online=="0"){ ?>
		toastr.remove()
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>