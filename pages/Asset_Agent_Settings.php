<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$query = "SELECT ID, online FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$data = mysqli_fetch_assoc($results);

$online = $data['online'];

$getWMI = array("agent_settings","general");
$json = getComputerData($computerID, $getWMI);
$agent_settings = $json['agent_settings']['Response'];
$hostname = textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
?>
<?php if($data['ID']==""){ ?>
	<br>
	<center>
		<h4>No Asset Selected</h4>
		<p>
			To Select An Asset, Please Visit The <a class='text-dark' style="cursor:pointer" onclick='loadSection("Assets");'><u>Assets page</u></a>
		</p>
	</center>
	<hr>
<?php exit; }?>
<div class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 title="ID: <?php echo $computerID; ?>" style="color:#0c5460">Editing Agent: <span style="color:#333"><?php echo $hostname; ?></span>
				<br>
				<p>
					Here You Fine Tune The Configuation Options For This Particular Agent.
				</p>
			</h5>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<button title="Refresh" onclick="loadSection('Asset_Agent_Settings');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-sync"></i>
			</button>
		</div>
	</div>
</div>
<div style="width:100%;backgrdound:#fff;padding:15px;">
	<form method="POST" action="/">
		<div class="row" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
			<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8" style="padding:5px;padding-bottom:20px;padding-top:0px;border-radius:6px;">	
				<div class="card table-card" style="margin-top:0px;padding:10px">  
					<div class="card-header"><br>
						<h4>Update Settings</h4>
						<p></p>
						<hr>
					</div>
					<div style="margin-left:50px;margin-top:-40px" class="row ">							
						<div style="padding:20px;border-radius:6px" class=" col-sm-12">
							<div class="row">
								<div class="col-sm-2">
									<?php 
									if($agent_settings["Updates"]['auto_update']=="1"){
										$auto = "checked";
									}else{
										$auto="";
									}
									?>
									<label>Automatic Updates</label>
									<center>
										<div style="margin-top:10px;" class="custom-control custom-switch">
											<input <?php echo $auto; ?> type="checkbox" class="custom-control-input" name="autoUpdate" value="1" id="customSwitches">
											<label class="custom-control-label" for="customSwitches"></label>
										</div>
									</center>
								</div>
								<div class=" col-sm-5">
									<label class="form-label" for="customRange2">Update URL</label>
									<input placeholder="https://" name="updateURL" class="form-control" type="url" value="<?php echo $agent_settings["Updates"]['update_url']; ?>">
								</div>
								<div class=" col-sm-5">
									<label class="form-label" for="customRange2">Update Check Interval</label>
									<div style="margin-top:10px;" class="range">
										<input style="width:200px" class="range-slider__range" type="range" name="updateInterval" value="<?php echo (int)$agent_settings["Updates"]['check_interval']; ?>" min="0" max="1000">
										<span style="background:#6c757d;color:#fff;width:120px" class="range-slider__value">0</span>
									</div>
								</div>									
							</div>
						</div>	
					</div>	
				</div>		 
				<div class="card table-card" id="printTable" style="margin-top:0px;padding:10px">  
					<div class="card-header"><br>
						<h4>Automatic Update Intervals</h4>
						<p>How often would you like the agent to send data?</p>
						<hr>
					</div>
					<div style="margin-left:50px;margin-top:-40px" class="row ">							
						<div style="padding:20px;border-radius:6px" class=" col-sm-12">
							<input type="hidden" name="type" value="agentConfig"/>
							<input type="hidden" name="ID" value="<?php echo $data['ID']; ?>"/>
							<div class="row">
								<?php
								$count=0;
								foreach ($agent_settings["Interval"] as $setting => $val) {
									$setting_new = str_replace("_"," ", $setting);
									$count++;	
								?>
									<div class=" col-sm-4">
										<label class="form-label" for="customRange2"><?php echo ucwords($setting_new); ?></label>
										<div class="range">
											<input class="range-slider__range" type="range" name="agent_<?php echo $setting; ?>" value="<?php echo $val; ?>" min="0" max="360">
											<span style="background:#6c757d;color:#fff;width:120px" class="range-slider__value">0</span>
										</div>
									</div>
								<?php } 
								if($count==0){
								?>
									<div style="margin-top:10px">
										<center>
											<h6>An error has occurred while trying to load the settings for the selected agent.</h6>
										</center>
									</div>
								<?php }	?>
							</div>
						</div>	
					</div>	
				</div>	
			</div>						
			<div class="col-sm-4">
			<?php if($online=="1"){ ?>	
				<div class="panel panel-default" style="height:auto;color:#fff;color#000;padding:20px;border-radius:6px;margin-bottom:20px;">
					<div class="panel-heading">
						<h4 class="panel-title">
							Agent Status
						</h4>
					</div>
					<div  class="panel-body">
						<div class="form-check" style="border-radius:6px;margin-bottom:10px;padding:10px;padding-left:50px;color:#333;">
							<button type="button" style="width:35%;margin-top:-3px;border:none;background:#f8d7da;color:#721c24;" onclick="agentStatus('<?php echo $computerID; ?>','stop');" class="btn btn-sm">
								<i class="fas fa-stop"></i> Stop Agent
							</button>
							<button type="button" style="width:55%;margin-top:-3px;border:none;background:#fff3cd;color:#856404;"  onclick="agentStatus('<?php echo $computerID; ?>','restart');" class="btn btn-sm">
								<i class="fas fa-redo"></i> Restart Agent
							</button>
						</div>
					</div>
				</div>	
				<?php } ?>
				<div class="panel panel-default" style="height:auto;color:#fff;color#000;padding:20px;border-radius:6px;margin-bottom:20px;">
					<div class="panel-heading">
						<h4 class="panel-title">
							Agent Configuration
						</h4>
					</div>
					<div  class="panel-body">
						<div class="form-check" style="border-radius:6px;margin-bottom:10px;padding:10px;padding-left:50px;color:#333;">
							<button style="width:100%;margin-top:-3px;border:none;background:#0c5460;color:<?php echo $siteSettings['theme']['Color 2'];?>;" class="btn btn-sm" type="submit">
								<i class="fas fa-save"></i> Save Configuration
							</button>
						</div>
					</div>
				</div>				
			</div>
		</div>
	</form>
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
			if(this.value==0){
				$(this).html("Disabled");
			}else{
				$(this).html(value+" minutes");
			}
			});
			range.on('input', function(){
				if(this.value==0){
					$(this).next(value).html("Disabled");
				}else{
					$(this).next(value).html(this.value+" minutes");
				}
			});
		});
	};
	rangeSlider();
</script>