<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$json = getComputerData($computerID, array("users","network_login_profile"));
$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">	
		<div class="col-md-9">
			<h5 style="color:#0c5460">
				User Accounts (<?php echo count($json['users']['Response']);?>)
			</h5>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['users_lastUpdate']);?>
			</span>
		</div>
		<div class="col-md-3" style="text-align:right;">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Users');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button style="background:#0c5460;color:#d1ecf1" type="button" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Users','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="$('#olderData_content').html(older_data_modal);olderData('<?php echo $computerID; ?>','users','null');">
				<i class="fas fa-scroll"></i>
			</button>
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
<div class="card">
	<div class="row" style="padding:15px;">	
		<div class="col-md-12" style="overflow-x:auto;">
	
			<table id="<?php echo $_SESSION['userid']; ?>Users" style="line-height:10px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
				<thead>
					<tr style="border-bottom:2px solid #d3d3d3;">
						<th>Name</th>
						<th>Disabled</th>
						<th>Password</th>
						<th>Domain</th>
						<th>Login Count</th>
						<th>Description</th>
						<th>Actions</th>
					</tr>					
				</thead>
				<tbody>
					<?php
						$users = $json['users']['Response'];
						$users_error = $json['users_error'];
						
						$netlogins = $json['network_login_profile']['Response'];
						$netlogin_error = $json['network_login_profile_error'];
						
						$numberOfLogins = array();
						foreach($users as $user){
							//Find network login profile
							foreach($netlogins as $netlogin){
								if(strtolower($netlogin['Caption']) == strtolower($user['Name'])){
									$numberOfLogins[strtolower($user['Name'])] = $netlogin['NumberOfLogons'];
								}
							}
					?>							
					<tr>
					  <td><?php echo textOnNull(ucfirst($user['Name']), "N/A");?></td>																			
					  <td><?php if($user['Disabled']=="1"){echo "Yes"; }else{ echo "No"; } ?>	</td>						
					  <td><?php if($user['PasswordRequired']=="1"){echo "Yes"; }elseif($user['PasswordRequired']==""){ echo "Unknown"; }else{ echo "No";}?></td>					
					  <td><?php echo textOnNull($user['Domain'], "N/A");?></td>
					  <td><?php echo textOnNull($numberOfLogins[strtolower($user['Name'])], "Unknown");?></td>
					  <td title="<?php echo $user['Description']; ?>"><?php echo textOnNull(strlen($user['Description']) > 20 ? substr($user['Description'],0,20)."..." : $user['Description'], "Not Set");?></td>
					  <td>
						<?php if($user['Disabled']=="True"){ ?>
							<button onclick='sendCommand("net user <?php echo $user["Name"]; ?> /active:yes", "Enable The Account For <?php echo $user["Name"]; ?>");'style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;float:right" title="Enable User?" class="btn btn-sm btn-success"><i class="fas fa-toggle-on"></i></button>
						<?php }else{ ?>
							<button onclick='sendCommand("net user <?php echo $user["Name"]; ?> passsword123", "Reset Password For <?php echo $user["Name"]; ?> To: passsword123");' style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;float:right;margin-left:5px;" title="Resets Password To: passsword123" class="btn btn-sm btn-primary"><i class="fas fa-star-of-life"></i></button>&nbsp;
							<button onclick='sendCommand("net user <?php echo $user["Name"]; ?> /active:no", "Disable The Account For <?php echo $user["Name"]; ?>");' style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;float:right" title="Disable User?" class="btn btn-sm btn-danger"><i class="fas fa-toggle-off"></i></button>
						<?php } ?>
					  </td>	
					</tr>			
				<?php }
					if(count($users) == 0){ ?>
					<tr>
						<td colspan=8>
							<center><h6>No users found.</h6></center>
						</td>
					</tr>
				<?php }?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Users').dataTable( {
			colReorder: true,
			stateSave: true
		} );
	});
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>