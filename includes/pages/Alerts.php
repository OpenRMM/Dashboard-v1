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
	
	$json = getComputerData($computerID, array("*"), $showDate);
	$lastPing = $json['Ping'];

	
	$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];

?>
<div class="rosw" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div v style="padding:20px" class="col-md-12">
		<h5>Alerts & Monitoring
			<div style="float:right;">
				<a href="#" title="Refresh" onclick="loadSection('Alerts');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
					<i class="fas fa-sync"></i>
				</a>
			</div>
		</h5><br><hr>
		<p>Configure Notifications For This Asset.</p>	
		<form action="index.php" method="post">
		<div>			
			<div class="row " style="margin-left:20px;padding-bottom:30px">
				<?php
				$count = 0;
				foreach($siteSettings['Alert Settings'] as $type=>$alert){
					$count++;	
				?>
					<div class="card col-sm-2" style="color:#fff;background:#353c4e;margin-bottom:5px;padding:10px;margin-right:15px;">
					   <div>
						<h6 style="color:#fff"><b><?php echo $type; ?>:</b></h6>
						<div style="font-size:12px" >
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
		</div>
		<button type="submit" style="float:right;margin-top:-30px;width:100px" class="btn btn-warning btn-sm">Save</button>
  </form>
  </div>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This Computer Appears To Be Offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>
  