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
	MQTTpublish($computerID."/Commands/getServices","true",$computerID);

	$json = getComputerData($computerID, array("WMI_Services"), $showDate);

	$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];

	$services = $json['WMI_Services'];
	$error = $json['WMI_Services_error'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Services (<?php echo count($services);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['WMI_Services_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_Services_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div style="text-align:center;" class="col-md-2">
		<a href="#" title="Refresh" onclick="loadSection('Services');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div style="padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
	<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
	  <thead>
		<tr style="border-bottom:2px solid #d3d3d3;">
		  <th scope="col">#</th>
		  <th scope="col">Name</th>
		  <th scope="col">Display Name</th>
		  <th scope="col">Description</th>
		  <th scope="col">Actions</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			foreach($services as $key=>$service){
				$name= explode("|",$service['Name']);
				$state = $name[0];
				if($state=="Running")$color=$siteSettings['theme']['Color 4'];
				if($state=="Stopped")$color="maroon";
				if($search!=""){
					if(stripos($name[1], $search) !== false){ }else{ continue; }
				}
				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td><?php echo textOnNull($name[1], "[No Name]");?></td>
			  <td><?php echo textOnNull(substr($service['DisplayName'],0,35), "Not Set");?></td>
			  <td><?php echo textOnNull(strlen($service['Description']) > 70 ? substr($service['Description'],0,70)."..." : $service['Description'], "Not Set");?></td>
			  <td>
				  <?php if($state=="Stopped"){ ?>
					<button title="Start Sevice" class="btn btn-sm btn-success" style="margin-top:-2px;padding:5px;padding-top:2px;padding-bottom:2px;border:none;" onclick='sendCommand("net start <?php echo $name[1]; ?>", "Kill <?php echo $proc['Name']; ?> service");'>
						<i style="font-size:12px;" class="fas fa-play"></i> Start
					</button>
				  <?php }elseif($state="Running"){ ?>
					<button title="Stop Service" class="btn btn-sm btn-danger" style="margin-top:-2px;padding:5px;padding-top:2px;padding-bottom:2px;background:maroon;border:none;" onclick='sendCommand("net stop <?php echo $name[1]; ?> /y", "Kill <?php echo $proc['Name']; ?> service");'>
						<i style="font-size:12px;" class="fas fa-times"></i> Stop
					</button>
				  <?php } ?>
			  </td>
		<?php }
			if($count == 0){ ?>
				<tr>
					<td colspan=5>
						<center><h5>No Services Found.</h5></center>
					</td>
				</tr>
		<?php }?>
	   </tbody>
	</table>
</div>
<script>
	$('#searchInputServices').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			search($('#searchInputServices').val(),'Services','<?php echo $computerID; ?>');
		}
	});
</script>
<script>
	$(document).ready(function() {
		$('#dataTable').DataTable();
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