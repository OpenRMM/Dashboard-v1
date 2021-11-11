<?php 
	if($_SESSION['userid']==""){ 
?>
	<script>		
		toastr.error('Session timed out.');
		setTimeout(function(){
			setCookie("section", btoa("Login"), 365);	
			window.location.replace("..//");
		}, 3000);		
	</script>
<?php 
		exit("<center><h5>Session timed out. You will be redirected to the login page in just a moment.</h5><br><h6>Redirecting</h6></center>");
	}
	$computerID = (int)base64_decode($_GET['ID']);
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
	
	$query = "SELECT online, ID, company_id, name, phone, email,hex, computer_type FROM computers WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$result = mysqli_fetch_assoc($results);

	$query = "SELECT name, phone, email,address,comments,date_added,hex FROM companies WHERE ID='".$result['company_id']."' LIMIT 1";
	$companies = mysqli_query($db, $query);
	$company = mysqli_fetch_assoc($companies);
	
	$json = getComputerData($computerID, array("*"), $showDate);
	$hostname = textOnNull($json['General']['Response'][0]['csname'],"Unavailable");
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
	if($result['ID']==""){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='/'><u>Dashboard</u></a></p></center><hr>"); }
	
	$online = $result['online'];
	$lastPing = $json['Ping'];
	
	//ex. 10-3=7
	$used2 = $json['General']['Response'][0]['Totalphysicalmemory'] - $json['General']['Response'][0]['FreePhysicalMemory'];
	//ex. 10-7=3
	$free2 = $json['General']['Response'][0]['Totalphysicalmemory'] - $used2;
	$total2 = $json['General']['Response'][0]['Totalphysicalmemory'];
	$average2 = round(($used2 / $total2) * 100,2);
	//echo $json['General']['Response'][0]['Totalphysicalmemory']."....".$free2."....".$used2."....".$average2;


	$cpuUsage= $json['Processor']['Response'][0]['LoadPercentage'];
	if($cpuUsage==""){
		$cpuUsage="100";
	}
	//log user activity
	$activity = "Technician Viewed Asset: ".textOnNull($json['General']['Response'][0]['csname'],"Unavailable");
	userActivity($activity,$_SESSION['userid']);		
?>
<style>
	.dataTables_info {margin-top:40px; }
</style>
<div style="padding:20px;margin-bottom:-1px;" class="card col-md-12">
	<h5>Overview of <?php echo $json['General']['Response'][0]['csname']; ?>	
		<center style="display:inline;margin-left:50px;">
			<?php $alertCount = count($json['Alerts']);?>
			<?php if($alertCount > 0){?>
				<button onclick="computerAlertsModal('This PC','<?php echo $json['Alerts_raw'];?>');" data-toggle="modal" data-target="#computerAlerts"  class="btn btn-sm btn-danger">	
					<i title="<?php echo $alertCount;?> Issues" class="fa fa-exclamation-triangle" aria-hidden="true"></i>
			</button>
			<?php } ?>
		</center>
		<div style="float:right;display:inline">			
			<div class="btn-group">
				<button onclick="loadSection('General');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="loadSection('General','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<?php if($_SESSION['accountType']=="Admin"){  ?>
				<a href="javascript:void(0)" title="Agent Configuration" onclick="loadSection('AgentSettings');" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
					<i class="fas fa-cogs"></i>
				</a>
			<?php } ?>
			<a href="javascript:void(0)" title="Edit Asset Details" class="btn btn-sm" onclick="loadSection('Edit');" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-pencil-alt"></i>
			</a>
			<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:3px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				<i class="far fa-calendar-alt"></i>
			</a>
		</div>
		<br>
		<p>	
			<?php if($showDate != "latest"){?>
				<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
					History: <?php echo date("l, F jS", strtotime($showDate));?>
				</span>
			<?php }else{ ?>
				<span style="font-size:12px;color:#333"> Last Updated: <?php echo ago($lastPing);?></span>
			<?php } ?>
		</p>
	</h5>
</div>
<?php
$agentVersion = $json['Agent']['Response'][0]['Version'];
if($agentVersion < $siteSettings['general']['agent_latest_version']){ ?>
	<?php if($agentVersion==""){?>
		<div  style="border-radius: 0px 0px 4px 4px;" class="alert alert-danger" role="alert">
			<div class="spinner-border spinner-border-sm" style="font-size:12px" role="status">
				<span class="sr-only">Loading...</span>
			</div>
			&nbsp;&nbsp;&nbsp;The agent is trying to get initial data for this asset		
		</div>
	<?php }else{ ?>
	<div onclick='sendCommand("C:\\\\OpenRMM\\\\Update.bat", "Update Agent", 2);' style="border-radius: 0px 0px 4px 4px;cursor:pointer" class="alert alert-danger" role="alert">
		<i class="fas fa-cloud-upload-alt"></i>&nbsp;&nbsp;&nbsp;An Update is available for this asset			
	</div>
	<?php } ?>
<?php }?>
<?php if($alert!=""){ ?>
	<div class="row alert alert-<?php echo $alertType; ?>" role="alert">
		<b><?php echo $alert; ?></b>
	</div>
<?php } 
 if($online=="1"){ 
	$query = "SELECT * FROM screenshots WHERE computer_id='".$computerID."' order by ID desc LIMIT 1";
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
	<div style="z-index:9999;overflow:hidden;" class="col-md-3 py-1 marginTop">
        <div style=";padding:0px;cursor:zoom-in;overflow:hidden;" class=" zoom2 card shadow-md">
            <img class="zoom" style="background-position: 50% 50%; background-size: 100vw" src="data:image/jpeg;base64,<?php echo base64_encode($computer['image']);?>"/>              
        </div>
    </div>
	<?php } ?>
    <div style="z-index:1" class=" col-md-<?php echo $size; ?> py-1">
        <div class="card">
            <div class="card-body">
                <canvas data-centerval="<?php echo $cpuUsage; ?>%" id="chDonut2"></canvas>
                <h6 style="text-align:center">CPU Usage</h6>
            </div>
        </div>
    </div>
    <div class="col-md-<?php echo $size; ?> py-1">
        <div class="card">
            <div class="card-body">
                <canvas data-centerval="<?php echo $average2; ?>%" id="chDonut1"></canvas>
                <h6 style="text-align:center">RAM Usage</h6>
            </div>
        </div>
    </div>
    <div class="col-md-<?php echo $size; ?> py-1">
        <div class="card">
            <div class="card-body">
				<?php
				//Determine Warning Level
					$freeSpace = $json['LogicalDisk']['Response'][0]['FreeSpace'];
					$size2 = $json['LogicalDisk']['Response'][0]['Size'];
					$used = $size2 - $freeSpace;
					$usedPct = round(($used/$size2) * 100);
					if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
						$pbColor = "red";
					}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
						$pbColor = "#ffa500";
					}else{ $pbColor = "#03925e"; }
					$left = 100 - $usedPct;
					if((int)$usedPct=="0"){$usedPct=100;}
					
				?>
                <canvas data-centerval="<?php echo (int)$usedPct;?>%" id="chDonut3"></canvas>
                <h6 style="text-align:center">Disk Usage</h6>
            </div>
        </div>
    </div>
