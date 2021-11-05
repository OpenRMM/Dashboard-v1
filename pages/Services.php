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
	//MQTTpublish($computerID."/Commands/getServices","true",getSalt(20));

	$json = getComputerData($computerID, array("Services"), $showDate);

	$query = "SELECT online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];

	$services = $json['Services']['Response'];
	$error = $json['Services_error'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Services (<?php echo count($services);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['Services_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS", strtotime($showDate));?>
			</span>
		<?php }?>
	</div>
	<div style="text-align:center;" class="col-md-2">
		<div class="btn-group">
			<button onclick="loadSection('Services');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
			<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-sort-down"></i>
			</button>
			<div class="dropdown-menu">
				<a onclick="loadSection('Services','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
			</div>
		</div>
		<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
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
				$state = $service['State'];
				if($state=="Running"){$color=$siteSettings['theme']['Color 4'];}
				if($state=="Stopped"){$color="maroon";}

				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td><?php echo textOnNull($service['Caption'], "[No Name]");?></td>
			  <td><?php echo textOnNull(substr($service['DisplayName'],0,35), "Not Set");?></td>
			  <td><?php echo textOnNull(strlen($service['Description']) > 70 ? substr($service['Description'],0,70)."..." : $service['Description'], "Not Set");?></td>
			  <td>
				  <?php if($state=="Stopped"){ ?>
					<button title="Start Sevice" class="btn btn-sm btn-success" style="margin-top:-2px;" onclick='sendCommand("net start <?php echo $name[1]; ?>", "Kill <?php echo $proc['Name']; ?> service");'>
						<i style="font-size:12px;" class="fas fa-play"></i>
					</button>
				  <?php }elseif($state="Running"){ ?>
					<button title="Stop Service" class="btn btn-sm btn-danger" style="margin-top:-2px;" onclick='sendCommand("net stop <?php echo $name[1]; ?> /y", "Kill <?php echo $proc['Name']; ?> service");'>
						<i style="font-size:12px;" class="fas fa-times"></i>
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