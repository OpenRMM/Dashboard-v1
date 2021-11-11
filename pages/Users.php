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
	//MQTTpublish($computerID."/Commands/getUsers","true",getSalt(20));
	
	$json = getComputerData($computerID, array("Users","NetworkLoginProfile"), $showDate);

	$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			User Accounts (<?php echo count($json['Users']['Response']);?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;">
				Last Update: <?php echo ago($json['Users_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS", strtotime($showDate));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<div class="btn-group">
			<button onclick="loadSection('Users');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
			<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-sort-down"></i>
			</button>
			<div class="dropdown-menu">
				<a onclick="loadSection('Users','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
			</div>
		</div>
		<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
	<div class="col-md-12" style="margin-left:-15px;">
		<div style="overflow-x:auto;padding:20px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;margin-top:20px;">
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
						$users = $json['Users']['Response'];
						$users_error = $json['Users_error'];
						
						$netlogins = $json['NetworkLoginProfile']['Response'];
						$netlogin_error = $json['NetworkLoginProfile_error'];
						
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
					  <td><?php echo textOnNull($user['LocalAccount'], "N/A");?></td>						
					  <td><?php echo textOnNull($user['Domain'], "N/A");?></td>
					  <td><?php echo textOnNull($numberOfLogins[strtolower($user['Name'])], "Unknown");?></td>
					  <td title="<?php echo $user['Description']; ?>"><?php echo textOnNull(strlen($user['Description']) > 20 ? substr($user['Description'],0,20)."..." : $user['Description'], "Not Set");?></td>
					  <td>
						<?php if($user['Disabled']=="True"){ ?>
							<button onclick='sendCommand("net user <?php echo $user["Name"]; ?> /active:yes", "Enable The Account For <?php echo $user["Name"]; ?>");' style="float:right" title="Enable User?" class="btn btn-sm btn-success"><i class="fas fa-toggle-on"></i></button>
						<?php }else{ ?>
							<button onclick='sendCommand("net user <?php echo $user["Name"]; ?> passsword123", "Reset Password For <?php echo $user["Name"]; ?> To: passsword123");' style="float:right;margin-left:5px;" title="Resets Password To: passsword123" class="btn btn-sm btn-primary"><i class="fas fa-star-of-life"></i></button>&nbsp;
							<button onclick='sendCommand("net user <?php echo $user["Name"]; ?> /active:no", "Disable The Account For <?php echo $user["Name"]; ?>");' style="float:right" title="Disable User?" class="btn btn-sm btn-danger"><i class="fas fa-toggle-off"></i></button>
						<?php } ?>
					  </td>	
					</tr>			
				<?php }
					if(count($users) == 0){ ?>
						<td colspan=8>
							<center><h6>No users found.</h6></center>
					</td>
				<?php }?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#dataTable').dataTable( {
			colReorder: true
		} );
	});
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>