</div>
<?php } ?>
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
								<?php echo crypto('decrypt',$result['name'],$result['hex'])!="" ? ucwords(crypto('decrypt',$result['name'],$result['hex']))." at" : ""; ?>
								<?php echo textOnNull((crypto('decrypt',$company['name'],$company['hex'])!="N/A" ? crypto('decrypt',$company['name'],$company['hex']) : ""), "No ".$msp." Name"); ?>
							</h5>
						</a>
						<span style="color:#666;font-size:14px;"><?php echo textOnNull(phone(crypto('decrypt',$result['phone'],$result['hex'])), "No ".$msp." Phone"); ?> &bull;
							<a href="mailto:<?php echo crypto('decrypt', $result['email'],$result['hex']); ?>">
								<?php echo textOnNull(phone(crypto('decrypt',$result['email'],$result['hex'])), "No ".$msp." Email"); ?>
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
					Asset Details
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">	
				<div class="roaw">
					<ul class="list-group" style="margin-left:20px">
						<li class="list-group-item" style="padding:6px"><b>Processor: </b><?php echo textOnNull(str_replace(" 0 ", " ",str_replace("CPU", "",str_replace("(R)","",str_replace("(TM)","",$json['Processor']['Response'][0]['Name'])))), "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>Operating System: </b><?php echo textOnNull(str_replace("Microsoft", "", $json['General']['Response'][0]['Caption']), "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>Architecture: </b><?php echo textOnNull(str_replace("PC", "",$json['General']['Response'][0]['SystemType']), "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>BIOS Version: </b><?php echo textOnNull($json['BIOS']['Response'][0]['Version'], "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><b>Public IP Address: </b><?php echo textOnNull($json['General']['Response'][0]['ExternalIP']["ip"], "N/A");?></li>
						<li class="list-group-item" style="padding:6px"><span style="margin-left:0px"><b>Local IP Address: </b><?php echo textOnNull($json['General']['Response'][0]['PrimaryLocalIP'], "N/A");?></span></li>
						<?php if(count($json['WindowsActivation']['Response']) > 0) {
							$status = $json['WindowsActivation']['Response'][0]['LicenseStatus'];
							if($status!="Licensed")$status="Not activated";
							$color = ($status == "Licensed" ? "text-success" : "text-danger");
						?>
							<li class="list-group-item" style="padding:6px"><b>Windows Activation: </b><span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
						<?php } 
						 if((int)$json['Battery']['Response'][0]['BatteryStatus']>0){ ?>
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
							$statusInt = $json['Battery']['Response'][0]['BatteryStatus'];						
						?>
						<?php echo textOnNull($json['Battery']['Response'][0]['EstimatedChargeRemaining'], "Unknown");?>%
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
					
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">
				<div class="">
					<ul class="list-group" style="margin-left:20px">
						<li class="list-group-item" style="padding:6px"><b>Current User: </b><?php echo textOnNull(basename($json['General']['Response'][0]['UserName']), "Unknown");?></li>
						<li class="list-group-item" style="padding:6px"><b>Domain: </b><?php echo textOnNull($json['General']['Response'][0]['Domain'], "N/A");?></li>
						<?php
							$lastBoot = explode(".", $json['General']['Response'][0]['LastBootUpTime'])[0];
							$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
						?>
						<li class="list-group-item" style="padding:6px"><b>Uptime: </b><?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
						<?php if(count($json['Firewall']) > 0) {

							$public = $json['Firewall']['Response'][0]['publicProfile'];
							if($public=="OFF"){ $public="Disabled"; }else{ $public="Enabled"; }
							$color1 = (($public == "Enabled") ? "text-success" : "text-danger");

							$private = $json['Firewall']['Response'][0]['privateProfile'];
							if($private=="OFF"){ $private="Disabled"; }else{ $private="Enabled"; }
							$color2 = (($private == "Enabled") ? "text-success" : "text-danger");

							$domain = $json['Firewall']['Response'][0]['domainProfile'];
							if($domain=="OFF"){ $domain="Disabled"; }else{ $domain="Enabled"; }
							$color3 = (($domain == "Enabled") ? "text-success" : "text-danger");
						?>
							<li class="list-group-item" style="padding:6px"><b>Firewall Status: </b><br>
								<center>
									<span style="margin-left:20px">Public: <span style="padding-right:20px" class="<?php echo $color1; ?>"><?php echo $public; ?></span></span>
									Private: <span style="padding-right:20px" class="<?php echo $color2; ?>"><?php echo $private; ?></span>
									Domain: <span class="<?php echo $color3; ?>"><?php echo $domain; ?></span>
								</center>
							
							</li>
						<?php } 
						
						if(count($json['General']['Response'][0]['Antivirus']) > 0) {
							$status = $json['General']['Response'][0]['Antivirus'];
							$color = ($status == "No Antivirus" ? "text-danger" : "text-success");
						?>
							<li class="list-group-item" style="padding:6px"><b>Antivirus: </b><span title="<?php echo textOnNull($status, "N/A"); ?>" class="<?php echo $color; ?>"><?php echo mb_strimwidth(textOnNull($status, "N/A"), 0, 30, "...");?></span></li>
						<?php } ?>
						<li class="list-group-item" title="Path: <?php echo $json['Agent']['Response'][0]['Path']; ?>" style="padding:6px"><b>Agent Version: </b><?php echo $json['Agent']['Response'][0]['Version']; ?></li>
					</ul>
				</div>
		  </div>
		</div>
	</div>
	<?php  if($online=="1"){  ?>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 style="padding:7px" class="panel-title">
					Geolocation Details
				</h5>
			</div>
			<div class="panel-body" style="height:285px;">
				<div class="row">
					<?php $loc = $json['General']['Response'][0]['ExternalIP']["loc"]; ?>
					<div style="width: 100%">
						<iframe width="100%" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=<?php echo $loc; ?>&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed">
							
						</iframe>
					</div>
				</div>
		  </div>
		</div>
	</div>
	<?php }
	 ?>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 style="padding:7px" class="panel-title">
					Agent Error Log
				</h5>
			</div>
			<div class="panel-body" style="height:285px;overflow:hidden">
				<div class="row">
					<table id="dataTable" style="width:125%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover table-borderless">
						<thead>
							<tr style="border-bottom:2px solid #d3d3d3;">
							<th style="width:20px;">Title</th>
							<th scope="col">Details</th>
							</tr>
						</thead>
						<tbody>		
						<?php
							$Logs = array_reverse($json['AgentLog']['Response']);
							$error = $json['AgentLog_error'];
							//Sort The array by Name ASC
							usort($Logs, function($a, $b) {
								return $a['Name'] <=> $b['Name'];
							});
							foreach($Logs as $key=>$log){
								if (strpos($log['Type'], 'Warn') !== false) {
									$logColor="background:#fff3cd;color:#856404";
									$type="Warning";
								}
								if (strpos($log['Type'], 'Info') !== false) {
									//$logColor="background:#e2e3e5;color:#383d41";
									$logColor="";
									$type="Information";
								}
								if (strpos($log['Type'], 'Error') !== false ) {
									$logColor="background:#f8d7da;color:#721c24";
									$type="Error";
								}
								$time = $log['Time'];
								$count++;
								if($count==400){
									break;
								}
								if (strlen($log['Message']) >= 45) {
									$message= substr($log['Message'], 0, 45)."...";
								}
								else {
									$message= $log['Message'];
								}
							
						?>	
							<tr style="<?php echo $logColor; ?>">
								<td width="5%" ><?php echo $log['Title']; ?></td>
								<td title="<?php echo $log['Message']." @ ".$time; ?>" ><?php echo $message; ?></td>
							</tr>	
							<?php } ?>				
						</tbody>
					</table>
				</div>
		  	</div>
		</div>
	</div>
	<?php  if($online=="1"){  ?>
	<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h5 style="padding:7px" class="panel-title">
					Speedtest
				</h5>
			</div>
			<div class="panel-body" style="overflow:hidden;background:#1D1D35;height:285px;">
				<div class="rsow">
					<a target="_blank" href="<?php echo str_replace(".png","",$json['OklaSpeedtest']['Response']['share']); ?>">
						<form style="" method="post" action="/">
						<?php if($json['OklaSpeedtest']['Response']['share']!=""){ ?>
							<center><img width="80%" style="margin-top:-10px" height="80%" src="<?php echo $json['OklaSpeedtest']['Response']['share']; ?>"/></center>
						<?php }else{ ?>
							<center><h6 style='text-align:center;width:100%;bottom:0;padding:30px;color:#fff'>Refresh the results to get the latest Internet Speedtest from this asset.</h6></center><br><br>
						<?php } ?>
							<input type="hidden" value="refreshSpeedtest" name="type">
							<input type="hidden" value="<?php echo $computerID; ?>" name="company_id">
							<center>
								<button class="btn btn-md btn-secondary" style="width:100%;bottom:0" type="submit">Refresh Results</button>
							</center>
						</form>
					</a>
				</div>
		 	 </div>
		</div>
	</div>
	<?php } ?>
</div>
<!-------------------------------MODALS------------------------------------>
<div id="companyMoreInfo" class="modal fade" role="dialog">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<h4 class="modal-title"><?php if(crypto('decrypt', $company['name'],$company['hex'])!="N/A"){ echo textOnNull(crypto('decrypt',$company['name'],$company['hex']), "No ".$msp." Info"); }else{ echo $msp." Information";} ?></h4>
	  </div>
	  <div class="modal-body">
		<ul class="list-group">
			<li class="list-group-item">
				<b>Phone:</b>
				<?php echo textOnNull(phone(crypto('decrypt',$company['phone'],$company['hex'])), "No ".$msp." Phone"); ?>
			</li>
			<li class="list-group-item">
				<b>Email:</b>
				<a href="mailto:<?php echo crypto('decrypt',$company['email'],$company['hex']); ?>">
					<?php echo textOnNull(crypto('decrypt',$company['email'],$company['hex']), "No ".$msp." Email"); ?>
				</a>
			</li>
			<li class="list-group-item">
				<b>Address:</b>
				<?php echo textOnNull(crypto('decrypt',$company['address'],$company['hex']), "No ".$msp." Address"); ?>
			</li>
			<li class="list-group-item">
				<b>Comments:</b><br>
				<span style="margin-left:10px;">
					<?php echo textOnNull(crypto('decrypt',$company['comments'],$company['hex']), "No Comments"); ?>
				</span>
			</li>
		</ul>
		<span style="color:#696969;float:right;font-size:10px;">
		<?php if($company['date_added']!=""){ ?>
			Added <?php echo date("m/d/Y\ h:i:s", strtotime($company['date_added'])); 
		} ?>
		</span>
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;color:#fff;" data-dismiss="modal">Close</button>
	  </div>
	</div>
  </div>
</div>

<script>
	function sendMessage(){  
		var alertType = $("input[name='alertType']:checked").val();
		var alertTitle = $("#inputTitle").val();
		var alertMessage = $("#inputMessage").val();
		$.post("/", {
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
	var data = {
	  labels: [
	    "Used (bytes)","Unused (bytes)"
	  ],
	  datasets: [
	    {
	      data: [<?php echo (int)$used2 .",".(int)$free2; ?>],
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
	      data: [<?php echo $cpuUsage.",".(100 - $cpuUsage); ?>],
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
	      data: [<?php echo (int)$usedPct.",".(int)$left; ?>],
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
		$('#dataTable').DataTable( {
			"lengthMenu": [[5], [5]],
			colReorder: true,
			"searching": false,
			"lengthChange": false,
			"info": false,
			"order": [],
			colReorder: true
		} );
	} );
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove()
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>