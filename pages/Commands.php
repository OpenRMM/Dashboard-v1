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
	$query = "SELECT online, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$computer = mysqli_fetch_assoc($results);

	$json = getComputerData($computerID, array("*"), $showDate);
	$online = $computer['online'];
	$lastPing = $json['Ping'];

	$json = getComputerData($computerID, array("WMI_Product"), $showDate);

	$programs = $json['WMI_Product'];
	$error = $json['WMI_Product_error'];
	
?>
<div class="row"  style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div style="padding:20px" class="col-md-12">
		<h5>Commands
			<div style="float:right;">
				<a href="#" title="Refresh" onclick="loadSection('Commands');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
					<i class="fas fa-sync"></i>
				</a>
			</div>
		</h5>
		
		<p>View & Execute Commands On This Asset.</p>
		<hr>
		<div class="row">
			<div style="" class="col-md-5">
			<h5>Execute A Command</h5>
			
			<?php if($online=="1"){ ?>
				<div style="height:200px">		
					<br>			
					<button class="btn btn-sm btn-default" data-dismiss="modal" type="button" style="margin:5px;width:45%;border:1px solid #000" data-toggle="modal" data-target="#terminalModal">
						<i class="fas fa-terminal" style="margin-top:3px;float:left"></i> Terminal
					</button>
					<button class="btn btn-sm btn-warning" data-dismiss="modal" type="button" style="margin:5px;width:45%;border:none" data-toggle="modal" data-target="#agentAlertModal">
						<i class="fas fa-comment" style="margin-top:3px;float:left"></i> One-way Message
					</button>
					<button data-dismiss="modal" class="btn btn-success btn-sm" type="button" style="display:inline;margin:5px;width:45%;border:none" onclick='sendCommand("reg add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 0 /f", "Enable Remote Desktop");'>
						<i class="fas fa-desktop" style="float:left;margin-top:3px"></i> Enable Remote Desktop
					</button>
					<button data-dismiss="modal" class="btn btn-primary btn-sm" type="button" style="display:inline;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;width:45%;border:none" onclick='sendCommand("reg add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 1 /f", "Disable Remote Desktop");'>
						<i class="fas fa-desktop" style="float:left;margin-top:3px"></i> Disable Remote Desktop
					</button>
					<button data-dismiss="modal" class="btn btn-primary btn-sm" type="button" style="display:inline;margin:5px;width:45%;border:none" onclick="sendCommand('Netsh Advfirewall set allprofiles state on', 'Enable Firewall');">
						<i class="fas fa-fire-alt" style="float:left;margin-top:3px"></i> Enable Firewall
					</button>
					<button data-dismiss="modal" class="btn btn-primary btn-sm" type="button" style="color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;display:inline;margin:5px;color:#fff;width:45%;border:none" onclick="sendCommand('Netsh Advfirewall set allprofiles state off', 'Disable Firewall');">
						<i class="fas fa-fire-alt" style="float:left;margin-top:3px"></i> Disable Firewall
					</button>
				</div>
				<br>
			</div>
			<div style="" class="col-md-7">
				<h5>Run A Custom Script</h5><br>
					<div>
						<div class="form-group">
							<label for="langscript">Script Language</label>
							<select required name="scriptType" class="form-control" id="langscript">
							<option value="0">Batch</option>
							<option disabled value="1">VB Script</option>
							</select>
						</div>	
						<textarea required name="customScript" id="scriptForm" class="form-control" style="width:100%;height:210px">
:: This batch file checks for network connection problems.
ECHO OFF
:: View network connection details
ipconfig /all
:: Check if google.com is reachable
ping google.com
:: Run a traceroute to check the route to google.com
tracert google.com
PAUSE
						</textarea>
						<br>
						<button onclick=" var text = $('#scriptForm').val();sendCommand(text , 'Run Custom Script');" class="btn btn-success btn-sm" style="float:right">Run Script &nbsp;&nbsp;<i class="fas fa-play"></i></button>
			</div>
				<?php }else{ ?>
					<br>
					<h6 style="text-align:left;color:red"><b>Asset Must Be Online To Execute Commands</b></h6>
				<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
	<div  style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;" class="row">
		<div class="col-md-12">
			<?php 
				$query = "SELECT ID, time_received,command, arg, expire_after,status,time_sent FROM commands WHERE status='Sent' or status='Received' AND ComputerID='".$result['hostname']."' ORDER BY ID DESC LIMIT 100";
				$results = mysqli_query($db, $query);
				$commandCount = mysqli_num_rows($results);
			?>
			<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">				
			  <thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
				  <th scope="col">Command</th>
				  <!--<th scope="col">Expire Time</th>-->
				  <th scope="col">Time Sent</th>
				  <th scope="col">Status</th>
				  <th scope="col"></th>
				</tr>
			  </thead>
			  <tbody>
				<?php
					//Fetch Results
					while($command = mysqli_fetch_assoc($results)){
						$count++;
					?>
					<tr>
					  <td title="<?php echo substr($command['command'], 0, 400) . '...'; ?>"><b><?php echo substr($command['command'], 0, 40) . '...'; ?></b></td>
					  <!--<td><?php echo strtolower($command['expire_after']);?> Minutes</td>-->
					  <td><?php echo $command['time_sent'];?></td>
					 
						  <?php if($command['time_received']!=""){
									$timer = $command['time_received'];
							   }else{
								  $timer = "Not Received";
							   } ?>
					  <td title="<?php echo $timer; ?>" ><b><?php echo $command['status'];?></b></td>
					   <td>
						   <form action="index.php" method="POST">
								<input type="hidden" name="type" value="DeleteCommand"/>
								<input type="hidden" name="ID" value="<?php echo $command['ID']; ?>"/>
									<button type="submit" title="Delete Command" style="border:none;" class="btn btn-danger btn-sm">
										<i class="fas fa-trash" ></i>
									</button>
							</form>
						</td>
					</tr>
				<?php }?>
				<?php if($count==0){ ?>
					<tr>
						<td colspan=30><center><h5>No Commands Found.</h5></center></td>
					</tr>
				<?php } ?>
			   </tbody>
			</table>
		</div>
	</div>
  </div>
</div>
<!-------modal----->
<div id="agentAlertModal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">One-way Message to Agent: <?php echo $_SESSION['ComputerHostname']; ?></h5>
			</div>
			<form method="post" action="index.php">
				<div class="modal-body">
					<input type="hidden" name="type" value="assetOneWayMessage"/>
					<input type="hidden" name="ID" value="<?php echo $computerID; ?>">
					<textarea placeholder="Your message here..." name="assetMessage" class="form-control"></textarea>
				</div>
				<div class="modal-footer">
					<button type="submit"  class="btn btn-primary btn-sm">
						Send <i class="fas fa-paper-plane" ></i>
					</button>
					<button type="button" class="btn btn-sm btn-default"  data-dismiss="modal">Close</button>
				</div>
			</form>
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