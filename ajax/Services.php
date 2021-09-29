<?php
	include("../Includes/db.php");
	$computerID = (int)$_GET['ID'];
	$search = $_GET['search'];
	$showDate = $_GET['Date'];

	//if(!$exists){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='index.php'><u>Dashboard</u></a></p></center><hr>"); }

	$json = getComputerData($computerID, array("WMI_Services"), $showDate);

	$services = $json['WMI_Services'];
	$error = $json['WMI_Services_error'];

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
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
		<a href="#" title="Refresh" onclick="loadSection('Services');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div  style="padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
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
					<button title="Start Sevice" class="btn btn-sm btn-success" style="margin-top:-2px;padding:5px;padding-top:2px;padding-bottom:2px;border:none;" onclick='sendCommand("cmd", "net start <?php echo $name[1]; ?>", "Kill <?php echo $proc['Name']; ?> service");'>
						<i style="font-size:12px;" class="fas fa-play"></i> Start
					</button>
				  <?php }elseif($state="Running"){ ?>
					<button title="Stop Service" class="btn btn-sm btn-danger" style="margin-top:-2px;padding:5px;padding-top:2px;padding-bottom:2px;background:maroon;border:none;" onclick='sendCommand("net", "stop <?php echo $name[1]; ?> /y", "Kill <?php echo $proc['Name']; ?> service");'>
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