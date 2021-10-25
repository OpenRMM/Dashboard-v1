<?php 
if($_SESSION['userid']==""){ 
?>
	<script>		
		toastr.error('Session timed out.');
		setTimeout(function(){
			setCookie("section", "Login", 365);	
			window.location.replace("../");
		}, 3000);		
	</script>
<?php 
	exit("<center><h5>Session timed out. You will be redirected to the login page in just a moment.</h5><br><h6>Redirecting</h6></center>");
}	
$limit = intval($_GET['limit']);
if($limit == 0){
	$limit = 20;
}
$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);
$username=$user['username'];

$query = "SELECT ID,hostname,computer_type FROM computerdata where active='1' and computer_type='OpenRMM Server' and online='1' LIMIT 1";
$results = mysqli_query($db, $query);
$computer = mysqli_fetch_assoc($results);
$resultCount = mysqli_num_rows($results);

$json = getComputerData($computer['ID'], array("*"), "");
function welcome(){
	if(date("H") < 12){
		return "Good Morning";
	}elseif(date("H") > 11 && date("H") < 18){
		return "Good Afternoon";
	}elseif(date("H") > 17){
		return "Good Evening";
	}
}



if($siteSettings['general']['serverStatus']=="0" or $siteSettings['general']['serverStatus']==""){
	$serverStatus="Offline";
	$serverStatus_color="danger";
}else{
	$serverStatus="Online";
	$serverStatus_color="success";
} 
?>	
	<?php if($_SESSION['usesrid']!="" ){ ?>
		<div class="row" style="margin-bottom:20px">
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%;background:<?php echo $siteSettings['theme']['Color 2']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('Assets');">
						<div style="padding:10px 10px 0px 20px;">
							<i class="fas fa-desktop" style="font-size:28px;float:right;"></i>
							<span style="font-size:18px;" ><?php echo $resultCount; ?></span><br>
							<span style="font-size:25px;">Assets</span>
						</div>							
					</a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%; background:<?php echo $siteSettings['theme']['Color 3']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('AllCompanies');">
						<div style="padding:10px 10px 0px 20px;">
							<i class="fas fa-building" style="font-size:28px;float:right;"></i>
							<span style="font-size:18px;"><?php echo $companyCount;?></span><br>
							<span style="font-size:25px;"><?php echo $msp; ?>s</span>
						</div>
					</a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%;background:<?php echo $siteSettings['theme']['Color 4']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('AllUsers');">
						<div style="padding:10px 10px 0px 20px;">
							<i class="fas fa-user" style="font-size:28px;float:right;"></i>
							<span style="font-size:18px;"><?php echo $userCount;?></span><br>
							<span style="font-size:25px;">Technicians</span>
						</div>
					</a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%;background:<?php echo $siteSettings['theme']['Color 5']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('Tickets');">
						<div style="padding:10px 10px 0px;">
							<i class="fas fa-server" style="font-size:28px;float:right;"></i>
							<span style="font-size:18px;"><?php echo $serverStatus; ?></span><br>
							<span style="font-size:25px;">Server Status</span>
						</div>
					</a>
				</div>
			</div>
		</div>
	<?php } ?>
	<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>;">Dashboard
			<a href="javascript:void(0)" title="Refresh" onclick="loadSection('Dashboard');" class="btn btn-sm" style="float:right;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-sync"></i>
			</a>
			<br>
			<span style="font-size:14px;color:#999"><?php echo welcome().", ".$user['username']."!"; ?></span>
		</h4>
	</div>	
	<div class="row" style="margin-bottom:10px;margin-top:20px;border-radius:3px;overflow:hidden;padding:0px;">
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 " style="padding-left:20px;">
			<div class="card user-card2" style="heigsht:100%;width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
				<div style="height:45px" class="panel-heading">
					<h5 class="panel-title">Recently Added Assets</h5>
				</div>			
				<div class="card-block text-center">
					<ul class="list-group">	
						<?php
								//Get Total Count
								$query = "SELECT ID FROM computerdata where active='1'";
								$results = mysqli_query($db, $query);
								$resultCount = mysqli_num_rows($results);							
								$query = "SELECT * FROM computerdata
											LEFT JOIN companies ON companies.CompanyID = computerdata.CompanyID
											WHERE computerdata.active='1'
											ORDER BY computerdata.hostname ASC";
								//Fetch Results
								$count = 0;
								$results = mysqli_query($db, $query);
								while($result = mysqli_fetch_assoc($results)){
									if($search==""){
										$getWMI = array("WMI_LogicalDisk", "WMI_ComputerSystem", "Ping");
									}else{
										$getWMI = array("*");
									}
									$data = getComputerData($result['ID'], $getWMI);
									$count++;
									$icons = array("desktop","server","laptop");
									if(in_array(strtolower($result['computer_type']), $icons)){
										$icon = strtolower($result['computer_type']);
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
							&nbsp;&nbsp;<?php echo $result['hostname']; ?>
						</li>
						<?php }  ?>
					</ul>
				</div>
			</div>	
							
			<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
				<div style="height:45px" class="panel-heading">
					<h5 class="panel-title">Notes
					<form style="display:inline" method="post">
						<input type="hidden" name="delNote" value="true"/>
						<button type="submit" class="btn btn-danger btn-sm" style="float:right;padding:5px;"><i class="fas fa-trash"></i>&nbsp;&nbsp;&nbsp;Clear All</button>
					</form>
					</h5>
				</div>
				<div class="card-block">
					<?php
					$count = 0;
					$query = "SELECT ID, notes FROM users where ID='".$_SESSION['userid']."'";
					$results = mysqli_query($db, $query);
					$data = mysqli_fetch_assoc($results);
					$notes = $data['notes'];
					if($notes!=""){
						$allnotes = explode("|",$notes);
						foreach(array_reverse($allnotes) as $note) {
							if($note==""){ continue; }
							if($count>=5){ break; }
							$note = explode("^",$note);
							$count++;
					?>
						<a title="View Note" onclick="$('#notetitle').text('<?php echo $note[0]; ?>');$('#notedesc').text('<?php echo $note[1]; ?>');" data-toggle="modal" data-target="#viewNoteModal">
							<li  style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item">
								<i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>
								<?php echo ucwords($note[0]);?>
							</li>
						</a>
					<?php } } ?>
					<?php if($count==0){ ?>
						<li class="list-group-item">No Notes</li>
					<?php } ?>
				</div>
				<button style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none" data-toggle="modal" data-target="#noteModal" title="Create New Note" class="btn btn-warning btn-block p-t-15 p-b-15">Create New Note</button>
			</div>
		</div>			
		<?php 	
		//Get stats
			//companies
			$query = "SELECT CompanyID, name FROM companies where active='1'";
			$companyArray= "";
			$companys = mysqli_query($db, $query);
			while($result = mysqli_fetch_assoc($companys)){
				$companyArray.= "'".$result['name']."',";
				$query = "SELECT ID FROM computerdata where active='1' and CompanyID='".$result['CompanyID']."'";
				$count = mysqli_num_rows(mysqli_query($db, $query));
				$companyTotal.=$count.",";
			}
			$companyArray= rtrim($companyArray,',');
			$companyTotal=rtrim($companyTotal,',');

			//users
			$query = "SELECT ID FROM users where active='1' and accountType='Admin'";
			$users1 = mysqli_num_rows(mysqli_query($db, $query));
			$query = "SELECT ID FROM users where active='1' and accountType='Standard'";
			$users2 = mysqli_num_rows(mysqli_query($db, $query));

			//assets
			$query = "SELECT ID FROM computerdata where active='1' and online='1'";
			$assets1 = mysqli_num_rows(mysqli_query($db, $query));
			$query = "SELECT ID FROM computerdata where active='1' and online='0'";
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
							<a data-toggle="pill" class="nav-link active" data-toggle="tab" href="#home">Summary</a>
						</li>
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-toggle="pill" class="nav-link"  data-toggle="tab" href="#menu1">Alerts</a>
						</li>
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-toggle="pill" class="nav-link"  data-toggle="tab" href="#menu2">Tasks</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" style="padding-top:10px;overflow:hidden" >
					<div id="home" class="tab-pane fade-in active">
						<h5 style="margin-left:15px;display:inline;">Server Details
							<h6 style="display:inline;margin-left:25px;margin-top:3px;position:absolute">
								<span style="color:#000" class="badge badge-<?php echo $serverStatus_color; ?>"><?php echo $serverStatus; ?></span>
							</h6>
							<form method="post" style="display:inline">
								<input type="hidden" name="type" value="stopServer">
								<button type="submit" title="Stop Server" style="float:right;margin-top:-10px" class="btn btn-sm btn-danger"><i class="fas fa-power-off"></i></button>
								<!--<button title="Restart Server" style="float:right;margin-right:10px" class="btn btn-sm btn-warning"><i class="fas fa-sync"></i></button>-->
							</form>	
						</h5>
						<hr>
						<div class="row">
							<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h5  style="padding:7px;" class="panel-title">
											Hardware Details
										</h5>
									</div>
									<div class="panel-body" style="height:285px;">	
										<div class="roaw">
											<ul class="list-group" style="margin-left:20px">
												<li class="list-group-item" style="padding:6px"><b>Processor: </b><?php echo textOnNull(str_replace("(R)","",str_replace("(TM)","",$json['WMI_Processor']['Response'][0]['Name'])), "N/A");?></li>
												<li class="list-group-item" style="padding:6px"><b>Operating System: </b><?php echo textOnNull(str_replace("Microsoft", "", $json['WMI_ComputerSystem']['Response'][0]['Caption']), "N/A");?></li>
												<li class="list-group-item" style="padding:6px"><b>Architecture: </b><?php echo textOnNull($json['WMI_ComputerSystem']['Response'][0]['SystemType'], "N/A");?></li>
												<li class="list-group-item" style="padding:6px"><b>BIOS Version: </b><?php echo textOnNull($json['WMI_BIOS']['Response'][0]['Version'], "N/A");?></li>
												<li class="list-group-item" style="padding:6px"><b>Public IP Address: </b><?php echo textOnNull($json['WMI_ComputerSystem']['Response'][0]['ExternalIP']["ip"], "N/A");?></li>
												<li class="list-group-item" style="padding:6px"><span style="margin-left:0px"><b>Local IP Address: </b><?php echo textOnNull($json['WMI_ComputerSystem']['Response'][0]['PrimaryLocalIP'], "N/A");?></span></li>
												<?php if((int)$json['WMI_Battery']['Response'][0]['BatteryStatus']>0){ ?>
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
												<?php echo textOnNull($json['WMI_Battery']['Response'][0]['EstimatedChargeRemaining'], "Unknown");?>%
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
											Server Details
										</h5>
									</div>
									<div class="panel-body" style="height:285px;">
										<div class="rsow">
											<ul class="list-group" style="margin-left:20px">
												<li class="list-group-item" style="padding:6px"><b>Hostname: </b><?php echo textOnNull(basename($computer['hostname']), "Unknown");?></li>
												<li class="list-group-item" style="padding:6px"><b>Current User: </b><?php echo textOnNull(basename($json['WMI_ComputerSystem']['Response'][0]['UserName']), "Unknown");?></li>
												<li class="list-group-item" style="padding:6px"><b>Domain: </b><?php echo textOnNull($json['WMI_ComputerSystem']['Response'][0]['Domain'], "N/A");?></li>
												<?php
													$lastBoot = explode(".", $json['WMI_ComputerSystem']['Response'][0]['LastBootUpTime'])[0];
													$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
												?>
												<li class="list-group-item" style="padding:6px"><b>Uptime: </b><?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
												<?php  
												if(count($json['WindowsActivation']) > 0) {
													$status = $json['WindowsActivation']['Value'];
													$color = ($status == "Activated" ? "text-success" : "text-danger");
												?>
													<li class="list-group-item" style="padding:6px"><b>Windows Activation: </b><span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
												<?php } 
												if(count($json['Antivirus']) > 0) {
													$status = $json['Antivirus']['Response']['Value'];
													$color = ($status == "No Antivirus" ? "text-danger" : "text-success");
												?>
													<li class="list-group-item" style="padding:6px"><b>Antivirus: </b><span title="<?php echo textOnNull($status, "N/A"); ?>" class="<?php echo $color; ?>"><?php echo mb_strimwidth(textOnNull($status, "N/A"), 0, 30, "...");?></span></li>
												<?php } ?>
												<li class="list-group-item" title="Path: <?php echo $json['Agent']['Response'][0]['Path']; ?>" style="padding:6px"><b>Server Version: </b><?php echo $json['Agent']['Response'][0]['Version']; ?></li>
											</ul>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h5 style="padding:7px" class="panel-title">
											Server Error Log
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
					<div id="menu1" class="tab-pane fade">
						<a href="" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> &nbsp;Add Alert</a><hr>
						<table class="table table-hover table-borderless" id="datatable">
							<tr>
								<th>Status</th>
								<th>Name</th>
								<th style="float:right">Actions</th>
							</tr>
							<tr>
								<td></td>
								<td> No Alerts</td>
								<td style="float:right"></td>
							</tr>
						</table>
					</div>
					<div id="menu2" class="tab-pane fade">
						<button data-toggle="modal" data-target="#editTrigger" class="btn btn-sm btn-warning"><i class="fas fa-plus"></i> &nbsp;Add Task</button><hr>
						<table class="table table-hover table-borderless" id="datatable">
							<tr>
								<th>Task Name</th>
								<th>Condition Types</th>
								<th>Action</th>
								<th>Last run</td>
								<th style="float:right"></th>
							</tr>
							<?php
							$query = "SELECT * FROM tasks WHERE active='1' ORDER BY ID ASC";
							$results = mysqli_query($db, $query);
							while($task = mysqli_fetch_assoc($results)){
								$count++;

								if($task['condition2Type']!=""){ $type=", ".$task['condition2Type'];}
								if($task['condition3Type']!=""){ $type.=", ".$task['condition3Type'];}
								if($task['last_run']!=""){ $last_run=$task['last_run'];}else{ $last_run="Never";}
							?>
							<tr>
								<td><?php echo $task['taskName']; ?></td>
								<td><?php echo $task['condition1Type'].$type; ?></td>
								<td><?php echo $task['actionCommand'].": ". $task['actionValue']; ?></td>
								<td><?php echo $last_run; ?></td>
								<td style="float:right">
									<form action="/" method="post" style="display:inline;">
										<input type="hidden" value="<?php echo $task['ID'];?>" name="ID">
										<input type="hidden" value="startTask" name="type">
										<button type="submit" title="Run Task Now" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-primary btn-sm">
											<i class="fas fa-play"></i>
										</button>
									</form>
									<form action="/" method="post" style="display:inline;">
										<input type="hidden" value="delTask" name="type">
										<input type="hidden" value="<?php echo $task['ID'];?>" name="ID">
										<button type="submit" title="Delete Task" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
											<i class="fas fa-trash"></i>
										</button>
									</form>	
								</td>
							</tr>
							<?php } 
							if($count == 0){ ?>
								<tr>
									<td colspan=4><center><h6>No Tasks Found.</h6></center></td>
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
	function printData(filename) {
		var csv = [];
		var rows = document.querySelectorAll("#printTable table tr");
		for (var i = 0; i < rows.length; i++) {
			var row = [], cols = rows[i].querySelectorAll("td, th");
			for (var j = 0; j < cols.length; j++)
				row.push(cols[j].innerText.replace("Disk Space","").replace("Actions","").replace(/[^\w\s]/gi,"").replace(/\s/g,""));
			csv.push(row.join(","));
		}
		downloadCSV(csv.join("\n"), "page.csv");
	}
	function downloadCSV(csv, filename) {
		var csvFile;
		var downloadLink;
		csvFile = new Blob([csv], {type: "text/csv"});
		downloadLink = document.createElement("a");
		downloadLink.download = filename;
		downloadLink.href = window.URL.createObjectURL(csvFile);
		downloadLink.style.display = "none";
		document.body.appendChild(downloadLink);
		downloadLink.click();
	}
</script>
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
	      data: [<?php echo $companyTotal; ?>],
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
		"Administators","Technicians"
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
<script src="js/tagsinput.js"></script>
