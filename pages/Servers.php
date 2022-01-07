<?php 
$computerID = (int)base64_decode($_GET['other']);
checkAccess($_SESSION['page']);

$query = "SELECT * FROM servers ORDER BY ID ASC";
$results = mysqli_query($db, $query);
$serverCount = mysqli_num_rows($results);

$query2 = "SELECT * FROM servers WHERE ID='".$computerID."' LIMIT 1";
$results2 = mysqli_query($db, $query2);
?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h5 style="color:#0c5460">All Servers (<?php echo $serverCount;?>)
		<button href="javascript:void(0)" title="Refresh" onclick="loadSection('Servers','','','<?php echo $computerID; ?>');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</button>
		<button class="btn-sm btn btn-light" style="margin:5px;background:#0ac282;;float:right;color:#fff" title="Download Server Application">
			 <i class="fas fa-download"></i> Download Server Application
		</button>
		<p>Listing all servers running the OpenRMM server application</p>
	</h5>	
</div>
<div class="card table-card">

	<div style="padding:10px;overflow-x:auto">	
		<table style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover table-borderless">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
					<th scope="col">ID</th>
					<th scope="col">Hostname</th>
					<th scope="col">RAM Usage</th>
					<th scope="col">CPU Usage</th>
					<th scope="col">Disk Usage</th>
					<th scope="col">Architecture</th>
					<th scope="col">Server Version</th>
					<th scope="col">Uptime</th>
					<th scope="col">Actions</th>
				</tr>
		  </thead>
		  <tbody>
			<?php
				//Fetch Results
				while($server = mysqli_fetch_assoc($results)){
					$count++;	
					$data = jsonDecode($server['statistics'],true)['json'];	
					$status=$data['status'];
					$arch=$data['architecture'];
					$version=$data['server_version'];
					if($data['uptime']=="0"){
						$uptime="Never";
					}else{
						$uptime="-".$data['uptime']." seconds" ;	
						$uptime = ago(date("Y-m-d h:i:sa", strtotime($uptime)));	
					}
					$usedPct = $data['disk_usage']['percent'];
					if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
						$pbColor = "red";
					}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
						$pbColor = "#ffa500";
					}else{ $pbColor = "#03925e"; }

					$ram = $data['swap_memory'];					
					$size2 = $ram['total'];
					$used2 = $ram['used'];
					$usedPct2 = round(($used2/$size2) * 100);
					if($usedPct2 > $siteSettings['Alert Settings']['Memory']['Danger'] ){
						$pbColor2 = "red";
					}elseif($usedPct2 > $siteSettings['Alert Settings']['Memory']['Warning']){
						$pbColor2 = "#ffa500";
					}else{ $pbColor2 = "#03925e"; }

					$usedPct3 = $data['cpu_percent'];					
					if($usedPct3 > $siteSettings['Alert Settings']['Processor']['Danger'] ){
						$pbColor3 = "red";
					}elseif($usedPct3 > $siteSettings['Alert Settings']['Processor']['Warning']){
						$pbColor3 = "#ffa500";
					}else{ $pbColor3 = "#03925e"; }

					$log=$data['logs'];
					
					if($computerID==$server['ID']){
						$color = "color:#0c5460;";
						$background="background:#d1ecf1;";
					}else{
						$color = "color:#000;";
						$background="";
					}
					if(strtotime($server['last_update']) < strtotime('-2 minutes')) {
						$status="Offline";
					}else{
						$status="Online";
					}
				?>
				<tr style="<?php echo $background.$color; ?>">
					<td><div style="margin-top:8px"><?php echo $server['ID'];?></div></td>
					<td style="cursor:pointer" onclick="loadSection('Servers', '','','<?php echo $server['ID']; ?>');">
						<h6 style="<?php echo $color; ?>margin-top:8px">
							<?php if($status=="Offline") {?>
								<i class="fas fa-server" style="color:#666;font-size:12px;" title="Offline"></i>
							<?php }else{?>
								<i class="fas fa-server" style="color:green;font-size:12px;" title="Online"></i>
							<?php }?>
							<?php echo ucwords($server['hostname']);?>
						</h6>
					</td>
					<td>
						<div class="progress" style="margin-top:11px;height:10px;background:#a4b0bd" title="<?php echo $usedPct2;?>%">
							<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor2;?>;width:<?php echo $usedPct2;?>%" aria-valuenow="<?php echo $usedPct2;?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</td>
					<td>
						<div class="progress" style="margin-top:11px;height:10px;background:#a4b0bd" title="<?php echo $usedPct3;?>%">
							<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor3;?>;width:<?php echo $usedPct3;?>%" aria-valuenow="<?php echo $usedPct3;?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</td>
					<td>
						<div class="progress" style="margin-top:11px;height:10px;background:#a4b0bd" title="<?php echo $usedPct;?>%">
							<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</td>
					<td>
						<div style="margin-top:8px"><?php echo $arch;?></div>
					</td>	
					<td>
						<div style="margin-top:8px"><?php echo $version;?></div>
					</td>
					<td>
						<div style="margin-top:8px"><?php echo str_replace("ago","",$uptime); ?></div>
					</td>
					<td>
						<form style="margin-top:8px">
							<?php if($server['active']=="1"){ ?>
								<button onclick="deleteServer('<?php echo $server['ID']; ?>','0')" id="delServer<?php echo $server['ID']; ?>"  type="button" title="Disable Server" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
									<i class="fas fa-trash" ></i>				
								</button>
								<button id="actServer<?php echo $server['ID']; ?>" onclick="deleteServer('<?php echo $server['ID']; ?>','1')" type="button" title="Enable Server" style="display:none;margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-success btn-sm">
									<i class="fas fa-plus" ></i>
								</button>
							<?php }else{ ?>
								<button id="actServer<?php echo $server['ID']; ?>" onclick="deleteServer('<?php echo $server['ID']; ?>','1')" type="button" title="Enable Server" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-success btn-sm">
									<i class="fas fa-plus" ></i>
								</button>
								<button id="delServer<?php echo $server['ID']; ?>" onclick="deleteServer('<?php echo $server['ID']; ?>','0')" type="button" title="Disable Server" style="display:none;margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
									<i class="fas fa-trash" ></i>				
								</button>
							<?php 
								} 
								if($status=="Online"){
							?>						
								<button onclick="serverStatus('<?php echo $server['ID']; ?>','restart')" type="button" title="Restart Server" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-warning btn-sm">
									<i class="fas fa-redo" ></i>				
								</button>
								<button onclick="serverStatus('<?php echo $server['ID']; ?>','shutdown')" type="button" title="Power Off Server" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
									<i class="fas fa-power-off" ></i>				
								</button>
							<?php } ?>
						</form>
					</td>
				</tr>	
			<?php }?>
		    </tbody>
		</table>
	</div>	
