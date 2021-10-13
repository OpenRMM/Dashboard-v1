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
$limit = intval($_GET['limit']);
if($limit == 0){
	$limit = 20;
}
$add = 20;
$count = 0;
//$sort = (trim($_GET['sort'])!="" ? $_GET['sort'] : "ID");
$search = ($_GET['search']);
$filters = ($_GET['filters']);
$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);
$username=$user['username'];
function welcome(){
	if(date("H") < 12){
		return "Good Morning";
	}elseif(date("H") > 11 && date("H") < 18){
		return "Good Afternoon";
	}elseif(date("H") > 17){
		return "Good Evening";
	}
}
$query = "SELECT ID,teamviewer FROM computerdata where active='1'";
$results = mysqli_query($db, $query);
$resultCount = mysqli_num_rows($results);

//Get stats
$query = "SELECT CompanyID FROM companies where active='1'";
$results = mysqli_query($db, $query);
$companyCount = mysqli_num_rows($results);
$query = "SELECT ID FROM users where active='1'";
$results = mysqli_query($db, $query);
$userCount = mysqli_num_rows($results);
$query = "SELECT ID,teamviewer FROM computerdata where active='1'";
$results = mysqli_query($db, $query);
$resultCount = mysqli_num_rows($results);
?>	
	<?php if($_SESSION['userid']!="" ){ ?>
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%;background:<?php echo $siteSettings['theme']['Color 2']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('Assets');">
						<div style="padding:10px 10px 0px 20px;">
							<i class="fas fa-desktop" style="font-size:28px;float:right;"></i>
							<span style="font-size:20px;" ><?php echo $resultCount; ?></span><br>
							<span style="font-size:20px;">Assets</span>
						</div>
													
					</a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%; background:<?php echo $siteSettings['theme']['Color 3']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('AllCompanies');">
						<div style="padding:10px 10px 0px 20px;">
							<i class="fas fa-building" style="font-size:28px;float:right;"></i>
							<span style="font-size:20px;"><?php echo $companyCount;?></span><br>
							<span style="font-size:20px;">Customers</span>
						</div>
					</a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%;background:<?php echo $siteSettings['theme']['Color 4']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;margin-right:30px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('AllUsers');">
						<div style="padding:10px 10px 0px 20px;">
							<i class="fas fa-user" style="font-size:28px;float:right;"></i>
							<span style="font-size:20px;"><?php echo $userCount;?></span><br>
							<span style="font-size:20px;">Technicians</span>
						</div>
					</a>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
				<div style="width:100%;background:<?php echo $siteSettings['theme']['Color 5']; ?>;height:100px;color:#fff;font-size:20px;text-align:left;border-radius:6px;">
					<a style="color:#fff;cursor:pointer;" onclick="loadSection('Tickets');">
						<div style="padding:10px 10px 0px;">
							<i class="fas fa-ticket-alt" style="font-size:28px;float:right;"></i>
							<span style="font-size:20px;"><?php echo $userCount;?></span><br>
							<span style="font-size:20px;">Tickets</span>
						</div>
					</a>
				</div>
			</div>
		</div>
	<?php } ?>
	<div class="row" id="sortable" style="margin-bottom:10px;margin-top:20px;border-radius:3px;overflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding:5px;padding-bottom:20px;padding-top:1px;border-radius:6px;">
				<div style="padding:15px" class="card">
					<canvas data-centerval="" id="line-chart" height="300"></canvas>
					<script>
						new Chart(document.getElementById("line-chart"), {
						  type: 'line',
						  data: {
							labels: [30,29,28,27,26,25,24,23,22,21,20,19,18,17,16,15,14,13,12,11,10,9,8,7,6,5,4,3,2,1],
							datasets: [{ 
								data: [86,114,106,106,107,111,133,221,783,2478,282,350,411,5002,635,809,947,1402,3700,5267,86,114,106,106,107,111,133,221,783,2478],
								label: "Assets",
								borderColor: "<?php echo $siteSettings['theme']['Color 2']; ?>",
								fill: false
							  }, { 
								data: [282,350,411,5002,635,809,947,1402,3700,5267,282,350,411,5002,635,809,947,1402,3700,5267,282,350,411,5002,635,809,947,1402,3700,5267],
								label: "Customers",
								borderColor: "<?php echo $siteSettings['theme']['Color 3']; ?>",
								fill: false
							  }, { 
								data: [2842,3500,4011,502,6385,809,947,1402,3700,5267,282,350,411,5002,235,809,947,1402,3700,5267,282,350,411,5002,6035,809,947,1402,3700,5267],
								label: "Technicians",
								borderColor: "<?php echo $siteSettings['theme']['Color 4']; ?>",
								fill: false
							  }
							  , { 
								data: [2842,3500,4011,502,6385,809,947,1402,3700,5267,282,350,411,5002,235,809,947,1402,3700,5267,282,350,411,5002,6035,809,947,1402,3700,5267],
								label: "Tickets",
								borderColor: "<?php echo $siteSettings['theme']['Color 5']; ?>",
								fill: false
							  }
							]
						  },
						  options: {	 
							title: {
							  display: true,
							  text: 'Overview Of The Last 30 Days'
							}
						  }
						});
					</script>						
				</div>
				<form method="post" action="index.php">				
				   <div class="card table-card" id="sortable printTable" style="margin-top:15px;padding:10px">
						   <div class="card-header">
								<h5>Recently Added Assets</h5>
								<div class="card-header-right">
									<ul class="list-unstyled card-option">
										<li><i class="feather icon-maximize full-card"></i></li>
										<li><i class="feather icon-minus minimize-card"></i></li>
										<li><i class="feather icon-trash-2 close-card"></i></li>
									</ul>
								</div>
							</div>
							<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:14px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
								<thead>
									<tr style="border-bottom:2px solid #d3d3d3;">
									  <th scope="col">#</th>
									  <th scope="col">Hostname</th>
									  <th scope="col"></th>
									  <th scope="col">Logged In</th>
									  <th scope="col">Company</th>
									  <th scope="col">Date Added</th>
									</tr>
							  	</thead>
							  	<tbody>
									<?php
										$query = "SELECT * FROM computerdata
												INNER JOIN companies ON companies.CompanyID = computerdata.CompanyID
												WHERE computerdata.active='1'
												ORDER BY computerdata.ID DESC Limit 10";
										//Fetch Results
										$count = 0;
										$results = mysqli_query($db, $query);
										while($result = mysqli_fetch_assoc($results)){
											$getWMI = array("WMI_LogicalDisk", "WMI_OS", "WMI_ComputerSystem", "Ping");
											$data = getComputerData($result['ID'], $getWMI);
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
										<span><?php echo $result["ID"]; ?></span>
									  </td>
									  <td>
										<a style="color:#000" href="#" onclick="loadSection('General', '<?php echo $result['ID']; ?>');">
											<?php if($result['online']=="0") {?>
												<i class="fas fa-desktop" style="color:#666;font-size:16px;" title="Offline"></i> <!-- needs os icon support -->
											<?php }else{?>
												<i class="fas fa-desktop" style="color:green;font-size:16px;" title="Online"></i> <!-- needs os icon support -->
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
									  <td onclick="$('input[type=search]').val('<?php $username = textOnNull($data['WMI_ComputerSystem'][0]['UserName'], "Unknown"); echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username)); ?>');$('input[type=search]').trigger('keyup'); $('html, body, table').animate({ scrollTop: 0 }, 'slow');">
										<?php $username = textOnNull($data['WMI_ComputerSystem'][0]['UserName'], "Unknown"); echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username)); ?>
									  </td>
									  <td>
										<a style="color:#000;" href="#" onclick="$('input[type=search]').val('<?php echo textOnNull($result['name'], "N/A");?>');$('input[type=search]').trigger('keyup'); $('html, body, table').animate({ scrollTop: 0 }, 'slow');">
											<?php echo textOnNull($result['name'], "Not Assigned");?>
										</a>
									  </td>
									  <td><?php echo gmdate("m/d/y\ h:i",$result['date_added']); ?></td>
									</tr>
								<?php }?>
							    </tbody>
							</table>
						</div>			
						<!------------- Add Company Computers ------------------->
						<div id="companyComputersModal2" class="modal fade" role="dialog">
						  <div class="modal-dialog modal-sm">
							<div class="modal-content">
							  <div class="modal-header">
								<h5 class="modal-title" id="pageAlert_title">Add Computers</h5>
							  </div>
							  <div class="modal-body">
								<h6 id="pageAlert_title">Select The Company You Would Like To Add These Computers Too</h6>
								<?php							
									$query = "SELECT CompanyID, name FROM companies ORDER BY CompanyID DESC LIMIT 100";
									$results = mysqli_query($db, $query);
									$commandCount = mysqli_num_rows($results);
									while($command = mysqli_fetch_assoc($results)){		
								?>
								  <div class="form-check">
									<input type="radio" required name="companies" value="<?php echo $command['CompanyID']; ?>" class="form-check-input" id="CompanyCheck">
									<label class="form-check-label" for="CompanyCheck"><?php echo $command['name']; ?></label>
								  </div>
								<?php } ?>
							  </div>
							  <div class="modal-footer">
								<input type="hidden" name="type" value="CompanyComputers">
								<button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;color:#fff;">Add</button>
							  </div>
							</div>
						  </div>
						</div>
					</form>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="padding-left:20px;">
					<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">			
						<div class="card-block text-center">
							<h6 class="m-b-15">Assets</h6>
							<div class="risk-rate">
								<span><b><?php echo $resultCount; ?></b></span>
							</div>
							<h6 class="m-b-10 m-t-10">&nbsp;</h6>
							<a onclick="loadSection('Assets');" style="cursor:pointer;color:<?php echo $siteSettings['theme']['Color 2']; ?>" class="text-c-yellow b-b-warning">View All Assets</a>
							<div style="margin-top:10px;" class="row justify-content-center m-t-10 b-t-default m-l-0 m-r-0">
								<div class="col m-t-15 b-r-default">
									<h6 class="text-muted">Online</h6>
									<h6>13</h6>
								</div>
								<div class="col m-t-15">
									<h6 class="text-muted">Offline</h6>
									<h6>2</h6>
								</div>
							</div>
						</div>
						<button onclick="printData();" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" title="Export As CSV File" class="btn btn-warning btn-block p-t-15 p-b-15">Export Table</button>		
					</div>			
					<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
						<div style="height:45px" class="panel-heading">
							<h5 class="panel-title">Notes
							<form style="display:inline" method="post">
								<input type="hidden" name="delNote" value="true"/>
								<button type="submit" class="btn btn-danger btn-sm" style="float:right;padding:5px;"><i class="fas fa-trash"></i>&nbsp;&nbsp;&nbsp;Clear All</button>
							</form>
							</h5>
						</div>
						<div class="card-block texst-center">
							<?php
							$count = 0;
							$query = "SELECT ID, notes FROM users where ID='".$_SESSION['userid']."'";
							$results = mysqli_query($db, $query);
							$data = mysqli_fetch_assoc($results);
							$notes = $data['notes'];
							if($notes!=""){
								$allnotes = explode("|",$notes);
								foreach(array_reverse($allnotes) as $note) {
									if($note==""){ continue; }
									if($count>=5){ break; }
									$note = explode("^",$note);
									$count++;
							?>
								<a title="View Note" onclick="$('#notetitle').text('<?php echo $note[0]; ?>');$('#notedesc').text('<?php echo $note[1]; ?>');" data-toggle="modal" data-target="#viewNoteModal">
									<li  style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item">
										<i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>
										<?php echo ucwords($note[0]);?>
									</li>
								</a>
							<?php } } ?>
							<?php if($count==0){ ?>
								<li class="list-group-item">No Notes</li>
							<?php } ?>
						</div>
						<button style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none" data-toggle="modal" data-target="#noteModal" title="Create New Note" class="btn btn-warning btn-block p-t-15 p-b-15">Create New Note</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	function printData(filename) {
		var csv = [];
		var rows = document.querySelectorAll("#printTable table tr");
		for (var i = 0; i < rows.length; i++) {
			var row = [], cols = rows[i].querySelectorAll("td, th");
			for (var j = 0; j < cols.length; j++)
				row.push(cols[j].innerText.replace("Disk Space","").replace("Actions","").replace(/[^\w\s]/gi,"").replace(/\s/g,""));
			csv.push(row.join(","));
		}
		downloadCSV(csv.join("\n"), "page.csv");
	}
	function downloadCSV(csv, filename) {
		var csvFile;
		var downloadLink;
		csvFile = new Blob([csv], {type: "text/csv"});
		downloadLink = document.createElement("a");
		downloadLink.download = filename;
		downloadLink.href = window.URL.createObjectURL(csvFile);
		downloadLink.style.display = "none";
		document.body.appendChild(downloadLink);
		downloadLink.click();
	}
</script>
<script>
	$(document).ready(function() {
		$('#dataTable').dataTable( {
			"paging": false,
			"order": []
		} );
	});
</script>
<script src="js/tagsinput.js"></script>
