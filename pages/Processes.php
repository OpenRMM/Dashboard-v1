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
	//MQTTpublish($computerID."/Commands/getProcesses","true",getSalt(20));

	$json = getComputerData($computerID, array("WMI_Processes"), $showDate);

	$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];

	$procs = $json['WMI_Processes'];
	$error = $json['WMI_Processes_error'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Processes (<?php echo count($procs);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['WMI_Processes_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_Processes_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div style="text-align:right;" class="col-md-2">
		<a href="#" title="Refresh" onclick="loadSection('Processes');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
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
			<th scope="col">PID</th>
			<th scope="col">Actions</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$count = 0;
			//Sort The array by Name ASC
			usort($procs, function($a, $b) {
				return $a['Name'] <=> $b['Name'];
			});
			foreach($procs as $key=>$proc){

				$count++;
		?>
			<tr>
				<th scope="row"><?php echo $count;?></th>
				<td><?php echo textOnNull($proc['Name'], "N/A");?></td>
				<td><?php echo textOnNull($proc['PID'], "N/A");?></td>
				<td>
				<button style="margin-top:-2px;" onclick='sendCommand("taskkill /F /PID <?php echo $proc["PID"]; ?>", "Kill <?php echo $proc["Name"]; ?> Proccess");' title="End <?php echo $proc['Name']; ?> process?" class="btn btn-danger btn-sm">
					<i style="font-size:12px;" class="fa fa-times"></i>
				</button>
				</td>
			</tr>
		<?php }
			if($count == 0){ ?>
				<tr>
					<td colspan=4><center><h5>No Processes Found.</h5></center></td>
				</tr>
		<?php }?>
		</tbody>
	</table>
</div>
<script>
	$('#searchInputProc').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			search($('#searchInputProc').val(),'Processes','<?php echo $computerID; ?>');
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