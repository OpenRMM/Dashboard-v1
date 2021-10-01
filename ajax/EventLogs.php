<?php
	//$sort = (trim($_GET['sort'])!="" ? $_GET['sort'] : "ID");
	$filters = clean($_GET['filters']);
	$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];
	$json = getComputerData($computerID, array("*"), $showDate);
	$online = $json['Online'];
	$lastPing = $json['Ping'];

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h5>Logs</h5>
		<p>View The Application Event Log For This Asset. This May Help You Diagnose Any Issues That May Occur.</p>	
		<hr>
		<div style="padding:0px;">
			<table id="dataTable" style="width:125%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover  table-borderless">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
				<th scope="col">#</th>
				<th scope="col">Type</th>
				<th scope="col">Title</th>
				<th scope="col">Details</th>
				<th scope="col">Application</th>
				<th scope="col">Date/Time</th>
				</tr>
			</thead>
			<tbody>			
				<tr>
					<td colspan=6><center><h6>No Logs Found</h6></center></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>					
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