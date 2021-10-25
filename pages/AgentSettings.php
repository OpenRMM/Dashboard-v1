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
$computerID = (int)clean($_GET['ID']);

$query = "SELECT ID, hostname, online, agent_settings FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$data = mysqli_fetch_assoc($results);

$online = $data['online'];
$agent_settings = json_decode($data['agent_settings']['Response'], true);

?>
<?php if($data['hostname']==""){ ?>
	<br>
	<center>
		<h4>No Asset Selected</h4>
		<p>
			To Select A Asset, Please Visit The
			<a class='text-dark' style="cursor:pointer" onclick="loadSection('Assets');" ><u>Agent Configuration</u></a>
		</p>
	</center>
	<hr>
<?php exit; }?>
<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">Editing Agent: <?php echo $data['hostname']; ?>
	<a href="javascript:void(0)" title="Refresh" onclick="loadSection('AgentSettings');" class="btn btn-sm" style="float:right;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
		<i class="fas fa-sync"></i>
	</a>
</h4>
<hr>
<div style="width:100%;backgrdound:#fff;padding:15px;">
	<p class="lead">
	   <small class="text-muted"> Here You Fine Tune The Configuation Option For This Particular Agent.</small>
	</p>
	<hr />
	<form method="POST" action="index.php">
	<div class="row" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8" style="padding:5px;padding-bottom:20px;padding-top:0px;border-radius:6px;">			 
				   <div class="card table-card" id="printTable" style="margin-top:-40px;padding:10px">  
				   		<div class="card-header"><br>
							<h4>Automatic Update Intervals</h4>
							<p style="color:red">Agent Interval changes will take effect on service restart. Changes you make may not be reflected below until Agent service restart.</p>
							<hr>
						</div>
						<div style="margin-left:50px;margin-top:-40px" class="row ">
							
							<div style="padding:20px;border-radius:6px" class=" col-sm-4">
								<input type="hidden" name="type" value="agentConfig"/>
								<input type="hidden" name="ID" value="<?php echo $data['ID']; ?>"/>
								<label class="form-label" for="customRange2">Heartbeat</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Heartbeat" value="<?php echo $agent_settings['interval']['Heartbeat']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Asset Overview</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_General" value="<?php echo $agent_settings['interval']['getGeneral']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">BIOS</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_BIOS" value="<?php echo $agent_settings['interval']['getBIOS']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Optional Features</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Features" value="<?php echo $agent_settings['interval']['getOptionalFeatures']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Processes</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Processes" value="<?php echo $agent_settings['interval']['getProcesses']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Services</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Services" value="<?php echo $agent_settings['interval']['getServices']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Users</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Users" value="<?php echo $agent_settings['interval']['getUsers']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Video Configuration</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Video" value="<?php echo $agent_settings['interval']['getVideoConfiguration']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Logical Disk</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Disk" value="<?php echo $agent_settings['interval']['getLogicalDisk']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Sound Devices</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Sound" value="<?php echo $agent_settings['interval']['getSoundDevices']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Windows Updates</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_WindowsUpdates" value="<?php echo $agent_settings['interval']['getWindowsUpdates']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
							</div>
							<div style="padding:20px;border-radius:6px" class="col-sm-4">
								<label class="form-label" for="customRange2">Pointing Device</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Pointing" value="<?php echo $agent_settings['interval']['getPointingDevice']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Keyboard</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Keyboard" value="<?php echo $agent_settings['interval']['getKeyboard']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Base Board</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Board" value="<?php echo $agent_settings['interval']['getBaseBoard']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Desktop Monitor</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Monitor" value="<?php echo $agent_settings['interval']['getDesktopMonitor']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Printers</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Printers" value="<?php echo $agent_settings['interval']['getPrinters']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Network Login Profile</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_NetworkLogin" value="<?php echo $agent_settings['interval']['getNetworkLoginProfile']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Network</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Network" value="<?php echo $agent_settings['interval']['getNetwork']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">PnP Entitys</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_PnP" value="<?php echo $agent_settings['interval']['getPnPEntitys']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">SCSI Controller</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_SCSI" value="<?php echo $agent_settings['interval']['getSCSIController']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Event Logs</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Logs" value="<?php echo $agent_settings['interval']['getEventLogs']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Agent Logs</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_logs" value="<?php echo $agent_settings['interval']['getAgentLogs']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
							</div>
							<div style="padding:20px;border-radius:6px" class="col-sm-4">

								<label class="form-label" for="customRange2">Products</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Products" value="<?php echo $agent_settings['interval']['getProducts']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Processor</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Processor" value="<?php echo $agent_settings['interval']['getProcessor']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Firewall</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Firewall" value="<?php echo $agent_settings['interval']['getFirewall']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Agent</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Agent" value="<?php echo $agent_settings['interval']['getAgent']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Battery</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Battery" value="<?php echo $agent_settings['interval']['getBattery']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Filesystem</label>
								<div class="range">
								<input class="range-slider__range" type="range" name="agent_Filesystem" value="<?php echo $agent_settings['interval']['getFilesystem']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Mapped Logical Disk</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Mapped" value="<?php echo $agent_settings['interval']['getMappedLogicalDisk']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Physical Memory</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Memory" value="<?php echo $agent_settings['interval']['getPhysicalMemory']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Startup</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_Startup" value="<?php echo $agent_settings['interval']['getStartup']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
								<label class="form-label" for="customRange2">Shared Drives</label>
								<div class="range">
									<input class="range-slider__range" type="range" name="agent_SharedDrives" value="<?php echo $agent_settings['interval']['getSharedDrives']; ?>" min="0" max="360">
									<span class="range-slider__value">0</span>
								</div>
							
							</div>
						</div>	
					</div>	
				</div>				
			<div class="col-sm-4">
				<div class="panel panel-default" style="height:auto;color:#fff;color#000;padding:20px;border-radius:6px;margin-bottom:20px;">
					<div class="panel-heading">
						<h4 class="panel-title">
							Agent Configuration
						</h4>
					</div>
					<div  class="panel-body">
						<div class="form-check" style="border-radius:6px;margin-bottom:10px;padding:10px;padding-left:50px;color:#333;">
							<button style="width:100%;margin-top:-3px;border:none;" class="btn btn-success btn-md" type="submit">
								<i class="fas fa-save"></i> Save Configuration
							</button>
						</div>
					</div>
				</div>					
			</div>
		</div>
	</form>

<script>
    tinymce.init({
      selector: 'textarea',
      plugins: 'a11ychecker advcode casechange formatpainter linkchecker autolink lists checklist media mediaembed pageembed permanentpen powerpaste table advtable tinycomments',
      toolbar: 'a11ycheck addcomment showcomments casechange checklist code formatpainter pageembed permanentpen table',
      toolbar_mode: 'floating',
      tinycomments_mode: 'embedded',
      tinycomments_author: 'SMG_RMM',
    });
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove()
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
var rangeSlider = function(){
var slider = $('.range'),
	range = $('.range-slider__range'),
	value = $('.range-slider__value');
	
slider.each(function(){

	value.each(function(){
	var value = $(this).prev().attr('value');
	$(this).html(value+" minutes");
	});

	range.on('input', function(){
	$(this).next(value).html(this.value+" minutes");
	});
});
};

rangeSlider();
</script>