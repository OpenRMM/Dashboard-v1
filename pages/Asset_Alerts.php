<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],"null");


$json = getComputerData($computerID, array("general"));
$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
?>
<div style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div style="padding:20px;overflow:auto" class="col-md-12">
		<h5 style="color:#0c5460">Alert Configuration
			<div style="float:right;">
				<button title="Refresh" onclick="loadSection('Asset_Alerts');" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
					<i class="fas fa-sync"></i>
				</button>
			</div>
			<br>
			<p>Configure Notifications For This Asset.</p>
		</h5>
		<hr>
		<button data-toggle="modal" data-target="#editAlert" onclick="$('#alertCompany').hide();$('#alertID').val('<?php echo $computerID; ?>');" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> &nbsp;Add Alert</button><hr>
		<table class="table table-hover table-borderless" id="datatable">
			<tr>
				<th>Name</th>
				<th >Alert Details</th>
				<th>Current Value</th>
				<th style="float:right">Actions</th>
			</tr>
			<?php
			$count=0;
			$query = "SELECT * FROM alerts WHERE active='1' and computer_id='".$computerID."' ORDER BY ID ASC";
			$results = mysqli_query($db, $query);
			while($alert = mysqli_fetch_assoc($results)){
				$count++;
				if($alert['last_run']!=""){ $last_run=$alert['last_run'];}else{ $last_run="Never";}
				$details=jsonDecode($alert['details'],true);
				$json = getComputerData($computerID, array("general","logical_disk","agent","windows_activation"));			
				switch ($details['json']['Details']['Condition']) {
					case "Total Alert Count":
					  $currentValue="0";
					break;
					case "Total Ram/Memory":
					$currentValue=formatBytes($json['general']['Response'][0]['Totalphysicalmemory'],0);
					break;
					case "Available Disk Space":
						$currentValue=formatBytes($json['logical_disk']['Response'][0]['FreeSpace']);
					break;
					case "Total Disk Space":
						$currentValue=formatBytes($json['logical_disk']['Response'][0]['Size']);
					break;
					case "Domain":
						$currentValue=$json['general']['Response'][0]['Domain'];
					break;
					case "Public IP Address":
						$currentValue=$json['general']['Response'][0]['ExternalIP']["ip"];
					break;
					case "Antivirus":
						$currentValue=$json['general']['Response'][0]['Antivirus'];
					break;
					case "Agent Version":
						$currentValue=$json['agent']['Response'][0]['Version'];
					break;
					case "Total User Accounts":
						$currentValue="0";
					break;
					case "Command Received":
						$currentValue="0";
					break;
					case "Agent Comes Online":
						if($results['online']=="0")$status="Offline";
						if($results['online']=="1")$status="Online";
						$currentValue=$status;
					break;
					case "Agent Goes Offline":
						if($results['online']=="0")$status="Offline";
						if($results['online']=="1")$status="Online";
						$currentValue=$status;
					break;
					case "Windows Activation":
						$status = $json['windows_activation']['Response'][0]['LicenseStatus'];
						if($status!="Licensed")$status="Not activated";
						$currentValue=$status;
					break;
					case "Local IP Address":
						$currentValue=$json['general']['Response'][0]['PrimaryLocalIP'];
					break;
					case "Last Update":
						$currentValue=$json['Ping'];
					break;

					default:
					 $currentValue="unknown";
				  }
			?>
			<tr>
				<td><?php echo $alert['name']; ?></td>
				<td>If <b><?php echo $details['json']['Details']['Condition']."</b> ".$details['json']['Details']['Comparison']." ".$details['json']['Details']['Value']; ?></td>
				<td><?php echo textOnNull($currentValue,"unknown"); ?></td>
				<td style="float:right">
					<form action="/" method="post" style="display:inline;">
						<input type="hidden" value="delAlert" name="type">
						<input type="hidden" value="<?php echo $alert['ID'];?>" name="ID">
						<button type="submit" title="Delete Alert" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
							<i class="fas fa-trash"></i>
						</button>
					</form>	
				</td>
			</tr>
			<?php } 
			if($count == 0){ ?>
				<tr>
					<td colspan=4><center><h6>Once you create an Alert, it will show up here.</h6></center></td>
				</tr>
		<?php }?>
		</table>
  	</div>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This Computer Appears To Be Offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>
