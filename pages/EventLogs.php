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

	$json = getComputerData($computerID, array("*"), $showDate);
	$lastPing = $json['Ping'];
	
	$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div style="padding:20px" class="col-md-12">
		<h5>Logs
			<div style="float:right;">
				<a href="#" title="Refresh" onclick="loadSection('EventLogs');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
					<i class="fas fa-sync"></i>
				</a>
			</div>
		</h5>
		<p>The Application Event Log May Help You Diagnose Any Issues That May Occur.</p>	
		<hr>
		<div style="padding:0px;">
			<table id="dataTable" style="width:125%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover table-borderless">
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
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>