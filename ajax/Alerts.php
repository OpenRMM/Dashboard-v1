<?php
	//$sort = (trim($_GET['sort'])!="" ? $_GET['sort'] : "ID");
	$filters = clean($_GET['filters']);
	$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];
	$json = getComputerData($computerID, array("*"), $showDate);
	$online = $json['Online'];
	$lastPing = $json['Ping'];

?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-12">
		<h5>Monitor</h5><hr>
		<p>Configure Notifications For This Asset.</p>	
		<form action="index.php" method="post">
		<div class="row">			
			<div class="col-sm-6" style="padding:5px;">
				<?php
				$count = 0;
				foreach($siteSettings['Alert Settings'] as $type=>$alert){
					$count++;
					if($count % 2 == 0){continue;}else{}
				?>
					<div class="card" style="color:#fff;background:#353c4e;margin-bottom:5px;padding:5px;">
					   <div class="form-gsroup">
						<h6 style="color:#fff"><b><?php echo $type; ?>:</b></h6>
						<div style="font-size:12px" class="col-sm-offset-2 col-sm-10">
							<?php foreach($alert as $option=>$options){ ?>
								<?php if(count($options) > 1){ //Contains Sub Options?>
									<b><?php echo $option;?></b>
									<?php foreach($options as $subOptionKey=>$subOptionValue){ ?>
										<div class="checkbox" style="margin-left:15px;font-size:12px">
											<label>
												<input type="checkbox" name="alert_settings_<?php echo $type."_".$option."_".$subOptionKey;?>" value="1"> <?php echo $subOptionKey; ?>
											</label>
										</div>
									<?php }?>
								<?php }else{?>
									<div class="checkbox"style="font-size:12px">
										<label>
											<input type="checkbox" name="alert_settings_<?php echo $type."_".$option;?>" value="1"> <?php echo $option; ?>
										</label>
									</div>
								<?php }?>
							<?php }?>
						</div>
					  </div>
					</div>
				<?php } ?>
			</div>
			<div class="col-sm-6" style="padding:5px;">
				<?php
				$count = 0;
				foreach($siteSettings['Alert Settings'] as $type=>$alert){
					$count++;
					if($count % 2 == 0){  }else{continue;}
				?>
					<div class="card" style="color:#fff;background:#353c4e;margin-bottom:5px;padding:5px;">
					   <div class="form-gsroup">
						<h6 style="color:#fff"><b><?php echo $type; ?>:</b></h6>
						<div class="col-sm-offset-2 col-sm-10">
							<?php foreach($alert as $option=>$options){ ?>
								<?php if(count($options) > 1){ //Contains Sub Options?>
									<b><?php echo $option;?></b>
									<?php foreach($options as $subOptionKey=>$subOptionValue){ ?>
										<div class="checkbox" style="margin-left:15px;font-size:12px">
											<label>
												<input type="checkbox" name="alert_settings_<?php echo $type."_".$option."_".$subOptionKey;?>" value="1"> <?php echo $subOptionKey; ?>
											</label>
										</div>
									<?php }?>
								<?php }else{?>
									<div class="checkbox" style="font-size:12px">
										<label>
											<input type="checkbox" name="alert_settings_<?php echo $type."_".$option;?>" value="1"> <?php echo $option; ?>
										</label>
									</div>
								<?php }?>
							<?php }?>
						</div>
					  </div>
					</div>
				<?php } ?>
			</div>
		</div>
		<button type="submit" style="float:right" class="btn btn-warning btn-sm">Save</button>
  </form>
  </div>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
  