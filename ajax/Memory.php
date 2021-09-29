<?php
	include("../Includes/db.php");
	$computerID = (int)$_GET['ID'];
	$showDate = $_GET['Date'];
	
	//if(!$exists){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='index.php'><u>Dashboard</u></a></p></center><hr>"); }
	
	$json = getComputerData($computerID, array("WMI_ComputerSystem", "WMI_PhysicalMemory"), $showDate);

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
			Memory/Ram (<?php echo count($json['WMI_PhysicalMemory']);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['WMI_PhysicalMemory_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_PhysicalMemory_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<a href="#" title="Refresh" onclick="loadSection('Memory');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>

<div class="row">
	<div class="col-md-3" style="padding:5px;">
		<div class="card" style="height:100%;text-align:center;">
		  <div class="card-body">				
				<h6 style="color:#333;">
					Total Installed
				</h6>
				
				<div style="width:100%;border-radius:8px;background-color:<?php echo $siteSettings['theme']['Color 5'];?>;padding:10px;font-size:16px;color:#fff;">
					<b><?php echo round((int)$json['WMI_ComputerSystem'][0]['TotalPhysicalMemory'] / 1024 /1024 /1024,1);?> GB</b> 
				</div>
		  </div>
		</div>
	</div>
	</div><br><hr><br>
	<div class="row">

	<?php
		$slots = $json['WMI_PhysicalMemory'];
		$error = $json['WMI_PhysicalMemory_error'];
		foreach($slots as $slot=>$info){
	?>
		<div class="col-md-3" style="padding:5px;">
			<div class="card" style="height:100%;text-align:center;">
			  <div class="card-body">				
					<h6 style="color:#333;">
						<?php echo $info['DeviceLocator'];?>
					</h6>
					
					<div style="width:100%;border-radius:8px;background-color:<?php echo $siteSettings['theme']['Color 3'];?>;padding:10px;font-size:16px;color:#fff;">
						<b><?php echo round($info['Capacity']/1024/1024/1024);?> GB</b> <?php echo (trim($info['Speed'])!="" ? "at ".$info['Speed']." Mhz" : "");?>
					</div>
			  </div>
			</div>
		</div>
	<?php } ?>
	<?php if(count($slots) == 0){ ?>
			<div class="col-md-12" style="padding:5px;">
				<center><h5>No Memory found.</h5></center>
			</div>
	<?php }?>
</div>