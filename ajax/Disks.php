<?php
	include("../Includes/db.php");
	$computerID = (int)$_GET['ID'];
	$showDate = $_GET['Date'];
	
	$query = "SELECT ID FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$result = mysqli_fetch_assoc($results);
	$exists = (bool)mysqli_num_rows($results);
	
	if(!$exists){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='index.php'><u>Dashboard</u></a></p></center><hr>"); }
	
	$json = getComputerData($result['ID'], array("WMI_MappedLogicalDisk", "WMI_LogicalDisk"), $showDate);
	
	$mappedDisks = $json['WMI_MappedLogicalDisk'];
	$disks = $json['WMI_LogicalDisk'];
	
	$error1 = $json['WMI_MappedLogicalDisk_error'];
	$error2 = $json['WMI_LogicalDisk_error'];

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
			Physical Drives (<?php echo count($disks);?>)
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
		<a href="#" title="Refresh" onclick="loadSection('Disks');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
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
				$status = round((int)$used/ 1024 /1024 /1024)." GB"." Of ".round((int)$disk['Size']/ 1024 /1024 /1024)." GB Used";
			}else{
				$status="No Size Avaliable";
			}
			
			//Determine Warning Level
			if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
				$pbColor = "red"; 
			}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
				$pbColor = "#ffa500";
			}else{ $pbColor = $siteSettings['theme']['Color 4']; }	
			
			//check if in network disks
			foreach($mappedDisks as $mappedDisk){
				if(trim($mappedDisk["Name"]) == trim($disk['Name'])){
					continue(2);
				}
			}
	?>
		<div class="col-md-2" style="padding:5px;">
			<div class="card" style="height:70%;padding:5px;">
			  <div style="text-align:center;">
				<h4 class="card-title" style="color:#333;">
					<b><?php echo $disk['Name'];?>\</b>
				</h4>
			  </div>
			  
			  <div class="progress" style="background:<?php echo $siteSettings['theme']['Color 3']; ?>;" title="<?php echo $usedPct;?>%">
				  <div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
			  </div>
			  
			  <center>
				<p style="color:#666;">
					<?php echo $status;?>
				</p>
			  </center>
			</div>
		</div>
	<?php }?>
	<?php if(count($disks) == 0){?>
		<div class="col-md-12" style="padding:5px;margin-left:30px;">
			<h6>No physical drives found.</h6>
		</div>
	<?php }?>
</div>


<?php exit; //Network drives do not seem to work, I believe this is do the the service running as system and network drives are user based ?>

<h3>
	Network Drives
	<span style="font-size:12px;color:#666;"> 
		- Last Update: <?php echo ago($json['WMI_MappedLogicalDisk_lastUpdate']);?>
	</span>
</h3><hr/>
<div class="row" style="margin-bottom:20px;">
	<?php
		foreach($mappedDisks as $disk){
			$freeSpace = $disk['FreeSpace'];
			$size = $disk['Size'];
			$used = $size - $freeSpace ;
			$usedPct = round(($used/$size) * 100);
			$status = round((int)$used/ 1024 /1024 /1024)." GB"." Of ".round((int)$disk['Size']/ 1024 /1024 /1024)." GB";
			
			//Determine Warning Level
			if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
				$pbColor = "red"; 
			}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
				$pbColor = "#ffa500";
			}else{ $pbColor = $siteSettings['theme']['Color 4']; }		
	?>
		<div class="col-md-4" style="padding:5px;">
			<div class="card" style="height:100%;padding:15px;">
			  <div style="text-align:center;">
				<h1 class="card-title" style="color:#333;">
					<b><?php echo $disk['Name'];?>\</b>
				</h1>
			  </div>
			  
			  <div class="progress" style="background:<?php echo $siteSettings['theme']['Color 3']; ?>;" title="<?php echo $usedPct;?>%">
				  <div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
			  </div>
			  
			  <center>
				<p style="color:#666;">
					<?php echo $status; ?> Used <br/>
					<b>Location:</b> <?php echo $disk['ProviderName'];?>
				</p>
			  </center>
			</div>
		</div>
	<?php }?>
	<?php if(count($mappedDisks) == 0){?>
		<div class="col-md-12" style="padding:5px;margin-left:30px;">
			<h6>No mapped network drives found.</h6>
		</div>
	<?php }?>
</div>