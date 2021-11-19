<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$json = getComputerData($computerID, array("usbhub", "desktop_monitor", "keyboard", "pointing_device", "sound_devices", "serial_port", "pnp_entities"));

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h4 style="color:#0c5460">
				Attached Devices
			</h4>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Attached_Devices');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button type="button" style="background:#0c5460;color:#d1ecf1" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Attached_Devices','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
		</div>
	</div>
</div>
<?php if($online=="0"){ ?>
	<div  style="border-radius: 0px 0px 4px 4px;" class="alert alert-danger" role="alert">
		&nbsp;&nbsp;&nbsp;This Agent is offline		
	</div>
<?php 
}else{
	echo"<br>";
}
?>
<div style="margin-left:20px" class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 style="color:#0c5460">
				Displays
			</h5>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['desktop_monitor_lastUpdate']);?>
			</span>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','desktop_monitor','null');">
				<i class="fas fa-scroll"></i>
			</button>
		</div>
	</div>
</div>
	<div class="row" style="margin-bottom:20px;padding-left:40px;">
		<?php
			$monitors = $json['desktop_monitor']['Response'];
			$error = $json['desktop_monitor_error'];
			foreach($monitors as $device){		
		?>
			<div class="col-md-2" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#343a40;color:#fff">
				  <div style="text-align:center;color:#fff;margin-top:10px">
					<h6 style="color:#fff" class="card-title">
						<?php echo $device['Name'];?>
					</h6>
					<!--<p><?php echo $device['Description'];?></p> They All Seem To Match Name-->
				  </div>
				</div>
			</div>
		<?php }?>
		<?php if(count($monitors) == 0){?>
			<div class="col-md-12" style="padding:5px;margin-left:30px;">
				<h6>No monitors found.</h6>
			</div>
		<?php }?>
	</div>
	<div style="margin-left:20px" class="card">
		<div class="row" style="padding:15px;">
			<div class="col-md-10">
				<h5 style="color:#0c5460">
					USB Hubs
				</h5>
				<span style="font-size:12px;color:#666;"> 
					Last Update: <?php echo ago($json['usbhub_lastUpdate']);?>
				</span>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','usbhub','null');">
					<i class="fas fa-scroll"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:20px;padding-left:40px;">
		<?php
			$hubs = $json['usbhub']['Response'];
			$error = $json['usbhub_error'];
			foreach($hubs as $hub){	
		?>
			<div class="col-md-2" style="padding:3px;">
				<div class="card" style="height:80%;paddings:5px;background:#343a40;color:#fff">
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
				<h6>No USB hubs found.</h6>
			</div>
		<?php }?>
	</div>
	<div style="margin-left:20px" class="card">
		<div class="row" style="padding:15px;">
			<div class="col-md-10">
				<h5 style="color:#0c5460">
					Keyboards
				</h5>
				<span style="font-size:12px;color:#666;"> 
					Last Update: <?php echo ago($json['keyboard_lastUpdate']);?>
				</span>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','keyboard','null');">
					<i class="fas fa-scroll"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:20px;padding-left:40px;">
		<?php
			$keyboards = $json['keyboard']['Response'];
			$error = $json['keyboard_error'];
			foreach($keyboards as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#343a40;color:#fff">
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
				<h6>No keyboards found.</h6>
			</div>
		<?php }?>
	</div>
	<div style="margin-left:20px" class="card">
		<div class="row" style="padding:15px;">
			<div class="col-md-10">
				<h5 style="color:#0c5460">
					Pointing Devices
				</h5>
				<span style="font-size:12px;color:#666;"> 
					Last Update: <?php echo ago($json['pointing_device_lastUpdate']);?>
				</span>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','pointing_device','null');">
					<i class="fas fa-scroll"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:20px;padding-left:40px;">
		<?php
			$pointingDevices = $json['pointing_device']['Response'];
			$error = $json['pointing_device_error'];
			foreach($pointingDevices as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#343a40;color:#fff">
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
				<h6>No pointing devices found.</h6>
			</div>
		<?php }?>
	</div>
	<div style="margin-left:20px" class="card">
		<div class="row" style="padding:15px;">
			<div class="col-md-10">
				<h5 style="color:#0c5460">
					Sound Devices
				</h5>
				<span style="font-size:12px;color:#666;"> 
					Last Update: <?php echo ago($json['sound_devices_lastUpdate']);?>
				</span>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','sound_devices','null');">
					<i class="fas fa-scroll"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:20px;padding-left:40px;">
		<?php
			$SoundDevices = $json['sound_devices']['Response'];
			$error = $json['sound_devices_error'];
			foreach($SoundDevices as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#343a40;color:#fff">
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
				<h6>No sound cards found.</h6>
			</div>
		<?php }?>
	</div>
	<div style="margin-left:20px" class="card">
		<div class="row" style="padding:15px;">
			<div class="col-md-10">
				<h5 style="color:#0c5460">
					Serial Ports
				</h5>
				<span style="font-size:12px;color:#666;"> 
					Last Update: <?php echo ago($json['serial_port_lastUpdate']);?>
				</span>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','serial_port','null');">
					<i class="fas fa-scroll"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:20px;padding-left:40px;">
		<?php
			$SerialPorts = $json['serial_port']['Response'];
			$error = $json['serial_port_error'];
			foreach($SerialPorts as $device){	
		?>
			<div class="col-md-3" style="padding:5px;">
				<div class="card" style="height:80%;paddings:5px;background:#343a40;color:#fff">
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
				<h6>No serial devices found.</h6>
			</div>
		<?php }?>
	</div>
</div>
<div style="margin-left:20px" class="card">
	<div class="row" style="padding:15px;">
			<div class="col-md-10">
				<h5 style="color:#0c5460">
					Plug And Play Devices (<?php echo count($json['pnp_entities']['Response']); ?>)
				</h5>
				<span style="font-size:12px;color:#666;"> 
					Last Update: <?php echo ago($json['pnp_entities_lastUpdate']);?>
				</span>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','pnp_entities','null');">
					<i class="fas fa-scroll"></i>
				</button>
			</div>
		</div>
	</div>
	<div style="margin-left:20px;overflow-x:auto;padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;margin-top:20px;">
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
					$PnPEntity = $json['pnp_entities']['Response'];
					$error = $json['pnp_entities_error'];
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
					<td><?php echo textOnNull($device['Manufacturer'],"Unknown");?></td>
					<td><?php echo textOnNull($device['Description'],"Unknown");?></td>
					<td><?php echo textOnNull($device['PNPClass'],"Unknown");?></td>
					</tr>
					<?php }
					if($key == 0){ ?>
						<tr>
							<td colspan=5><center><h6>No plug and play devices found.</h6></center></td>
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