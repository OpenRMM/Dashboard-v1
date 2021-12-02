<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$getEvent=clean(base64_decode($_GET['other']));
if($getEvent=="" or $getEvent=="force"){
	$getEvent="application";
}
//MQTTpublish($computerID."/Commands/getEventLogs",'{"userID":'.$_SESSION['userid'].',"data":"'.$getEvent'"}',getSalt(20),false);
//sleep(3);
$json = getComputerData($computerID, array("event_log_".$getEvent));
	
$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

$events = $json["event_log_".$getEvent]['Response'];
$error = $json["event_log_".$getEvent."_error"];
?>
<div class="row" style="position:relative">
	<div class="col-md-12" >
		<div class="card" style="padding:20px;margin-bottom:-1px">
			<h5 style="color:0c5460">Event Logs<br>
				<span style="color:#000;font-size:12px">Last Update: <?php echo ago($json['event_log_'.$getEvent.'_lastUpdate']);?></span>
				<div style="float:right;">
					<div class="btn-group">
						<button onclick="loadSection('Asset_Event_Logs');" style="background:#0c5460;color:#d1ecf1" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
						<button type="button" style="background:#0c5460;color:#d1ecf1" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="fas fa-sort-down"></i>
						</button>
						<div class="dropdown-menu">
							<a onclick="force='true'; loadSection('Asset_Event_Logs','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
						</div>
					</div>
				</div>
			</h5>	
		</div>
	</div>
</div>
<?php if($online=="0"){ ?>
	<div  style="border-radius: 0px 0px 4px 4px;" class="alert alert-danger" role="alert">
		<i class="fas fa-ban"></i>&nbsp;&nbsp;&nbsp;This Agent is offline		
	</div>
	<?php 
}else{
	echo"<br>";
}
?>
<div class="row" style="overflow:auto">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" >
		<div class="card" style="padding:20px;overflow:auto" >
			<div class="tab-block">
				<form style="margin-bottom:-10px" method="POST" action="/" style="display:inline">
					<ul class="nav nsv-tabs">
						<?php if($getEvent=="application"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
						<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
							<a onclick="loadSection('Asset_Event_Logs', '<?php echo $computerID; ?>','latest','application');" href="javascript:void(0)" <?php if($getEvent=="application"){ echo 'class="text-white"'; } ?> data-toggle="tab">Application Logs</a>
						</li>
						<?php if($getEvent=="security"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
						<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
							<a onclick="loadSection('Asset_Event_Logs', '<?php echo $computerID; ?>','latest','security');" href="javascript:void(0)" <?php if($getEvent=="security"){ echo 'class="text-white"'; } ?> data-toggle="tab">Security Logs</a>
						</li>
						<?php if($getEvent=="system"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
						<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
							<a onclick="loadSection('Asset_Event_Logs', '<?php echo $computerID; ?>','latest','system');" href="javascript:void(0)" <?php if($getEvent=="system"){ echo 'class="text-white"'; } ?> data-toggle="tab">System Logs</a>
						</li>
						<?php if($getEvent=="setup"){ $style="background:#333;color:#fff;"; }else{ $style="background:#f3f3f3;color:#333;"; } ?>
						<li style="<?php echo $style; ?>padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class=" active">
							<a onclick="loadSection('Asset_Event_Logs', '<?php echo $computerID; ?>','latest','setup');" href="javascript:void(0)" <?php if($getEvent=="setup"){ echo 'class="text-white"'; } ?> data-toggle="tab">Setup Logs</a>
						</li>
					</ul>
				</form>
			</div>
			<div class="tab-contsent p3s0" style="padding:0px;margin-top:5px;overflow:auto">
				<table id="<?php echo $_SESSION['userid']; ?>Event_logs" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
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
									if (strlen($message) >= 100) {
										echo substr($message, 0, 100). " ... " . substr($message, -5);
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
</div>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Event_logs').DataTable( {
			//"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
			"paging": false,
			"order": [],
			stateSave: true,
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