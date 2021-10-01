<?php
	$search = $_GET['search'];
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
	MQTTpublish($_SESSION['computerHostname']."/Commands/getProducts","true",$_SESSION['computerHostname']);

	$json = getComputerData($computerID, array("WMI_Product"), $showDate);

	$programs = $json['WMI_Product'];
	$error = $json['WMI_Product_error'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
			Installed Programs (<?php echo count($programs);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['WMI_Product_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_Product_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div style="text-align:right;" class="col-md-2">
		<a href="#" title="Refresh" onclick="loadSection('Programs');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
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
		  <th scope="col">Vendor</th>
		  <th scope="col">Location</th>
		  <th scope="col">Installed</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			$count = 0;

			//Sort The array by Name ASC
			usort($programs, function($a, $b) {
				return $a['Name'] <=> $b['Name'];
			});

			foreach($programs as $key=>$program){
				//ignore empty name
				if(trim($program['Name']) == ""){
					continue;
				}

				if($search!=""){
					if(stripos($program['Name'], $search) !== false){ }else{ continue; }
				}
				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td><?php echo $program['Caption'];?></td>
			  <td><?php echo $program['Vendor'];?></td>
			  <td><?php echo textOnNull($program['InstallLocation'],"Unknown");?></td>
			  <td><?php echo date("m/d/Y", strtotime($program['InstallDate']));?></td>
			</tr>
		<?php }
				if($count == 0){ ?>
					<tr>
						<td colspan=6><center><h5>No Programs found.</h5></center></td>
					</tr>
			<?php }?>
	   </tbody>
	</table>
</div>
<script>
	$('#searchInputPrograms').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			search($('#searchInputPrograms').val(),'Programs','<?php echo $computerID; ?>');
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