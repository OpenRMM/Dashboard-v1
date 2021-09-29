<?php
	include("../Includes/db.php");
	$computerID = (int)$_GET['ID'];
	$showDate = $_GET['Date'];
	
	//if(!$exists){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='index.php'><u>Dashboard</u></a></p></center><hr>"); }

	$json = getComputerData($computerID, array("WMI_NetworkAdapters"), $showDate);

	$adapters = $json['WMI_NetworkAdapters'];
	$error = $json['WMI_NetworkAdapters_error'];

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>" class="col-md-10">
		Network Adapters (<?php echo count($adapters);?>)<br/>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['WMI_NetworkAdapters_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_NetworkAdapters_lastUpdate']));?>
			</span>
		<?php }?>
	</h4>
	
	<div style="text-align:right" class="col-md-2">
		<a href="#" title="Refresh" onclick="loadSection('Network');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>

<div class="col-md-12" style="padding:5px;">
	<div class="card" style="height:95%;background:#fff;padding:10px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
	  <div>
		<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover dataTable table-borderless">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
					<th>Description</th>
					<th>DHCP</th>
					<th>MAC Address</th>
					<th>DHCP Server</th>
				</tr>
			</thead>
			<tbody>
	<?php
		foreach($adapters as $key=>$adapter){
	?>
				<tr>
					<td><?php echo textOnNull($adapter['Description'],"None");?></td>	
					<td><?php echo textOnNull($adapter['DHCPEnabled'],"None");?></td>				
					<td><?php echo textOnNull($adapter['MACAddress'],"None"); ?></td>
					<td><?php echo textOnNull($adapter['DHCPServer'],"None"); ?></td>
				</tr>			
	<?php }
		if(count($adapters) == 0){ ?>
			<div class="col-md-12" style="padding:5px;">
				<center><h5>No Network Adapters found.</h5></center>
			</div>
	<?php }?>
			</tbody>
		</table>
	  </div>
	</div>
</div>
	<script>
		$(document).ready(function() {
			  $('#dataTable').DataTable();
		});
	</script>