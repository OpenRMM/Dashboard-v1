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
//MQTTpublish($computerID."/Commands/getNetwork","true",getSalt(20));

$json = getComputerData($computerID, array("NetworkAdapters"), $showDate);

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

$adapters = $json['NetworkAdapters']['Response'];
$error = $json['NetworkAdapters_error'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>" class="col-md-10">
		Network Adapters (<?php echo count($adapters);?>)<br/>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['NetworkAdapters_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS", strtotime($showDate));?>
			</span>
		<?php }?>
	</h4>	
	<div style="text-align:right" class="col-md-2">
		<div class="btn-group">
			<button onclick="loadSection('Network');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
			<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-sort-down"></i>
			</button>
			<div class="dropdown-menu">
				<a onclick="loadSection('Network','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>

			</div>
		</div>
		<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
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