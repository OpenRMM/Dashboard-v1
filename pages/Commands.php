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
$query = "SELECT online, hostname, ID FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
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
				<a href="javascript:void(0)" title="Refresh" onclick="loadSection('Commands');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
					<i class="fas fa-sync"></i>
				</a>
			</div>
		</h5>
		<p>View & Execute Commands On This Asset.</p>
		<hr>
		<div class="row">
			<div style="" class="col-md-5">
			<?php if($online=="1"){ ?>
				<div class="row" style="margin-top:50px;margin-left:50px">		
					
					<div class="col-md-3 secbtn" onclick='$("#terminaltxt").focus();' data-dismiss="modal" style="cursor:pointer;color:#fff;background:#333;height:80px;border-radius:5px;margin-right:10px" data-toggle="modal" data-target="#terminalModal">	
						<center>
							<i class="fas fa-terminal" style="margin-top:15px;"></i>
							<br>Terminal
						</center>
					</div>
					<div data-toggle="modal" data-target="#agentAlertModal" class="col-md-3 bg-success text-white secbtn" style="cursor:pointer;height:80px;border-radius:5px;margin-right:10px">
						<center>
							<i class="fas fa-comment" style="margin-top:10px;"></i>
							<br>One-way <br>Message	
						</center>
					</div>
					<div  data-dismiss="modal" class="bg-primary col-md-3 text-white secbtn" style="cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;" onclick='sendCommand("reg add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 0 /f", "Enable Remote Desktop");'>
						<center>
							<i class="fas fa-desktop" style="margin-top:15px"></i><br> Enable RDP
						<center>
					</div>
					<div  data-dismiss="modal" class="bg-warning col-md-3 text-white secbtn" style="margin-top:10px;cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;" onclick='sendCommand("reg add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 1 /f", "Disable Remote Desktop");'>
						<center>
						<i class="fas fa-desktop" style="margin-top:15px"></i><br> Disable RDP
						<center>
					</div>
					<div  data-dismiss="modal" class="bg-primary col-md-3 text-white secbtn" style="margin-top:10px;cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;" onclick="sendCommand('Netsh Advfirewall set allprofiles state on', 'Enable Firewall');">
						<center>
						<i class="fas fa-fire-alt" style="margin-top:15px"></i><br> Enable Firewall
						<center>
					</div>
					<div  data-dismiss="modal" class="bg-warning col-md-3 text-white secbtn" style="cursor:pointer;display:inline;margin-top:10px;width:45%;border:none;border-radius:5px;margin-left:0px;margin-right:10px;height:80px;" onclick="sendCommand('Netsh Advfirewall set allprofiles state off', 'Disable Firewall');">
						<center>	
						<i class="fas fa-fire-alt" style="margin-top:15px"></i><br> Disable Firewall
						<center>
					</div>
					<div  data-dismiss="modal" class="bg-secondary col-md-3 text-white secbtn" style="cursor:pointer;display:inline;margin-top:10px;width:45%;border:none;border-radius:5px;margin-left:0px;margin-right:10px;height:80px;" onclick="sendCommand('ipconfig /flushdns', 'Clear DNS Cache');">
						<center>	
							<i class="fas fa-network-wired" style="margin-top:15px"></i><br> Flush DNS
						<center>
					</div>
					<div data-dismiss="modal" class="bg-secondary col-md-3 text-white secbtn" style="cursor:pointer;display:inline;margin-top:10px;width:45%;border:none;border-radius:5px;margin-left:0px;margin-right:10px;height:80px;" onclick="sendCommand('sfc /scannow', 'Repair File System');">
						<center>
							<i class="fas fa-file" style="margin-top:15px"></i><br> Repair System
						<center>
					</div>
					<div data-dismiss="modal" class="bg-secondary col-md-3 text-white secbtn" style="cursor:pointer;display:inline;margin-top:10px;width:45%;border:none;border-radius:5px;margin-left:0px;margin-right:10px;height:80px;">
						<center>
							<i class="fas fa-keyboard" style="margin-top:15px"></i><br> Send Keyboard
						<center>
					</div>
				</div>
			</div>
		
		<br>
	
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
	<div style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;" class="row">
		<div class="col-md-12">
			<?php 
				$query = "SELECT * FROM commands WHERE ComputerID='".$computer['ID']."' ORDER BY ID DESC LIMIT 1000";
				$results = mysqli_query($db, $query);
				$commandCount = mysqli_num_rows($results);
			?>
			<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover table-borderless">				
			  <thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
				  <th scope="col">Command</th>
				  <!--<th scope="col">Expire Time</th>-->
				  <th scope="col">Time Sent</th>
				  <th scope="col">Data Received</th>
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
					  <td title="<?php echo substr($command['command'], 0, 400); if(strlen($command['command'])>400){echo '...'; } ?>"><b><?php echo substr($command['command'], 0, 40); if(strlen($command['command'])>40){echo '...'; } ?></b></td>
					  <!--<td><?php echo strtolower($command['expire_after']);?> Minutes</td>-->
					  <td><?php echo $command['time_sent'];?></td>
					  <td title="<?php echo substr($command['data_received'], 0, 400); if(strlen($command['data_received'])>400){echo '...'; } ?>"><b><?php echo substr($command['data_received'], 0, 40); if(strlen($command['data_received'])>40){echo '...'; } ?></b></td>

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
						<td colspan=30><center><h6>No Commands Found.</h6></center></td>
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
					<div class="form-group">
						<label><b>Title</b></label>
						<input type="text" id="inputTitle" placeholder="Your title here..." class="form-control" name="alertTitle"/>
					</div>
					<div class="form-group">
						<label><b>Message</b></label>
						<textarea id="inputMessage" placeholder="Your message here..." name="alertMessage" class="form-control"></textarea>
					</div>
					<label><b>Alert Type</b></label>
					<center>
						<div class="form-group">							
							<label class="radio-inline">
								<input type="radio" id="#inputType" class="form-control" name="alertType" value="alert" checked>Alert
							</label>
							<label class="radio-inline">
								<input type="radio" id="#inputType" class="form-control" name="alertType" value="confirm" >Confirm
							</label>
							<label class="radio-inline">
								<input type="radio" id="#inputType" class="form-control" name="alertType" value="password" >Password
							</label>
							<label class="radio-inline">
								<input type="radio" id="#inputType" class="form-control" name="alertType" value="prompt" >Prompt
							</label>
						</div>
					<center>
				</div>
				<div class="modal-footer">
					<button type="button" onclick='sendMessage($("#inputMessage").val());' data-dismiss="modal" class="btn btn-primary btn-sm">
						Send <i class="fas fa-paper-plane" ></i>
					</button>
					<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	function sendMessage(){  
		var alertType = $("input[name='alertType']:checked").val();
		var alertTitle = $("#inputTitle").val();
		var alertMessage = $("#inputMessage").val();
		$.post("index.php", {
		type: "assetOneWayMessage",
		ID: computerID,
		alertType: alertType,
		alertTitle: alertTitle,
		alertMessage: alertMessage,
		},
		function(data, status){
			toastr.options.progressBar = true;
			toastr.success("Your Message Has Been Sent");
		});  
	}
	$(document).ready(function() {
		$('#dataTable').dataTable( {
			colReorder: true
		} );
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