<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page']);

$query = "SELECT username,nicename,user_color FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);
$username=$user['username'];
$query = "SELECT * FROM servers where active='1' ORDER BY ID ASC";
$results = mysqli_query($db, $query);
$hostnames=array();
while($computer = mysqli_fetch_assoc($results)){
	if(strtotime($computer['last_update']) < strtotime('-2 minutes')) {
		continue;
	}
	$computerID= (int)$computer['ID'];
	$data = jsonDecode($computer['statistics'],true)['json'];	
	$status=$data['status'];
	$arch=$data['architecture'];
	$version=$data['server_version'];
	$uptime=$data['uptime'];
	$hostname = $computer['hostname'];
	array_push($hostnames,$hostname);
	continue;
}
$getWMI = array("general","logical_disk","bios","processor","agent","battery","windows_activation","antivirus","firewall");
$json = getComputerData($computerID, $getWMI);
//print_r(getComputerData($computer['ID'], array("*")));

$hostnames=implode(", ", $hostnames);
if($hostname==""){
	$serverStatus="Offline";
	$serverStatus_color="background:#f8d7da;color:#721c24";
}else{
	$serverStatus="Online";
	$serverStatus_color="background:#d4edda;color:#155724";
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
		<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
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
							$date = strtotime($data['general_lastUpdate']);
							if($date < strtotime('-1 days')) {
								$result['online']="0";
							}
							$name = textOnNull(crypto("decrypt", $result['name'],$result['hex']), "not defined");
							$count++;
							$icons = array("desktop","server","laptop","tablet","allinone","other");
							if(in_array(strtolower(str_replace("-","",$result['computer_type'])), $icons)){
								$icon = strtolower(str_replace("-","",$result['computer_type']));
								if($icon=="allinone")$icon="tv";
								if($icon=="tablet")$icon="tablet-alt";
								if($icon=="other")$icon="microchip";
							}else{
								$icon = "desktop";
							}  
					?>
					<li onclick="loadSection('Asset_General', '<?php echo $result['ID']; ?>');" class="list-group-item secbtn" style="text-align:left;cursor:pointer;">
						<span style="font-size:14px;cursor:pointer">
							<span class="tooltips tooltipHelper">
								<?php if($result['online']=="0") {?>
									<i class="fas fa-<?php echo $icon;?>" style="color:#666;font-size:12px;" title="Offline"></i>
								<?php }else{?>
									<i class="fas fa-<?php echo $icon;?>" style="color:green;font-size:12px;" title="Online"></i>
								<?php }?>
								&nbsp;<?php echo textOnNull(strtoupper($data['general']['Response'][0]['csname']),"Unavailable");?>
								<span class="tooltiptext">
									<div style="padding:5px">
										<div style='text-align:left;'>
											<h6><?php echo textOnNull(strtoupper($data['general']['Response'][0]['csname']),"Unavailable");?></h6>
											<ul style="padding:2px;color:#fff;background:#333" class="list-group">
												<li style="padding:2px;color:#fff;background:#333" class="list-group-item">Last updated: <?php echo ago($data['general_lastUpdate']);?></li>
												<?php
													$lastBoot = explode(".", $data['general']['Response'][0]['LastBootUpTime'])[0];
													$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
												?>
												<li style="padding:2px;color:#fff;background:#333" class="list-group-item">Uptime: <?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></</li>
												<li style="padding:2px;color:#fff;background:#333" class="list-group-item">Client Name: <?php echo $name; ?></li>
											</ul>
										</div>
									</div>
								</span>
							</span>
						</span>
					</li>
					<?php }  ?>
				</ul>
			</div>
		</div>	
		<?php if($siteSettings['Service_Desk']=="Enabled"){ ?>
		<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
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
					<li onclick="loadSection('Service_Desk_Ticket', '<?php echo $result['ID']; ?>');" class="list-group-item secbtn" style="text-align:left;cursor:pointer;">
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
					<a style="text-decoration:none" title="View Note" class="noteList" onclick="$('#notetitle').text('<?php echo $note[0]; ?>');$('#notedesc').text('<?php echo $note[1]; ?>');" data-bs-toggle="modal" data-bs-target="#viewNoteModal">
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
			<button style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none;color:#fff" data-bs-toggle="modal" data-bs-target="#noteModal" title="Create New Note" class="btn btn-sm">Create New Note</button>
		</div>
	</div>			
	<?php 	
	//Get stats
		$companyArray= "";
		$query3 = "SELECT ID FROM computers where active='1' and company_id='0'";
		$count3 = mysqli_num_rows(mysqli_query($db, $query3));
		$colors = array("#dedede","#0c5460","#333");
		if($count3 > 0){
			$computerColors="'#dedede',";
			$companyArray= "'Not Assigned',";
			$companyTotal =$count3.",";
		}

		$query = "SELECT ID, name,hex FROM companies where active='1'";
		$count2=0;
		$count3=1;
		$companys = mysqli_query($db, $query);
		while($result = mysqli_fetch_assoc($companys)){
			$companyArray.= "'".crypto('decrypt', $result['name'],$result['hex'])."',";
			$query = "SELECT ID FROM computers where active='1' and company_id='".$result['ID']."'";
			$count = mysqli_num_rows(mysqli_query($db, $query));
			$companyTotal.=$count.",";
			$computerColors .= "'".$colors[$count3]."',";
			$count3++;
			if($count3==3){
				$count3=0;
			}
			$count2 = $count2 + $count;
			$count=0;
		}
		
		$companyArray= rtrim($companyArray,',');
		$companyTotal=rtrim($companyTotal,',');
		if($count2==0){
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
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mh-40">
			<div class="row">
				<div class="col-md-4 py-1">
					<div class="card">
						<div style="cursor:pointer;" onclick="loadSection('Technicians');" class="card-body">
							<canvas data-centerval="" id="chDonut2"></canvas>
							<h6 style="text-align:center">Technicians</h6>
						</div>
					</div>
				</div>
				<div class="col-md-4 py-1">
					<div class="card">
						<div style="cursor:pointer;" onclick="loadSection('Customers');" class="card-body">
							<canvas data-centerval="" id="chDonut1"></canvas>
							<h6 style="text-align:center"><?php echo $msp."s"; ?></h6>
						</div>
					</div>
				</div>
				<div class="col-md-4 py-1">
					<div class="card">
						<div style="cursor:pointer;" onclick="loadSection('Assets');"  class="card-body">
							<canvas data-centerval="" id="chDonut3"></canvas>
							<h6 style="text-align:center">Asset Status</h6>
						</div>
					</div>
				</div>
			</div>
			<div style="padding:15px" class="panel panel-default">
				<h6 style="margin-left:15px;display:inline;"><span style="cursor:pointer" onclick="loadSection('Servers');">Detected OpenRMM servers: <?php echo textOnNull($hostnames,"No online servers detected"); ?></span>
					<h6 style="display:inline;margin-left:25px;margin-top:0px;position:absolute;font-size:16px">
					
						<span style="<?php echo $serverStatus_color; ?>" class="badge"><?php echo $serverStatus; ?></span>
						<?php if($serverupdate){ ?>
						<span style="color:#856404;background:#fff3cd;cursor:pointer" class="badge "><i class="fas fa-upload"></i>&nbsp; Update to v.2.0.3</span>
						<?php } ?>
						
					</h6>
					<?php if($_SESSION['accountType']=="Admin" and $serverStatus=="Online"){ ?>	
						<div style="float:right;margin-top:-5px" class="btn-group">
							<button type="button" class=" btn-danger btn btn-sm"><i class="fas fa-power-off"></i></button>
							<button type="button" class="btn-danger btn dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-sort-down"></i>
							</button>
							<div class="dropdown-menu">
								<a onclick="serverStatus('<?php echo $computerID; ?>','restart');" title="Restart Server"class="dropdown-item" href="javascript:void(0)">Restart Server</a>
								<a onclick="serverStatus('<?php echo $computerID; ?>','shutdown');" title="Shutdown Server" class="bg-danger text-white dropdown-item" href="javascript:void(0)">Shutdown Server</a>
							</div>
						</div>
						<div style="float:right;margin-top:-5px;margin-right:5px;" class="btn-group">
							<button type="button" class="btn btn-sm btn-warning"><i class="fas fa-database"></i> </button>
							<button type="button" class="btn dropdown-toggle-split btn-sm btn-warning" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-sort-down"></i>
							</button>
							<div class="dropdown-menu">
								<a onclick="serverStatus('<?php echo $computerID; ?>','restart service');" title="Restart Server Service"class="dropdown-item" href="javascript:void(0)">Restart Service</a>
								<a onclick="serverStatus('<?php echo $computerID; ?>','stop service');" title="Stop Server Service" class="bg-warning text-white dropdown-item" href="javascript:void(0)">Stop Service</a>
							</div>
						</div>					
					<?php } ?>
				</h6>		
			</div>
			<div style="padding:15px" class="card">
				<div class="tab-block">
					<ul class="nav nav-pills">
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-bs-toggle="pill" class="nav-link active" data-bs-toggle="tab" href="#Alerts">Alerts</a>
						</li>
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-bs-toggle="pill" class="nav-link" data-bs-toggle="tab" href="#Tasks">Tasks</a>
						</li>
					</ul>
				</div>
			
				<div class="tab-content" style="padding-top:10px;overflow:hidden" >
					
					<div id="Alerts" class="tab-pane fade-in active">
						<button data-bs-toggle="modal" data-bs-target="#editAlert" onclick="$('#alertCompany').show();$('#alertID').val('');" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> &nbsp;Add Alert</button><hr>
						<p>Configure alerts for all assets or assets within a certain <?php echo strtolower($msp); ?></p>
						<table class="table table-hover table-borderless table-striped" id="datatable">
							<tr>
								
								<th>Name</th>
								<th >Details</th>
								<th><?php echo $msp; ?></th>
								<th>Actions</th>
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
								<td>
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
						<button data-bs-toggle="modal" data-bs-target="#editTrigger" class="btn btn-sm btn-warning"><i class="fas fa-plus"></i> &nbsp;Add Task</button><hr>
						<table class="table table-hover table-borderless table-striped" id="datatable">
							<tr>
								<th>Name</th>
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
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mh-40 offset-md-3 offset-lg-3">
			<div class="row">
				<div  class="panel-default">
					<div class="panel-heading">
						<h5 style="padding:7px" class="panel-title">
							Recent Activity Feeds
						</h5>
					</div>
					<div  class="panel-body" style="background:#fff;overflow:auto">
						<div class="rosw">
							<table id="<?php echo $_SESSION['userid']; ?>Activity_Logs" style="font-size:14px;margin-top:0px;font-family:Arial;" class="table-striped table table-hover table-borderless">
								<thead>
									<tr>
										<th scope="col">Event</th>
										<th scope="col">Date</th>
										<th style="display:none" scope="col">Simple Date</th>			  
									</tr>
								</thead>
								<tbody>			
								<?php
									//Fetch Results
									$count=0;
									$query = "SELECT * FROM user_activity WHERE active='1' ORDER BY ID DESC";
									$results = mysqli_query($db, $query);
									$userCount = mysqli_num_rows($results);
									while($activity = mysqli_fetch_assoc($results)){
										$count++;
															
									?>
										<tr>
											<td><?php echo crypto('decrypt',$activity['activity'],$activity['hex']); ?></td>	
											<td style="display:none" ><?php echo (date("m/d/y\ h:i",$activity['date'])); ?></td>
											<td><?php echo ago(date("m/d/y\ h:i",$activity['date'])); ?></td>				
										</tr>
										<?php }
										if($count==0){?>
											<tr>
												<td colspan=4><center><h6>No activity found.</h6></center></td>
											</tr>
									<?php } ?>				
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Error_Log').dataTable( {
			"paging": false,
			"order": [],
			stateSave: true,
			colReorder: true
		} );
	});
	$('#<?php echo $_SESSION['userid']; ?>Activity_Logs').dataTable( {
		"paging": true,
		"order": [[ 1, "desc" ]],
		stateSave: true,
		colReorder: true,
		"language": {
			"info": ""
		}
	} );
	
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
	       <?php echo $computerColors; ?>
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