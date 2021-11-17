<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$json = getComputerData($computerID, array("network_adapters"));

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

$adapters = $json['network_adapters']['Response'];
$error = $json['network_adapters_error'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">
		<h5 style="color:#0c5460" class="col-md-10">
			Network Adapters (<?php echo count($adapters);?>)<br/>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['network_adapters_lastUpdate']);?>
			</span>
		</h5>	
		<div style="text-align:right" class="col-md-2">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Network');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button style="background:#0c5460;color:#d1ecf1" type="button" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Network','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','NetworkAdapters','null');">
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
<div class="col-md-12" style="padding:5px;">
	<div class="card" style="overflow-x:auto;height:95%;background:#fff;padding:10px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
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
					<td><?php if($adapter['DHCPEnabled']=="1"){echo "Yes"; }elseif($adapter['DHCPEnabled']==""){ echo "Unknown"; }else{ echo "No";}?></td>				
					<td><?php echo textOnNull($adapter['MACAddress'],"None"); ?></td>
					<td><?php echo textOnNull($adapter['DHCPServer'],"None"); ?></td>
				</tr>			
			<?php }
				if(count($adapters) == 0){ ?>
					<tr>
						<td><h6>No Network Adapters found.</h6></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
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
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>