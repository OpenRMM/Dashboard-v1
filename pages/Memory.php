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
if($computerID<0){ 
	?>
	<br>
	<center>
		<h4>No Asset Selected</h4>
		<p>
			To Select An Asset, Please Visit The <a class='text-dark' style="cursor:pointer" onclick='loadSection("Assets");'><u>Assets page</u></a>
		</p>
	</center>
	<hr>
<?php
	exit;
}		
//get update
//MQTTpublish($computerID."/Commands/getPhysicalMemory","true",getSalt(20));

$json = getComputerData($computerID, array("General", "physical_memory"));

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 style="color:#0c5460">
				Memory/Ram (<?php echo count($json['physical_memory']['Response']);?>)
			</h5>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['physical_memory_lastUpdate']);?>
			</span>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<div class="btn-group">
				<button onclick="loadSection('Memory');" style="background:#0c5460;color:#d1ecf1" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button style="background:#0c5460;color:#d1ecf1" type="button" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="loadSection('Memory','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','physical_memory','null');">
				<i class="fas fa-scroll"></i>
			</button>
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
<div style="margin-left:5px;" class="row">
<?php
	$slots = $json['physical_memory']['Response'];
	$error = $json['physical_memory_error'];
	foreach($slots as $slot=>$info){
?>
	<div class="col-md-3" style="padding:5px;">
		<div class="card" style="height:80%;text-align:center;">
			<div class="card-body">				
				<h6 style="color:#333;">
					<?php echo textOnNull($info['DeviceLocator'],"Unknown Location");?>
				</h6>
				<div style="width:100%;border-radius:8px;background-color:#343a40;padding:10px;font-size:16px;color:#fff;">
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