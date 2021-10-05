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
		
	//get update
	MQTTpublish($computerID."/Commands/getPhysicalMemory","true",$computerID);

	$json = getComputerData($computerID, array("WMI_ComputerSystem", "WMI_PhysicalMemory"), $showDate);

	$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];
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
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>