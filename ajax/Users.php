<?php
	include("../Includes/db.php");
	$computerID = (int)$_GET['ID'];
	$showDate = $_GET['Date'];

	//if(!$exists){ exit("<br><center><h4>No Computer Selected</h4><p>To Select A Computer, Please Visit The <a class='text-dark' href='index.php'><u>Dashboard</u></a></p></center><hr>"); }

	$json = getComputerData($computerID, array("WMI_UserAccount","WMI_NetworkLoginProfile"), $showDate);

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 1'];?>">
			User Accounts (<?php echo count($json['WMI_UserAccount']);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['WMI_UserAccount_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_UserAccount_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<a href="#" title="Refresh" onclick="loadSection('Users');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
	<div class="col-md-12" style="margin-left:-15px;">
		<div style="padding:20px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;margin-top:20px;">
			<table id="dataTable" style="line-height:10px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
				<thead>
					<tr style="border-bottom:2px solid #d3d3d3;">
						<th>Name</th>
						<th>Disabled</th>
						<th>Password</th>
						<th>Local</th>
						<th>Domain</th>
						<th>Login Count</th>
						<th>Description</th>
						<th>Actions</th>
					</tr>					
				</thead>
				<tbody>
					<?php
						$users = $json['WMI_UserAccount'];
						$users_error = $json['WMI_UserAccount_error'];
						
						$netlogins = $json['WMI_NetworkLoginProfile'];
						$netlogin_error = $json['WMI_NetworkLoginProfile_error'];
						
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
					  <td><?php echo $user['Disabled'];?>	</td>						
					  <td><?php echo textOnNull($user['PasswordRequired'], "N/A");?></td>					
					  <td><?php echo textOnNull($user['LocalAccount'], "N/A");?></td>						
					  <td><?php echo textOnNull($user['Domain'], "N/A");?></td>
					  <td><?php echo textOnNull($numberOfLogins[strtolower($user['Name'])], "N/A");?></td>
					  <td title="<?php echo $user['Description']; ?>"><?php echo textOnNull(strlen($user['Description']) > 20 ? substr($user['Description'],0,20)."..." : $user['Description'], "Not Set");?></td>
					  <td>
						<?php if($user['Disabled']=="True"){ ?>
							<button onclick='sendCommand("net", "user <?php echo $user['Name']; ?> /active:yes", "Enable The Account For <?php echo $user['Name']; ?>");' style="float:right" title="Enable User?" class="btn btn-sm btn-success"><i class="fas fa-toggle-on"></i></button>
						<?php }else{ ?>
							<button onclick='sendCommand("net", "user <?php echo $user['Name']; ?> Passw0rd!", "Reset Password For <?php echo $user['Name']; ?>");' style="float:right;margin-left:5px;" title="Reset To Simple Password?" class="btn btn-sm btn-primary"><i class="fas fa-star-of-life"></i></button>&nbsp;
							<button onclick='sendCommand("net", "user <?php echo $user['Name']; ?> /active:no", "Disable The Account For <?php echo $user['Name']; ?>");' style="float:right" title="Disable User?" class="btn btn-sm btn-danger"><i class="fas fa-toggle-off"></i></button>
						<?php } ?>
					  </td>	
					</tr>			
				<?php }
					if(count($users) == 0){ ?>
						<div class="col-md-12" style="padding:5px;">
							<center><h5>No Users found.</h5></center>
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