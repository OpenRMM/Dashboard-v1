
<?php 
ini_set('display_errors', '0');
if($_SESSION['userid']==""){ 
?>
	<script>		
		toastr.error('Session timed out.');
		setTimeout(function(){
			setCookie("section", btoa("Login"), 365);	
			window.location.replace("../");
		}, 3000);		
	</script>
<?php 
	exit("<center><h5>Session timed out. You will be redirected to the login page in just a moment.</h5><br><h6>Redirecting</h6></center>");
}	

$query = "SELECT username,nicename,user_color FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);
$username=$user['username'];

$query = "SELECT * FROM computers where active='1' and computer_type='OpenRMM Server' ORDER BY ID DESC LIMIT 1";
$results = mysqli_query($db, $query);
$computer = mysqli_fetch_assoc($results);
$computerID= $computer['ID'];

$getWMI = array("general","logical_disk","bios","processor","agent","battery","windows_activation","antivirus","firewall");
$json = getComputerData($computer['ID'], $getWMI);
//print_r(getComputerData($computer['ID'], array("*")));
function welcome(){
	if(date("H") < 12){
		return "Good Morning";
	}elseif(date("H") > 11 && date("H") < 18){
		return "Good Afternoon";
	}elseif(date("H") > 17){
		return "Good Evening";
	}
}
if($siteSettings['general']['server_status']=="0" or $siteSettings['general']['server_status']==""){
	$serverStatus="Offline";
	$serverStatus_color="danger";
}else{
	$serverStatus="Online";
	$serverStatus_color="success";
} 

