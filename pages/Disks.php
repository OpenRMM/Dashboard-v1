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
$query = "SELECT ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$result = mysqli_fetch_assoc($results);

//get update
//MQTTpublish($computerID."/Commands/getLogicalDisk","true",getSalt(20));
MQTTpublish($computerID."/Commands/getMappedLogicalDisk","true",getSalt(20),false);
MQTTpublish($computerID."/Commands/getSharedDrives","true",getSalt(20),false);
$json = getComputerData($result['ID'], array("WMI_MappedLogicalDisk", "WMI_LogicalDisk","WMI_SharedDrives"), $showDate);

$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

$mappedDisks = $json['WMI_MappedLogicalDisk']['Response'];
$disks = $json['WMI_LogicalDisk']['Response'];
$shared = $json['WMI_SharedDrives']['Response'];

$error1 = $json['WMI_MappedLogicalDisk_error'];
$error2 = $json['WMI_LogicalDisk_error'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Physical Drives
		</h4>	
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['WMI_LogicalDisk_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_LogicalDisk_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<a href="javascript:void(0)" title="Refresh" onclick="loadSection('Disks');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div class="row">
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
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Network Drives
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['WMI_LogicalDisk_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_LogicalDisk_lastUpdate']));?>
			</span>
		<?php }?>	
	</div>
</div>
<div class="row" style="margin-bottom:20px;">
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
	<div class="col-md-12" style="padding:5px;margin-left:30px;">
		<h6>No network drives found.</h6>
	</div>
<?php } ?>
</div>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Shared Drives
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['WMI_SharedDrives_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_SharedDrives_lastUpdate']));?>
			</span>
		<?php }?>	
	</div>
</div>
<div class="row" style="margin-bottom:20px;">
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