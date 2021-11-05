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
$getEvent=clean(base64_decode($_GET['other']));
if($getEvent=="" or $getEvent=="force"){
	$getEvent="Application";
}
//MQTTpublish($computerID."/Commands/getEventLogs",'{"userID":'.$_SESSION['userid'].',"data":"'.$getEvent'"}',getSalt(20),false);
//sleep(3);
$json = getComputerData($computerID, array("EventLog_".$getEvent), $showDate);
	
$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

$events = $json["EventLog_".$getEvent]['Response'];
$error = $json["EventLog_".$getEvent."_error"];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div style="padding:20px" class="col-md-12">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">Event Logs<br>
		<span style="color:#000;font-size:12px">Last Update: <?php echo ago($json['EventLog_'.$getEvent.'_lastUpdate']);?></span>
			<div style="float:right;">
				<div class="btn-group">
					<button onclick="loadSection('EventLogs');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
					<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fas fa-sort-down"></i>
					</button>
					<div class="dropdown-menu">
						<a onclick="loadSection('EventLogs','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>

					</div>
				</div>
			</div>
		</h4>	
		<hr>
		<div class="tab-block">
			<form style="margin-bottom:-10px" method="POST" action="/" style="display:inline">
				<ul class="nav nsv-tabs">
					<?php if($getEvent=="Application"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
					<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
						<a onclick="loadSection('EventLogs', '<?php echo $computerID; ?>','latest','Application');" href="javascript:void(0)" <?php if($getEvent=="Application"){ echo 'class="text-white"'; } ?> data-toggle="tab">Application Logs</a>
					</li>
					<?php if($getEvent=="Security"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
					<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
						<a onclick="loadSection('EventLogs', '<?php echo $computerID; ?>','latest','Security');" href="javascript:void(0)" <?php if($getEvent=="Security"){ echo 'class="text-white"'; } ?> data-toggle="tab">Security Logs</a>
					</li>
					<?php if($getEvent=="System"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
					<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
						<a onclick="loadSection('EventLogs', '<?php echo $computerID; ?>','latest','System');" href="javascript:void(0)" <?php if($getEvent=="System"){ echo 'class="text-white"'; } ?> data-toggle="tab">System Logs</a>
					</li>
					<?php if($getEvent=="Setup"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
					<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
						<a onclick="loadSection('EventLogs', '<?php echo $computerID; ?>','latest','Setup');" href="javascript:void(0)" <?php if($getEvent=="Setup"){ echo 'class="text-white"'; } ?> data-toggle="tab">Setup Logs</a>
					</li>
	
				</ul>
			</form>
		<div class="tab-content p30" style="padding:0px;margin-top:5px">
			<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
				<thead>
					<tr style="border-bottom:2px solid #d3d3d3;">
						<th scope="col">Source</th>
						<th scope="col">Message</th>
						<th scope="col">Type</th>
						<th scope="col">Time</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach($events as $key=>$event){
							$type="";
							$eventColor="";
							$count++;
							if (strpos($event['Type'], 'WARNING') !== false) {
								$eventColor="background:#fff3cd;color:#856404";
								$type="Warning";
							}
							if (strpos($event['Type'], 'INFORMATION') !== false) {
								$eventColor="background:#e2e3e5;color:#383d41";
								$type="Information";
							}
							if (strpos($event['Type'], 'SUCCESS') !== false) {
								$eventColor="background:#d4edda;color:#155724";
								$type="Success";
							}
							if (strpos($event['Type'], 'ERROR') !== false or strpos($event['Type'], 'FAILURE') !== false) {
								$eventColor="background:#f8d7da;color:#721c24";
								$type="Error";
							}
							$message=htmlspecialchars($event['Message']);
							$message=clean(str_replace(">","",$message));
							if($message==""){continue;}
							
					?>
						<tr style="<?php echo $eventColor; ?>">
						<td scope="row"><?php echo $event['Source'];?></td>
						<td style="color:#333" title="<?php echo $message; ?>">
							<?php 
								if (strlen($message) >= 150) {
									echo substr($message, 0, 150). " ... " . substr($message, -5);
								}else {
									echo $message;
								}
							?>
						</td>
						<td><?php echo $type;?></td>
						<td><?php echo textOnNull(substr($event['Time'],0,25), "Not Set");?></td>
						</tr>
					<?php }
						if(count($events) == 0){ ?>
							<tr>
								<td colspan=4><center><h6>No events found.</h6></center></td>
							</tr>
					<?php }?>
				</tbody>
				</table>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#dataTable').DataTable( {
			//"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
			"paging": false,
			"order": [],
			colReorder: true
		} );
	} );
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