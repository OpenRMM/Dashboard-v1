<?php 
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
$limit = intval($_GET['limit']);
if($limit == 0){
	$limit = 20;
}
$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);
$username=$user['username'];

$query = "SELECT ID,computer_type FROM computers where active='1' and computer_type='OpenRMM Server' and online='1' ORDER BY ID DESC LIMIT 1";
$results = mysqli_query($db, $query);
$computer = mysqli_fetch_assoc($results);


$json = getComputerData($computer['ID'], array("*"), "latest");
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
							$query2 = "SELECT ID FROM computers where active='1'";
							$results2= mysqli_query($db, $query2);
							$resultCount = mysqli_num_rows($results2);							
							$query = "SELECT * FROM computers WHERE active='1' ORDER BY ID DESC LIMIT 3";
							//Fetch Results
							$count = 0;
							$results = mysqli_query($db, $query);
							while($result = mysqli_fetch_assoc($results)){
								$getWMI = array("*");
								$data = getComputerData($result['ID'], $getWMI,"latest");
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
							&nbsp;&nbsp;<?php echo textOnNull($data['General']['Response'][0]['csname'],"Unavailable"); ?>
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
						<hr>
						<div class="row">
							<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h5  style="padding:7px;" class="panel-title">
											Details
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
												<?php if((int)$json['Battery']['Response'][0]['BatteryStatus']>0){ ?>
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
													$statusInt = $json['Battery'][0]['BatteryStatus'];						
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
										<div class="rsow">
											<ul class="list-group" style="margin-left:20px">
												<li class="list-group-item" style="padding:6px"><b>Hostname: </b><?php echo textOnNull($json['General']['Response'][0]['csname'], "Unavailable");?></li>
												<li class="list-group-item" style="padding:6px"><b>Current User: </b><?php echo textOnNull(basename($json['General']['Response'][0]['UserName']), "Unknown");?></li>
												<li class="list-group-item" style="padding:6px"><b>Domain: </b><?php echo textOnNull($json['General']['Response'][0]['Domain'], "N/A");?></li>
												<?php
													$lastBoot = explode(".", $json['General']['Response'][0]['LastBootUpTime'])[0];
													$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
												?>
												<li class="list-group-item" style="padding:6px"><b>Uptime: </b><?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
												<?php  
												if(count($json['WindowsActivation']['Response']) > 0) {
													$status = $json['WindowsActivation']['Response'][0]['LicenseStatus'];
													if($status!="Licensed")$status="Not activated";
													$color = ($status == "Licensed" ? "text-success" : "text-danger");
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
					<div id="menu1" class="tab-pane fade">
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
					<div id="menu2" class="tab-pane fade">
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
	        "<?php echo $siteSettings['theme']['Color 2']; ?>"
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
