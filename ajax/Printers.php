<?php
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
	
	MQTTpublish($_SESSION['computerHostname']."/Commands/getPrinters","true",$_SESSION['computerHostname']);

	$json = getComputerData($computerID, array("WMI_Printers"), $showDate);
	
	$printers = $json['WMI_Printers'];
	$error = $json['WMI_Printers_error'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
			Printers (<?php echo count($printers);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['WMI_Printers_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_Printers_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:center;">
		<a href="#" title="Refresh" onclick="loadSection('Printers');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
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
		  <th scope="col">Port</th>
		  <th scope="col">Shared</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			foreach($printers as $key=>$print){
				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td>
				<?php echo textOnNull($print['Caption'], "[No Name]");?>
				<?php 
					if($print['Default'] == "True"){echo "<b>(Default)</b>";}
				?>
			  </td>
			  <td><?php echo textOnNull(substr($print['PortName'],0,25), "Not Set");?></td>
			  <td><?php echo textOnNull($print['Shared'], "False");?></td>
			</tr>
		<?php }
			if(count($printers) == 0){ ?>
				<tr>
					<td colspan=4><center><h5>No Printers found.</h5></center></td>
				</tr>
		<?php }?>
	   </tbody>
	</table>
</div>
<script>
	$(document).ready(function() {
			$('#dataTable').DataTable();
	});
</script>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>