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

$query = "SELECT ID, online FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$data = mysqli_fetch_assoc($results);

$online = $data['online'];

$getWMI = array("agent_settings","general");
$json = getComputerData($computerID, $getWMI);
$agent_settings = $json['agent_settings']['Response']["Interval"];
$hostname = textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
//print_r($agent_settings);
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
			<h5 style="color:#0c5460">Editing Agent: <span style="color:#333"><?php echo $hostname; ?></span>
				<br>
				<p>
					Here You Fine Tune The Configuation Options For This Particular Agent.
				</p>
			</h5>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<button title="Refresh" onclick="loadSection('Agent_Settings');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-sync"></i>
			</button>
		</div>
	</div>
</div>
<div style="width:100%;backgrdound:#fff;padding:15px;">
	<form method="POST" action="/">
		<div class="row" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
			<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8" style="padding:5px;padding-bottom:20px;padding-top:0px;border-radius:6px;">			 
				   <div class="card table-card" id="printTable" style="margin-top:-40px;padding:10px">  
				   		<div class="card-header"><br>
							<h4>Automatic Update Intervals</h4>
							<p style="color:red">Agent Interval changes will take effect on service restart. Changes you make may not be reflected below until Agent service restart.</p>
							<hr>
						</div>
						<div style="margin-left:50px;margin-top:-40px" class="row ">							
							<div style="padding:20px;border-radius:6px" class=" col-sm-12">
								<input type="hidden" name="type" value="agentConfig"/>
								<input type="hidden" name="ID" value="<?php echo $data['ID']; ?>"/>
								<div class="row">
									<?php
									$count=0;
									foreach ($agent_settings as $setting => $val) {
										$setting_new = str_replace("_"," ", $setting);
										$count++;	
									?>
										<div class=" col-sm-4">
											<label class="form-label" for="customRange2"><?php echo ucwords($setting_new); ?></label>
											<div class="range">
												<input class="range-slider__range" type="range" name="agent_<?php echo $setting; ?>" value="<?php echo $val; ?>" min="0" max="360">
												<span style="background:#6c757d;color:#fff" class="range-slider__value">0</span>
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