</div>
<?php 
	$computer = mysqli_fetch_assoc($results2);
	$asset = jsonDecode($computer['statistics'],true)['json'];
	
	if(strtotime($computer['last_update']) < strtotime('-2 minutes')) {
		$serverStatus="Offline";
		$serverStatus_color="background:#f8d7da;color:#721c24";
	}else{
		$serverStatus="Online";
		$serverStatus_color="background:#d4edda;color:#155724";
	} 	
?>
<div style="width:97%;" class=" mx-auto card table-card">
	<div class="card-header">
		Server Details
		<?php 
		if($computerID>0){ 
			echo " for ";  
			echo $computer['hostname']; ?>
			<span style="<?php echo $serverStatus_color; ?>;margin-left:20px" class="badge">
			<?php echo $serverStatus; ?></span> 
		<?php } ?>
		<hr>
	</div>
	<div style="padding:20px">
	<?php if($computerID>0){ ?>
		<div class="row">
			<div class="col-xs-6 col-sm-6 col-md-4 col-lg-6" style="margin-top:-30px;padding:3px;">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h5  style="padding:7px;" class="panel-title">
							Details
						</h5>
					</div>
					<div class="panel-body" style="height:260px;">	
						<div class="roaw">
							<ul class="list-group" style="margin-left:10px">
								<li class="list-group-item" style="z-index:2;padding:6px;width:100%"><b>Processor: </b><?php echo textOnNull(str_replace(" 0 ", " ",str_replace("CPU", "",str_replace("(R)","",str_replace("(TM)","",$asset['processor'])))), "N/A");?></li>
								<li class="list-group-item" style="padding:6px"><b>Python Version: </b><?php echo textOnNull($asset['python_version'], "N/A");?></li>
								<li class="list-group-item" style="padding:6px"><b>Architecture: </b><?php echo textOnNull($asset['architecture'], "N/A");?></li>
								<li class="list-group-item" style="padding:6px">
									<span style="margin-left:0px"><b>Local IP Addresses: </b>
										<ul class="list-group" style="margin-left:10px">
											<?php foreach($asset['net_adapters'] as $x => $net) { 
												$test=array_pop($net);
												if($x>3){
													break;
												}
											?>
												<li class="list-group-item" style="padding:6px"><?php print_r($test['ipv4']); ?></li>
											<?php } ?>
										</ul>
									</span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-6 col-sm-6 col-md-4 col-lg-6" style="margin-top:-30px;padding:3px;">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h5 style="padding:7px" class="panel-title"></h5>
					</div>
					<div class="panel-body" style="height:260px;">
						<div class="rsow">
							<ul class="list-group" style="margin-left:20px">
							<?php
								$uptime="-".$asset['uptime']." seconds" ;	
								if($asset['uptime']=="0"){
									$uptime="Never";
								}else{	
									$uptime = ago(date("Y-m-d h:i:sa", strtotime($uptime)));
								}
								
							?>
								<li class="list-group-item" style="padding:6px"><b>Uptime: </b><?php echo str_replace("ago","",$uptime); ?></li>	
								<?php
									$boot="-".$asset['boot_time']." seconds" ;
									if($asset['boot_time']=="0"){
										$boot="Never";
									}else{	
										$boot = ago(date("Y-m-d h:i:sa", strtotime($boot)));
									}
								?>	
								<li class="list-group-item" style="padding:6px"><b>Boot time: </b><?php echo $boot; ?></li>		
								<li class="list-group-item" style="padding:6px"><b>Server Version: </b><?php echo textOnNull($asset['server_version'],"N/A"); ?></li>
								<li class="list-group-item" style="padding:6px"><b>MySQL Server: </b><?php echo textOnNull($asset['mysql_server'],"N/A"); ?></li>
								<li class="list-group-item" style="padding:6px"><b>MQTT Server: </b><?php echo textOnNull($asset['mqtt_server'],"N/A"); ?></li>
								<li class="list-group-item" style="padding:6px"><b>Operating System: </b><?php echo textOnNull($asset['os'],"N/A"); ?></li>

							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xs-6 col-sm-6 col-md-12 col-lg-12" style="padding:3px;">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h5 style="padding:7px" class="panel-title">Logs</h5>
					</div>
					<div class="panel-body" style="s:285px;">
						<div class="rsow">
							<table id="<?php echo $_SESSION['userid']; ?>Servers" style="line-height:10px;;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover table-borderless">
								<thead>
									<tr>
										<th scope="col">Event</th>
										<th scope="col">Date</th>
										<th scope="col">Type</th>
										<th scope="col">Date</th>			  
									</tr>
								</thead>
								<tbody>	
									<?php 
									$count=0;
									foreach ($asset['logs'] as $log) {
										$count++;
									?>
									<tr>
										<td><?php echo $log['Title']; ?></td>
										<td><?php echo $log['Message']; ?></td>
										<td><?php echo $log['Type']; ?></td>
										<td><?php echo $log['Time']; ?></td>
									</tr>
									<?php } ?>
									<?php if ($count==0){?>
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
		<?php }else{ ?>
			<center><h6>Select a Server to view more information about it.</h6></center>
		<?php } ?>
	</div>
</div>
<script>
$(document).ready(function() {
    $('#<?php echo $_SESSION['userid']; ?>Servers').dataTable( {
		colReorder: true,
		stateSave: true
	} );
});
</script>