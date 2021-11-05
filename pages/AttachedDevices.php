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
//get update
MQTTpublish($computerID."/Commands/getVideoConfiguration","true",getSalt(20),false);
MQTTpublish($computerID."/Commands/getPointingDevice","true",getSalt(20),false);
MQTTpublish($computerID."/Commands/getDesktopMonitor","true",getSalt(20),false);
MQTTpublish($computerID."/Commands/getKeyboard","true",getSalt(20),false);
//MQTTpublish($computerID."/Commands/getPnPEntitys","true",getSalt(20));

$json = getComputerData($computerID, array("USBHub", "DesktopMonitor", "Keyboard", "PointingDevice", "SoundDevices", "SerialPort", "PnPEntities"), $showDate);

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Attached Devices
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['WMUSBHub_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS", strtotime($showDate));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<div class="btn-group">
			<button onclick="loadSection('AttachedDevices');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
			<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-sort-down"></i>
			</button>
			<div class="dropdown-menu">
				<a onclick="loadSection('AttachedDevices','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
			</div>
		</div>
		<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div class="col-md-12" style="margin-left:20px">
	<h6 style="color:#000"><b>Displays</b></h6>
	<div class="row" style="margin-bottom:10px;padding-left:40px;">
		<?php
			$monitors = $json['DesktopMonitor']['Response'];
			$error = $json['DesktopMonitor_error'];
			foreach($monitors as $device){		
		?>
			<div class="col-md-2" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#696969;color:#fff">
				  <div style="text-align:center;color:#fff;margin-top:10px">
					<h6 style="color:#fff" class="card-title">
						<?php echo $device['Name'];?>
					</h6>
					<!--<p><?php echo $device['Description'];?></p> They All Seem To mAtch Name-->
				  </div>
				</div>
			</div>
		<?php }?>
		<?php if(count($monitors) == 0){?>
			<div class="col-md-12" style="padding:5px;margin-left:30px;">
				No monitors found.
			</div>
		<?php }?>
	</div>
	<hr/>
	<h6 style="color:#000"><b>USB Hubs</b></h6>
	<div class="row" style="margin-bottom:10px;padding-left:40px;">
		<?php
			$hubs = $json['USBHub']['Response'];
			$error = $json['USBHub_error'];
			foreach($hubs as $hub){	
		?>
			<div class="col-md-2" style="padding:3px;">
				<div class="card" style="height:80%;paddings:5px;background:#696969;color:#fff">
				  <div style="text-align:center;color:#fff;margin-top:10px">
					<h6 style="color:#fff" class="card-title">
						<?php echo $hub['Name'];?>
					</h6>
				  </div>
				</div>
			</div>
		<?php }?>
		<?php if(count($hubs) == 0){?>
			<div class="col-md-12" style="padding:5px;margin-left:30px;">
				No USB hubs found.
			</div>
		<?php }?>
	</div>
	<hr>
	<h6 style="color:#000"><b>Keyboards</b></h6>
	<div class="row" style="margin-bottom:10px;padding-left:40px;">
		<?php
			$keyboards = $json['Keyboard']['Response'];
			$error = $json['Keyboard_error'];
			foreach($keyboards as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#696969;color:#fff">
				  	<div style="text-align:center;color:#fff;margin-top:10px">
						<h6 style="color:#fff" class="card-title">
							<b><?php echo $device['Caption'];?></b>
							<p><?php echo $device['Description'];?></p>
						</h6>
				    </div>
				</div>
			</div>
		<?php }?>
		<?php if(count($keyboards) == 0){?>
			<div class="col-md-12" style="padding:5px;margin-left:30px;">
				No keyboards found.
			</div>
		<?php }?>
	</div>
	<hr>
	<h6 style="color:#000"><b>Pointing Devices</b></h6>
	<div class="row" style="margin-bottom:10px;padding-left:40px;">
		<?php
			$pointingDevices = $json['PointingDevice']['Response'];
			$error = $json['PointingDevice_error'];
			foreach($pointingDevices as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#696969;color:#fff">
				  <div style="text-align:center;color:#fff;margin-top:10px">
					<h6 style="color:#fff" class="card-title">
						<?php echo $device['Name'];?>
					</h6>
					<!--<p><?php echo $device['Description'];?></p> They All Seem To mAtch Name-->
				  </div>
				</div>
			</div>
		<?php }?>
		<?php if(count($pointingDevices) == 0){?>
			<div class="col-md-12" style="padding:5px;margin-left:30px;">
				No pointing devices found.
			</div>
		<?php }?>
	</div>
	<hr>
	<h6 style="color:#000"><b>Sound</b></h6>
	<div class="row" style="margin-bottom:10px;padding-left:40px;">
		<?php
			$SoundDevices = $json['SoundDevices']['Response'];
			$error = $json['SoundDevices_error'];
			foreach($SoundDevices as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#696969;color:#fff">
				  <div style="text-align:center;color:#fff;margin-top:10px">
					<h6 style="color:#fff" class="card-title">
						<?php echo $device['Name'];?>
					</h6>
					<!--<p><?php echo $device['Description'];?></p> They All Seem To mAtch Name-->
				  </div>
				</div>
			</div>
		<?php }?>
		<?php if(count($SoundDevices) == 0){?>
			<div class="col-md-12" style="padding:5px;margin-left:30px;">
				No sound cards found.
			</div>
		<?php }?>
	</div>
	<hr>
	<h6 style="color:#000"><b>Serial Ports</b></h6>
	<div class="row" style="margin-bottom:10px;padding-left:40px;">
		<?php
			$SerialPorts = $json['SerialPort']['Response'];
			$error = $json['SerialPort_error'];
			foreach($SerialPorts as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#696969;color:#fff">
				  <div style="text-align:center;color:#fff;margin-top:10px">
					<h6 style="color:#fff" class="card-title">
						<?php echo $device['DeviceID'];?>
					</h6>
					<p><?php echo $device['Description'];?></p>
				  </div>
				</div>
			</div>
		<?php }?>
		<?php if(count($SerialPorts) == 0){?>
			<div class="col-md-12" style="padding:5px;margin-left:30px;">
				No serial devices found.<br><br>
			</div>
		<?php }?>
	</div>
</div>
	<hr>
	<h6 style="color:#000"><b>Plug And Play Devices (<?php echo count($json['PnPEntities']['Response']); ?>)</b></h6>
	<div style="padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;margin-top:20px;">
		<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
				<th scope="col">#</th>
				<th scope="col">Name</th>
				<th scope="col">Manufacturer</th>
				<th scope="col">Description</th>
				<th scope="col">PNP Class</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$PnPEntity = $json['PnPEntities']['Response'];
					$error = $json['PnPEntities_error'];
					//Sort The array by Name ASC
					usort($PnPEntity, function($a, $b) {
						return $a['PNPClass'] <=> $b['PNPClass'];
					});
					$key  = 0;
					foreach($PnPEntity as $device){
						if(trim($device['Caption'])==""){continue;}
						$key++;
				?>
					<tr>
					<th scope="row"><?php echo $key;?></th>
					<td><?php echo $device['Caption'];?></td>
					<td><?php echo $device['Manufacturer'];?></td>
					<td><?php echo $device['Description'];?></td>
					<td><?php echo $device['PNPClass'];?></td>
					</tr>
					<?php }
					if($key == 0){ ?>
						<tr>
							<td colspan=5><center><h5>No PNP Devices found.</h5></center></td>
						</tr>
				<?php }?>
			</tbody>
		</table>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#dataTable').dataTable( {
			colReorder: true
		} );
	});
</script>
<script>
		$(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>