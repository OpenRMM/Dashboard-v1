<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$query = "SELECT ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$result = mysqli_fetch_assoc($results);

$json = getComputerData($result['ID'], array("mapped_logical_disk", "logical_disk","shared_drives"));

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

$mappedDisks = $json['mapped_logical_disk']['Response'];
$disks = $json['logical_disk']['Response'];
$shared = $json['shared_drives']['Response'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h4 style="color:#0c5460">
				Disks
			</h4>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Disks');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button type="button" style="background:#0c5460;color:#d1ecf1" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Disks','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
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
<div class="card" style="margin-left:20px">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 style="color:#0c5460">
				Physical Drives
			</h5>	
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['logical_disk_lastUpdate']);?>
			</span>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','logical_disk','null');">
				<i class="fas fa-scroll"></i>
			</button>
		</div>
	</div>
</div>
<div style="margin-left:30px;margin-bottom:20px;" class="row">
	<?php
		foreach($disks as $disk){
			$freeSpace = $disk['FreeSpace'];
			$size = $disk['Size'];
			$used = $size - $freeSpace ;
			$usedPct = round(($used/$size) * 100);
			if($size!=0){
				$status = round((int)$used/ 1024 /1024 /1024)." GB"." of ".round((int)$disk['Size']/ 1024 /1024 /1024)." GB Used";
			}else{
				$status="No Size Avaliable";
			}
			//Determine Warning Level
			if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
				$pbColor = "red"; 
			}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
				$pbColor = "#ffa500";
			}else{ $pbColor = "#03925e"; }	
			//check if in network disks
			foreach($mappedDisks as $mappedDisk){
				if(trim($mappedDisk["Name"]) == trim($disk['Name'])){
					continue(2);
				}
			}
			if(strpos($disk["ProviderName"], ".") == false){		
	?>
		<div class="col-md-2" style="padding:5px;">
			<div class="card" style="height:80%;padding:5px;">
				<div style="text-align:center;">
				<h4 class="card-title" style="color:#333;">
					<b><?php echo $disk['Name'];?>\</b>
				</h4>
				</div>
				<div class="progress" style="background:#a4b0bd;" title="<?php echo $usedPct;?>%">
					<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
				</div>
				<center>
				<p style="color:#666;">
					<?php echo $status; ?>
				</p>
				</center>
			</div>
		</div>
	<?php 
			} 
		} 
	 if(count($disks) == 0){ ?>
		<div class="col-md-12" style="padding:5px;margin-left:30px;">
			<h6>No physical drives found.</h6>
		</div>
	<?php } ?>
</div>
<div class="card" style="margin-left:20px">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 style="color:#0c5460">
				Network Drives
			</h5>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['mapped_logical_disk_lastUpdate']);?>
			</span>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','mapped_logical_disk','null');">
				<i class="fas fa-scroll"></i>
			</button>
		</div>
	 </div>
</div>
<div class="row" style="margin-left:25px;margin-bottom:20px;">
<?php
	$count = 0;
	foreach($disks as $disk){
		$freeSpace = $disk['FreeSpace'];
		$size = $disk['Size'];
		$used = $size - $freeSpace ;
		$usedPct = round(($used/$size) * 100);
		if($size!=0){
			$status = round((int)$used/ 1024 /1024 /1024)." GB"." of ".round((int)$disk['Size']/ 1024 /1024 /1024)." GB Used";
		}else{
			$status="No Size Avaliable";
		}
		//Determine Warning Level
		if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
			$pbColor = "red"; 
		}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
			$pbColor = "#ffa500";
		}else{ $pbColor = "#03925e"; }	
		//check if in network disks
		foreach($mappedDisks as $mappedDisk){
			if(trim($mappedDisk["Name"]) == trim($disk['Name'])){
				continue(2);
			}
		}
		if(strpos($disk["ProviderName"], ".") !== false){
			$count++;
	?>
	<div class="col-md-2" style="padding:5px;">
		<div class="card" style="height:80%;padding:5px;">
			<div style="text-align:center;">
			<h4 class="card-title" style="color:#333;">
				<b><?php echo $disk['Name'];?>\</b>
			</h4>
			</div>
			<div class="progress" style="background:#a4b0bd;" title="<?php echo $usedPct;?>%">
				<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
			<center>
			<p style="color:#666;">
				<?php echo $status; ?>
			</p>
			</center>
		</div>
	</div>
<?php } } ?>
<?php if($count == 0){ ?>
	<div class="col-md-12" style="padding:5px;margin-left:25px;">
		<h6>No network drives found.</h6>
	</div>
<?php } ?>
</div>
<div class="card" style="margin-left:20px">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 style="color:#0c5460">
				Shared Drives
			</h5>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['shared_drives_lastUpdate']);?>
			</span>	
			</div>
		<div class="col-md-2" style="text-align:right;">
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','shared_drives','null');">
				<i class="fas fa-scroll"></i>
			</button>
		</div>
	</div>
</div>
<div class="row" style="margin-left:30px;margin-bottom:20px;">
<?php
	$count = 0;
	foreach($shared as $disk){
		$freeSpace = $disk['FreeSpace'];
		$size = $disk['Size'];
		$used = $size - $freeSpace ;
		$usedPct = round(($used/$size) * 100);
		if($size!=0){
			$status = round((int)$used/ 1024 /1024 /1024)." GB"." of ".round((int)$disk['Size']/ 1024 /1024 /1024)." GB Used";
		}else{
			$status="No Size Avaliable";
		}
		//Determine Warning Level
		if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
			$pbColor = "red"; 
		}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
			$pbColor = "#ffa500";
		}else{ $pbColor = "#03925e"; }	
		//check if in network disks
		foreach($mappedDisks as $mappedDisk){
			if(trim($mappedDisk["Name"]) == trim($disk['Name'])){
				continue(2);
			}
		}
		if(strpos($disk["ProviderName"], ".") !== false){
			$count++;
	?>
	<div class="col-md-2" style="padding:5px;">
		<div class="card" style="height:80%;padding:5px;">
			<div style="text-align:center;">
			<h4 class="card-title" style="color:#333;">
				<b><?php echo $disk['Name'];?>\</b>
			</h4>
			</div>
			<div class="progress" style="background:#a4b0bd;" title="<?php echo $usedPct;?>%">
				<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
			<center>
			<p style="color:#666;">
				<?php echo $status; ?>
			</p>
			</center>
		</div>
	</div>
<?php } } ?>
<?php if($count == 0){ ?>
	<div class="col-md-12" style="padding:5px;margin-left:30px;">
		<h6>No shared drives found.</h6>
	</div>
<?php } ?>
</div>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>