?>	
	<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
		<h5 style="color:#0c5460;">Dashboard
			<button title="Refresh" onclick="loadSection('Dashboard');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-sync"></i>
			</button>
			<br>
			<span style="font-size:14px;color:#999"><?php echo welcome().", ".$user['username']."!"; ?></span>
		</h5>
	</div>	
	<div class="row" style="margin-bottom:10px;margin-top:20px;border-radius:3px;overflow:hidden;padding:0px;">
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 " style="padding-left:20px;">
			<div class="card user-card2" style="heigsht:100%;width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
				<div style="height:45px" class="panel-heading">
					<h5 class="panel-title">New Assets</h5>
				</div>			
				<div class="card-block text-center">
					<ul class="list-group">	
						<?php
							//Get Total Count					
							$query = "SELECT * FROM computers WHERE active='1' ORDER BY ID DESC LIMIT 3";
							//Fetch Results
							$count = 0;
							$results = mysqli_query($db, $query);
							$resultCount = mysqli_num_rows($results);
							while($result = mysqli_fetch_assoc($results)){
								$getWMI = array("general");
								$data = getComputerData($result['ID'], $getWMI);
								$count++;
								$icons = array("desktop","server","laptop","tablet","allinone","other");
								if(in_array(strtolower(str_replace("-","",$result['computer_type'])), $icons)){
									$icon = strtolower(str_replace("-","",$result['computer_type']));
									if($icon=="allinone")$icon="tv";
									if($icon=="tablet")$icon="tablet-alt";
									if($icon=="other")$icon="microchip";
								}else{
									$icon = "server";
								}  
						?>
						<li onclick="loadSection('General', '<?php echo $result['ID']; ?>');" class="list-group-item secbtn" style="text-align:left;cursor:pointer;">
							<?php if($result['online']=="0") {?>
								<i class="fas fa-<?php echo $icon;?>" style="color:#666;font-size:12px;" title="Offline"></i>
							<?php }else{?>
								<i class="fas fa-<?php echo $icon;?>" style="color:green;font-size:12px;" title="Online"></i>
							<?php }?>
							&nbsp;&nbsp;<?php echo textOnNull($data['general']['Response'][0]['csname'],"Unavailable"); ?>
						</li>
						<?php }  ?>
					</ul>
				</div>
			</div>	
			<?php if($siteSettings['Service_Desk']=="Enabled"){ ?>
			<div class="card user-card2" style="heigsht:100%;width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
				<div style="height:45px" class="panel-heading">
					<h5 class="panel-title">Recent Tickets</h5>
				</div>			
				<div class="card-block text-center">
					<ul class="list-group">	
					<?php
							//Get Total Count							
							$query = "SELECT * FROM tickets WHERE active='1' ORDER BY ID DESC LIMIT 3";
							//Fetch Results
							$count = 0;
							$results = mysqli_query($db, $query);
							while($result = mysqli_fetch_assoc($results)){
								$count++;	
							?>
						<li onclick="loadSection('Ticket', '<?php echo $result['ID']; ?>');" class="list-group-item secbtn" style="text-align:left;cursor:pointer;">
							<i class="fas fa-ticket-alt" style="color:#666;font-size:12px;" title="Ticket"></i>
							&nbsp;&nbsp;<?php echo $result['title']; ?>
						</li>
						<?php }  ?>
					</ul>
				</div>
			</div>
			<?php } ?>				
			<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
				<div style="height:45px" class="panel-heading">
					<h5 class="panel-title">Notes
						<button id="delNote" type="button" onclick="deleteNote('1');" class="delNote btn btn-danger btn-sm" style="float:right;padding:5px;"><i class="fas fa-trash"></i>&nbsp;&nbsp;&nbsp;Clear All</button>
					</h5>
				</div>
				<div id="TextBoxesGroup" class="card-block">
					<?php
					$count = 0;
					$query = "SELECT ID, notes,hex FROM users where ID='".$_SESSION['userid']."'";
					$results = mysqli_query($db, $query);
					$data = mysqli_fetch_assoc($results);
					$notes = crypto('decrypt', $data['notes'],$data['hex']);
					if($notes!=""){
						$allnotes = explode("|",$notes);
						foreach(array_reverse($allnotes) as $note) {
							if($note==""){ continue; }
							if($count>=5){ break; }
							$note = explode("^",$note);
							$count++;
					?>
						<a title="View Note" class="noteList" onclick="$('#notetitle').text('<?php echo $note[0]; ?>');$('#notedesc').text('<?php echo $note[1]; ?>');" data-toggle="modal" data-target="#viewNoteModal">
							<li  style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item">
								<i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>
								<?php echo ucwords($note[0]);?>
							</li>
						</a>
					<?php } } ?>
					<?php if($count==0){ ?>
						<li class="no_noteList list-group-item">No Notes</li>
					<?php }else{ ?>
					<li class="no_noteList list-group-item" style="display:none" >No Notes</li>
					<?php } ?>
				</div>
				<button style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none" data-toggle="modal" data-target="#noteModal" title="Create New Note" class="btn btn-warning btn-block p-t-15 p-b-15">Create New Note</button>
			</div>
		</div>			
		<?php 	
		//Get stats
			//companies
			$query = "SELECT ID, name,hex FROM companies where active='1'";
			$companyArray= "";
			$companys = mysqli_query($db, $query);
			while($result = mysqli_fetch_assoc($companys)){
				$companyArray.= "'".crypto('decrypt', $result['name'],$result['hex'])."',";
				$query = "SELECT ID FROM computers where active='1' and company_id='".$result['ID']."'";
				$count = mysqli_num_rows(mysqli_query($db, $query));
				$companyTotal.=$count.",";
			}
			$companyArray= rtrim($companyArray,',');
			$companyTotal=rtrim($companyTotal,',');
			if($count==0){
				$companyTotal="0,100";
				$companyArray= "'Assigned','Not Assigned'";
			}
			//users
			$query = "SELECT ID FROM users where active='1'";
			$users1 = mysqli_num_rows(mysqli_query($db, $query));
			$query = "SELECT ID FROM users where active='0'";
			$users2 = mysqli_num_rows(mysqli_query($db, $query));

			//assets
			$query = "SELECT ID FROM computers where active='1' and online='1'";
			$assets1 = mysqli_num_rows(mysqli_query($db, $query));
			$query = "SELECT ID FROM computers where active='1' and online='0'";
			$assets2 = mysqli_num_rows(mysqli_query($db, $query));
		
		?>
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mh-40" style="heidght:260px;">
			<div class="row" style="heighst:200px">
				<div class="col-md-4 py-1">
					<div class="card" style="backgrsound:#35384e">
						<div style="cursor:pointer;" onclick="loadSection('AllUsers');" class="card-body">
							<canvas data-centerval="" id="chDonut2"></canvas>
							<h6 style="text-align:center">Users</h6>
						</div>
					</div>
				</div>
				<div class="col-md-4 py-1">
					<div class="card" style="bacskground:#35384e">
						<div style="cursor:pointer;" onclick="loadSection('AllCompanies');" class="card-body">
							<canvas data-centerval="" id="chDonut1"></canvas>
							<h6 style="text-align:center"><?php echo $msp."s"; ?></h6>
						</div>
					</div>
				</div>
				<div class="col-md-4 py-1">
					<div class="card" style="bacskground:#35384e">
						<div style="cursor:pointer;" onclick="loadSection('Assets');"  class="card-body">
							<canvas data-centerval="" id="chDonut3"></canvas>
							<h6 style="text-align:center">Asset Status</h6>
						</div>
					</div>
				</div>
			</div>
			<div style="padding:15px" class="card">
				<div class="tab-block">
					<ul class="nav nav-pills">
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center;" class="nav-item">
							<a data-toggle="pill" class="nav-link active" data-toggle="tab" href="#Summary">Summary</a>
						</li>
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-toggle="pill" class="nav-link"  data-toggle="tab" href="#Alerts">Alerts</a>
						</li>
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-toggle="pill" class="nav-link"  data-toggle="tab" href="#Tasks">Tasks</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" style="padding-top:10px;overflow:hidden" >
					<div id="Summary" class="tab-pane fade-in active">
						<h5 style="margin-left:15px;display:inline;">Server Information Summary
							<h6 style="display:inline;margin-left:25px;margin-top:3px;position:absolute">
								<span style="color:#000" class="badge badge-<?php echo $serverStatus_color; ?>"><?php echo $serverStatus; ?></span>
							</h6>
							<?php if($_SESSION['accountType']=="Admin"){ ?>
								<form method="post" style="display:inline">
									<input type="hidden" name="type" value="stopServer">
									<button type="submit" title="Stop Server" style="float:right;margin-top:-10px" class="btn btn-sm btn-danger"><i class="fas fa-power-off"></i></button>
									<!--<button title="Restart Server" style="float:right;margin-right:10px" class="btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>-->
								</form>	
							<?php } ?>
						</h5>
						<br>
						<div class="row">
							<div class="col-xs-6 col-sm-6 col-md-4 col-lg-6" style="padding:3px;">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h5  style="padding:7px;" class="panel-title">
											Details
										</h5>
									</div>
									<div class="panel-body" style="height:285px;">	
										<div class="roaw">
										<ul class="list-group" style="margin-left:10px">
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','processor','0.Name');" id="processor_LoadPercentage" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Processor: </b><?php echo textOnNull(str_replace(" 0 ", " ",str_replace("CPU", "",str_replace("(R)","",str_replace("(TM)","",$json['processor']['Response'][0]['Name'])))), "N/A");?></li>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.Caption');" id="general_Caption" class="list-group-item secbtn olderdata" style="padding:6px"><b>Operating System: </b><?php echo textOnNull(str_replace("Microsoft", "", $json['general']['Response'][0]['Caption']), "N/A");?></li>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.SystemType');" id="general_SystemType" class="list-group-item secbtn olderdata" style="padding:6px"><b>Architecture: </b><?php echo textOnNull(str_replace("PC", "",$json['general']['Response'][0]['SystemType']), "N/A");?></li>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','bios','0.Version');" id="bios_Version" class="list-group-item secbtn olderdata" style="padding:6px"><b>BIOS Version: </b><?php echo textOnNull($json['bios']['Response'][0]['Version'], "N/A");?></li>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','ExternalIP.ip');" id="general_ip" class="list-group-item secbtn olderdata" style="padding:6px"><b>Public IP Address: </b><?php echo textOnNull($json['general']['Response'][0]['ExternalIP']["ip"], "N/A");?></li>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.');" id="general_PrimaryLocalIP" class="list-group-item secbtn olderdata" style="padding:6px"><span style="margin-left:0px"><b>Local IP Address: </b><?php echo textOnNull($json['general']['Response'][0]['PrimaryLocalIP'], "N/A");?></span></li>
											<?php 
											if((int)$json['battery']['Response'][0]['BatteryStatus']>0){ ?>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','battery','0.BatteryStatus');" id="battery_BatteryStatus" class="list-group-item secbtn olderdata" style="padding:6px"><b>Battery Status: </b><?php 								
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
												$statusInt = $json['battery']['Response'][0]['BatteryStatus'];						
											?>
											<?php echo textOnNull($json['battery']['Response'][0]['EstimatedChargeRemaining'], "Unknown");?>%
											(<span style="color:<?php echo $statusArray[$statusInt]['Color']; ?>"><?php echo $statusArray[$statusInt]['Text']; ?></span>)	
											</li>
											<?php } ?>
										</ul>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-4 col-lg-6" style="padding:3px;">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h5 style="padding:7px" class="panel-title">
											
										</h5>
									</div>
									<div class="panel-body" style="height:285px;">
										<div class="rsow">
										<ul class="list-group" style="margin-left:20px">
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.csname');" id="general_0csname" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Hostname: </b><?php echo textOnNull($json['general']['Response'][0]['csname'], "Unavailable");?></li>
											<?php
												$lastBoot = explode(".", $json['general']['Response'][0]['LastBootUpTime'])[0];
												$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
											?>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.LastBootUpTime');" id="general_0LastBootUpTime" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Uptime: </b><?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
											<?php if(count($json['firewall']) > 0) {

												$public = $json['firewall']['Response'][0]['publicProfile'];
												//if($public=="OFF"){ $public="Disabled"; }else{ $public="Enabled"; }
												$color1 = (($public == "Enabled") ? "text-success" : "text-danger");

												$private = $json['firewall']['Response'][0]['privateProfile'];
												//if($private=="OFF"){ $private="Disabled"; }else{ $private="Enabled"; }
												$color2 = (($private == "Enabled") ? "text-success" : "text-danger");

												$domain = $json['firewall']['Response'][0]['domainProfile'];
												//if($domain=="OFF"){ $domain="Disabled"; }else{ $domain="Enabled"; }
												$color3 = (($domain == "Enabled") ? "text-success" : "text-danger");
											?>
												<li id="Firewall" class="list-group-item olderdata" style="z-index:2;padding:6px;width:100%"><b>Firewall Status: </b><br>
													<center>
														<span data-toggle="modal" data-target="#olderDataModal"  onclick="olderData('<?php echo $computerID; ?>','firewall','0.publicProfile');"  style="margin-left:20px">
															Public: <span style="padding-right:20px" class="<?php echo $color1; ?>"><?php echo $public; ?></span>
														</span>
														<span data-toggle="modal" data-target="#olderDataModal"  onclick="olderData('<?php echo $computerID; ?>','firewall','0.privateProfile');">
															Private: <span style="padding-right:20px" class="<?php echo $color2; ?>"><?php echo $private; ?></span>
														</span>
														<span data-toggle="modal" data-target="#olderDataModal"  onclick="olderData('<?php echo $computerID; ?>','firewall','0.domainProfile');">
															Domain: <span class="<?php echo $color3; ?>"><?php echo $domain; ?></span>
													</span>
													</center>
												</li>		
											<?php } 
											if(count($json['general']['Response'][0]['Antivirus']) > 0) {
												$status = $json['general']['Response'][0]['Antivirus'];
												$color = ($status == "No Antivirus" ? "text-danger" : "text-success");
											?>
												<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','general','0.Antivirus');" id="general_0Antivirus" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%"><b>Antivirus: </b><span title="<?php echo textOnNull($status, "N/A"); ?>" class="<?php echo $color; ?>"><?php echo mb_strimwidth(textOnNull($status, "N/A"), 0, 30, "...");?></span></li>
											<?php } ?>
											<?php if(count($json['windows_activation']['Response']) > 0) {
												$status = $json['windows_activation']['Response'][0]['LicenseStatus'];
												if($status!="Licensed")$status="Not activated";
												$color = ($status == "Licensed" ? "text-success" : "text-danger");
											?>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','windows_activation','0.LicenseStatus');" id="WindowsActivation_0LicenseStatus" class="list-group-item secbtn olderdata" style="padding:6px"><b>Windows Activation: </b><span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
											<?php } ?>
											<li data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','agent','0.Version');" id="agent_0Version" class="list-group-item secbtn olderdata" style="z-index:2;padding:6px;width:100%" title="Path: <?php echo $json['agent']['Response'][0]['Path']; ?>"><b>Server Version: </b><?php echo textOnNull($json['agent']['Response'][0]['Version'],"N/A"); ?></li>
										</ul>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-4 col-lg-12" style="padding:3px;">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h5 style="padding:7px" class="panel-title">
											Error Log
										</h5>
									</div>
									<div class="panel-body" style="height:285px;">
										<div class="row">
											<table id="datsaTable" style="width:125%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover table-borderless">
												<thead>
													<tr style="border-bottom:2px solid #d3d3d3;">
														<th scope="col">Details</th>
														<th scope="col">Time</th>
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
						</div>
					</div>
					<div id="Alerts" class="tab-pane fade">
						<button data-toggle="modal" data-target="#editAlert" onclick="$('#alertCompany').show();$('#alertID').val('');" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> &nbsp;Add Alert</button><hr>
						<table class="table table-hover table-borderless" id="datatable">
							<tr>
								
								<th>Name</th>
								<th >Alert Details</th>
								<th><?php echo $msp; ?></th>
								<th style="float:right">Actions</th>
							</tr>
							<?php
							$count=0;
							$query = "SELECT * FROM alerts WHERE active='1' and computer_id='0' ORDER BY ID ASC";
							$results = mysqli_query($db, $query);
							while($alert = mysqli_fetch_assoc($results)){
								$count++;

								if($alert['last_run']!=""){ $last_run=$alert['last_run'];}else{ $last_run="Never";}
								$details=jsonDecode($alert['details'],true);
								if($alert['company_id']=="0"){
									$company="All ".$msp."s";
								}else{
									$query = "SELECT * FROM companies WHERE active='1' and ID='".$alert['company_id']."' ORDER BY ID ASC";
									$results = mysqli_query($db, $query);
									$companyArray = mysqli_fetch_assoc($results);
									$company=crypto('decrypt', $companyArray['name'],$companyArray['hex']);
								}
							?>
							<tr id="alert<?php echo $alert['ID']; ?>">
								<td><?php echo $alert['name']; ?></td>
								<td>If <b><?php echo $details['json']['Details']['Condition']."</b> ".$details['json']['Details']['Comparison']." ".$details['json']['Details']['Value']; ?></td>
								<td><?php echo $company; ?></td>
								<td style="float:right">
									<button type="button" onclick="deleteAlert('<?php echo $alert['ID']; ?>')" title="Delete Alert" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i>
									</button>									
								</td>
							</tr>
							<?php } 
							if($count == 0){ ?>
								<tr>
									<td colspan=4><center><h6>Once you create an Alert, it will show up here.</h6></center></td>
								</tr>
						<?php }?>
						</table>
					</div>
					<div id="Tasks" class="tab-pane fade">
						<button data-toggle="modal" data-target="#editTrigger" class="btn btn-sm btn-warning"><i class="fas fa-plus"></i> &nbsp;Add Task</button><hr>
						<table class="table table-hover table-borderless" id="datatable">
							<tr>
								<th>Task Name</th>
								<th>Last run</td>
								<th style="float:right"></th>
							</tr>
							<?php
							$count=0;
							$query = "SELECT * FROM tasks WHERE active='1' ORDER BY ID ASC";
							$results = mysqli_query($db, $query);
							while($task = mysqli_fetch_assoc($results)){
								$count++;
								if($task['last_run']!=""){ $last_run=$task['last_run'];}else{ $last_run="Never";}
							?>
							<tr id="task<?php echo $task['ID']; ?>">
								<td><?php echo $task['name']; ?></td>
								<td><?php echo $last_run; ?></td>
								<td style="float:right">
									<form action="/" method="post" style="display:inline;">
										<input type="hidden" value="<?php echo $task['ID'];?>" name="ID">
										<input type="hidden" value="startTask" name="type">
										<button type="submit" title="Run Task Now" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-primary btn-sm">
											<i class="fas fa-play"></i>
										</button>
									</form>
									<button type="button" onclick="deleteTask('<?php echo $task['ID']; ?>')" title="Delete Task" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i>
									</button>
								</td>
							</tr>
							<?php } 
							if($count == 0){ ?>
								<tr>
									<td colspan=4><center><h6>Once you create a Task, it will show up here.</h6></center></td>
								</tr>
						<?php }?>
						</table>
					</div>
				</div>					
			</div>		
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#dataTable').dataTable( {
			"paging": false,
			"order": [],
			colReorder: true
		} );
	});
</script>
<script>
	var data = {
	  labels: [
		<?php echo $companyArray; ?>
	  ],
	  datasets: [
	    {
	      data: [<?php echo $companyTotal;  ?>],
	      backgroundColor: [
	        "#0c5460"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
	      ]
	    }]
	};
	var data2 = {
	  labels: [
		"Active","Deactivated"
	  ],
	  datasets: [
	    {
	      data: [<?php echo $users1.",".$users2 ?>],
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
	    "Online","Offline"
	  ],
	  datasets: [
	    {
	      data: [<?php echo $assets1.",".$assets2; ?>],
	      backgroundColor: [
	        "<?php echo $siteSettings['theme']['Color 5']; ?>"
	      ],
	      hoverBackgroundColor: [
	        "#696969"
	      ]
	    }]
	};
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut1'), {
	  type: 'pie',
	  data: data,
	  options: {
	  	responsive: true,
	    legend: {
	      display: false
	    }
	  }
	});
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut2'), {
	  type: 'pie',
	  data: data2,
	  options: {
	  	responsive: true,
	    legend: {
	      display: false
	    }
	  }
	});
	var promisedDeliveryChart = new Chart(document.getElementById('chDonut3'), {
	  type: 'pie',
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