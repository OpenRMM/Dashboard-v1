<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$query = "SELECT online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$computer = mysqli_fetch_assoc($results);

$json = getComputerData($computerID, array("general"));
$online = $computer['online'];

$hostname = $json['general']['Response'][0]['csname'];	
?>
<div class="row" style="">
	<div style="" class=" col-md-12">
		<div class="card" style="padding:20px;margin-bottom:-1px">					
			<h5 style="color:#0c5460">Commands
				<div style="float:right;">
					<button href="javascript:void(0)" title="Refresh" onclick="loadSection('Asset_Commands');" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
						<i class="fas fa-sync"></i>
					</button>
				</div>
				<br>
				<p>View & Execute Commands On This Asset.</p>
			</h5>	
		</div>
	</div>	
</div>
<?php if($online=="0"){ ?>
	<div  style="border-radius: 0px 0px 4px 4px;" class="alert alert-danger" role="alert">
		&nbsp;&nbsp;&nbsp;This Agent is offline		
	</div>
	<?php 
}else{
	echo"<br>";
}
?>
<div class="row" style="">
	<div style="" class=" col-md-4">
		<div class="card">
			<div class="card-body">
					<div class="row" style="margin-top:10px;margin-left:30px;padding-bottom:10px">		
						
						<div class="col-md-5 secbtn bg-dark" onclick='$("#terminaltxt").focus();' data-dismiss="modal" style="margin-top:10px;cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;" data-toggle="modal" data-target="#terminalModal">	
							<center>
								<i class="fas fa-terminal" style="margin-top:15px;"></i>
								<br>Terminal
							</center>
						</div>
						<div data-toggle="modal" data-target="#agentMessageModal" class="col-md-5 bg-success text-white secbtn" style="margin-top:10px;cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;">
							<center>
								<i class="fas fa-comment" style="margin-top:10px;"></i>
								<br>One-way <br>Message	
							</center>
						</div>
						<div  data-dismiss="modal" class="bg-primary col-md-5 text-white secbtn" style="margin-top:10px;cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;" onclick='sendCommand("reg add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 0 /f", "Enable Remote Desktop");'>
							<center>
								<i class="fas fa-desktop" style="margin-top:15px"></i><br> Enable RDP
							<center>
						</div>
						<div  data-dismiss="modal" class="bg-warning col-md-5 text-white secbtn" style="margin-top:10px;cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;" onclick='sendCommand("reg add \"HKEY_LOCAL_MACHINE\\\\SYSTEM\\\\CurrentControlSet\\\\Control\\\\Terminal Server\" /v fDenyTSConnections /t REG_DWORD /d 1 /f", "Disable Remote Desktop");'>
							<center>
							<i class="fas fa-desktop" style="margin-top:15px"></i><br> Disable RDP
							<center>
						</div>
						<div  data-dismiss="modal" class="bg-primary col-md-5 text-white secbtn" style="margin-top:10px;cursor:pointer;display:inline;width:45%;border:none;border-radius:5px;margin-right:10px;height:80px;" onclick="sendCommand('Netsh Advfirewall set allprofiles state on', 'Enable Firewall');">
							<center>
							<i class="fas fa-fire-alt" style="margin-top:15px"></i><br> Enable Firewall
							<center>
						</div>
						<div  data-dismiss="modal" class="bg-warning col-md-5 text-white secbtn" style="cursor:pointer;display:inline;margin-top:10px;width:45%;border:none;border-radius:5px;margin-left:0px;margin-right:10px;height:80px;" onclick="sendCommand('Netsh Advfirewall set allprofiles state off', 'Disable Firewall');">
							<center>	
							<i class="fas fa-fire-alt" style="margin-top:15px"></i><br> Disable Firewall
							<center>
						</div>
						<div  data-dismiss="modal" class="bg-secondary col-md-5 text-white secbtn" style="cursor:pointer;display:inline;margin-top:10px;width:45%;border:none;border-radius:5px;margin-left:0px;margin-right:10px;height:80px;" onclick="sendCommand('ipconfig /flushdns', 'Clear DNS Cache');">
							<center>	
								<i class="fas fa-network-wired" style="margin-top:15px"></i><br> Flush DNS
							<center>
						</div>					
					</div>
			</div>
		</div>
	</div>	
	<div class="col-md-8 " >
		<div class="card" style="overflow-x:auto">
			<div class="card-body">
				<?php 
					$query = "SELECT * FROM commands WHERE computer_id='".$computer['ID']."' ORDER BY ID DESC LIMIT 1000";
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
							$cmd = crypto('decrypt', $command['command'], $command['hex']);
							$data = computerDecrypt($command['data_received']);
							if($command['status']=="Deleted"){continue;}
						?>
						<tr>
						<td title="<?php echo substr($cmd, 0, 400); if(strlen($cmd)>400){echo '...'; } ?>"><b><?php echo substr($cmd, 0, 40); if(strlen($cmd)>40){echo '...'; } ?></b></td>
						<!--<td><?php echo strtolower($command['expire_after']);?> Minutes</td>-->
						<td><?php echo $command['time_sent'];?></td>
						<td title="<?php echo substr($data, 0, 400); if(strlen($data)>400){echo '...'; } ?>"><b><?php echo substr($data, 0, 40); if(strlen($data)>40){echo '...'; } ?></b></td>

							<?php if($command['time_received']!=""){
										$timer = $command['time_received'];
								}else{
									$timer = "Not Received";
								} ?>
						<td title="<?php echo $timer; ?>" ><b><?php echo $command['status'];?></b></td>
						<td>
							<?php if(($_SESSION['accountType']=="Standard" and $command['status']=="Sent") or $_SESSION['accountType']=="Admin" ){ ?>
								<form action="/" method="POST">
									<input type="hidden" name="type" value="DeleteCommand"/>
									<input type="hidden" name="ID" value="<?php echo $command['ID']; ?>"/>
										<button type="submit" title="Delete Command" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
											<i class="fas fa-trash" ></i>
										</button>
								</form>
								<?php } ?>
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
<script>
	function sendMessage(){  
		var alertType = $("input[name='alertType']:checked").val();
		var alertTitle = $("#inputTitle").val();
		var alertMessage = $("#inputMessage").val();
		$.post("/", {
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