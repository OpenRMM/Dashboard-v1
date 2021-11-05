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
$computerID = (int)base64_decode($_GET['ID']);
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
//MQTTpublish($computerID."/Commands/getPhysicalMemory","true",getSalt(20));

$json = getComputerData($computerID, array("General", "PhysicalMemory"), $showDate);

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Memory/Ram (<?php echo count($json['PhysicalMemory']['Response']);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['PhysicalMemory_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS", strtotime($showDate));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<div class="btn-group">
			<button onclick="loadSection('Memory');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
			<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-sort-down"></i>
			</button>
			<div class="dropdown-menu">
				<a onclick="loadSection('Memory','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
			</div>
		</div>
		<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div class="row">
<?php
	$slots = $json['PhysicalMemory']['Response'];
	$error = $json['PhysicalMemory_error'];
	foreach($slots as $slot=>$info){
?>
	<div class="col-md-3" style="padding:5px;">
		<div class="card" style="height:100%;text-align:center;">
			<div class="card-body">				
				<h6 style="color:#333;">
					<?php echo $info['DeviceLocator'];?>
				</h6>
				<div style="width:100%;border-radius:8px;background-color:#333;padding:10px;font-size:16px;color:#fff;">
					<b><?php echo round($info['Capacity']/1024/1024/1024);?> GB</b> <?php echo (trim($info['Speed'])!="" ? "at ".$info['Speed']." Mhz" : "");?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
	<?php if(count($slots) == 0){ ?>
		<div class="col-md-12" style="padding:5px;">
			<center>
				<h5>
					No Memory found.
				</h5>
			</center>
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