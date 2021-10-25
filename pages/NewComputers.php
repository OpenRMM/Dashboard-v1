<?php 
if($_SESSION['userid']==""){ 
?>
	<script>		
		toastr.error('Session timed out.');
		setTimeout(function(){
			setCookie("section", "Login", 365);	
			window.location.replace("..//");
		}, 3000);		
	</script>
<?php 
	exit("<center><h5>Session timed out. You will be redirected to the login page in just a moment.</h5><br><h6>Redirecting</h6></center>");
}	
?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">New Computers
		<a href="javascript:void(0)" title="Refresh" onclick="loadSection('NewComputers');" class="btn btn-sm" style="float:right;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</a>
	</h4>
</div>
<div style="overflow:auto;width:100%">
	<form method="post" action="/">
	  <div class="card table-card" id="printTable" >
		   <div class="card-header">
				<h5>Listing All Current Customers</h5>
				<div class="card-header-right">
					<ul class="list-unstyled card-option">
						<li><i class="feather icon-maximize full-card"></i></li>
						<li><i class="feather icon-minus minimize-card"></i></li>
						<li><i class="feather icon-trash-2 close-card"></i></li>
					</ul>
				</div>
			</div>
		<div style="padding:10px;">	
		<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:14px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
			<col width="30">
			<col width="30">
			<col width="280">
			<col width="120">
			<col width="100">
			<col width="180">
			<col width="80">
			<col width="180">
			<col width="100">
			<col width="120">
			<col width="120">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
					<th scope="col"><input onClick="toggle(this)" id="allcomputers"  value="<?php echo $result['ID']; ?>" style="display:inline;appearance:none" type="checkbox"></th>
					<th scope="col">ID</th>
					<th scope="col">Hostname</th>
					<th scope="col"></th>
					<th scope="col">Logged In</th>
					<th scope="col"><?php echo $msp; ?></th>
					<th scope="col">Disk Space</th>
					<th scope="col">Date Added</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$query = "SELECT * FROM computerdata
								INNER JOIN companies ON companies.CompanyID = computerdata.CompanyID
								WHERE computerdata.active='1'
								ORDER BY computerdata.ID DESC Limit 20";
					//Fetch Results
					$count = 0;
					$results = mysqli_query($db, $query);
					while($result = mysqli_fetch_assoc($results)){
						$getWMI = array("WMI_LogicalDisk", "WMI_OS", "WMI_ComputerSystem", "Ping");
						$data = getComputerData($result['hostname'], $getWMI);
						$count++;
						$freeSpace = $data['WMI_LogicalDisk'][0]['FreeSpace'];
						$size = $data['WMI_LogicalDisk'][0]['Size'];
						$used = $size - $freeSpace ;
						$usedPct = round(($used/$size) * 100);
						//Determine Warning Level
						if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
							$pbColor = "red";
						}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
							$pbColor = "#ffa500";
						}else{ $pbColor = $siteSettings['theme']['Color 4']; }
				?>
				<tr>
					<td>
						<input id="computers" name="computers[]" value="<?php echo $result['ID']; ?>" style="display:inline;appearance:none" type="checkbox">
					</td>
					<td scope="row">
						<?php echo $result["ID"]; ?>
					</td>
					<td>
						<a style="color:<?php echo $siteSettings['theme']['Color 2']; ?>" href="javascript:void(0)" onclick="loadSection('General', '<?php echo $result['ID']; ?>');">
							<?php if(!$data['Online']) {?>
								<i class="fas fa-desktop" style="color:#666;font-size:16px;" title="Offline"></i>
							<?php }else{?>
								<i class="fas fa-desktop" style="color:green;font-size:16px;" title="Online"></i>
							<?php };?>
							&nbsp;<?php echo strtoupper($result['hostname']);?>
						</a>
					</td>
					<td>
						<?php $alertCount = count($data['Alerts']);?>
						<?php if($alertCount > 0){?>
							<span class="text-danger" data-toggle="modal" data-target="#computerAlerts" style="cursor:pointer;" onclick="computerAlertsModal('<?php echo strtoupper($result['hostname']);?>','<?php echo $data['Alerts_raw'];?>');">
								<i title="Priority" class="text-danger fa fa-exclamation-triangle" aria-hidden="true"></i>
								<?php echo $alertCount;?> <?php echo ($alertCount > 1 ? "Alerts" : "Alert");?>
							</span>
						<?php }else{?>
							<span class="text-success" data-toggle="modal" data-target="#computerAlerts" style="cursor:pointer;" onclick="computerAlertsModal('<?php echo strtoupper($result['hostname']);?>');">
								<i class="fas fa-thumbs-up"></i> No Issues
							</span>
						<?php };?>
					</td>
					<td>
						<?php
							$username = textOnNull($data['WMI_ComputerSystem'][0]['UserName'], "Unknown");
							echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username)); 
						?>
					</td>
					<td>
						<a style="color:#000;" href="javascript:void(0)" onclick="searchItem('<?php echo textOnNull($result['name'], "N/A");?>');">
							<u><?php echo textOnNull($result['name'], "Not Assigned");?></u>
						</a>
					</td>
					<td>
						<div class="progress" style="background:<?php echo $siteSettings['theme']['Color 3']; ?>;" title="<?php echo $usedPct;?>%">
							<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</td>
					<td>
						<?php 
							echo gmdate("m/d/y\ h:i",$result['date_added']); 
						?>
					</td>
				</tr>
			<?php }?>
			</tbody>
		</table>
	</div>
</div>
<button data-toggle="modal" type="button" data-target="#companyComputersModal" style="background:<?php echo $siteSettings['theme']['Color 2'];?>;color:#fff" class="btn btn-sm">
	Add Selected To..
</button>
<!------------- Add Company Computers ------------------->
<div id="companyComputersModal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-sm">
	<div class="modal-content">
		<div class="modal-header">
		<h5 class="modal-title" id="pageAlert_title">Add Computers</h5>
		</div>
		<div class="modal-body">
		<h6 id="pageAlert_title">Select The <?php echo $msp; ?> You Would Like To Add These Computers To.</h6>
		<?php
		$query = "SELECT CompanyID, name FROM companies ORDER BY CompanyID DESC LIMIT 100";
		$results = mysqli_query($db, $query);
		$commandCount = mysqli_num_rows($results);
		while($command = mysqli_fetch_assoc($results)){
		$count++;
		?>
			<div class="form-check">
			<input type="radio" name="companies" value="<?php echo $command['CompanyID']; ?>" class="form-check-input" id="CompanyCheck">
			<label class="form-check-label" for="CompanyCheck"><?php echo $command['name']; ?></label>
			</div>
		<?php } ?>
		</div>
		<div class="modal-footer">
		<input type="hidden" name="type" value="CompanyComputers">
		<button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
		<button type="submit" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;color:#fff;" d>Add To Company</button>
		</div>
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