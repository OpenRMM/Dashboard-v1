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
	$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];
?>
	<div class="row" style="margin-bottom:10px;margin-top:20px;border-radius:3px;overflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding:5px;padding-bottom:20px;padding-top:30px;border-radius:6px;">			 
			   <form method="post" action="index.php">
				   <div class="card table-card" id="printTable" style="margin-top:-40px;padding:10px">  
				   <div class="card-header">
						<h5>Asset List</h5>
						<div class="card-header-right">
							<ul class="list-unstyled card-option">
								<li><i class="feather icon-maximize full-card"></i></li>
								<li><i class="feather icon-minus minimize-card"></i></li>
								<li><i class="feather icon-trash-2 close-card"></i></li>
							</ul>
						</div>
					</div>
					<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">				
					  <thead>
						<tr style="border-bottom:2px solid #d3d3d3;">
						  <th scope="col">
							<input onclick="toggle(this);" id="allcomputers" value="<?php echo $result['ID']; ?>" style="display:inline;appearance:none;" type="checkbox"/>
						  </th>		  
						  <th scope="col">Hostname</th>
						  <th scope="col">Logged In</th>
						  <th scope="col">Version</th>
						  <th scope="col">Company</th>
						  <th scope="col">Disk</th>
						  <th scope="col">Actions</th>
						</tr>
					  </thead>
					  <tbody>
						<?php
							//Get Total Count
							$query = "SELECT ID FROM computerdata where active='1'";
							$results = mysqli_query($db, $query);
							$resultCount = mysqli_num_rows($results);							
							$query = "SELECT * FROM computerdata
									  LEFT JOIN companies ON companies.CompanyID = computerdata.CompanyID
									  WHERE computerdata.active='1'
									  ORDER BY computerdata.hostname ASC";
						
							//Fetch Results
							$count = 0;
							$results = mysqli_query($db, $query);
							while($result = mysqli_fetch_assoc($results)){
								if($search==""){
									$getWMI = array("WMI_LogicalDisk", "WMI_OS", "WMI_ComputerSystem", "Ping");
								}else{
									$getWMI = array("*");
								}
								$data = getComputerData($result['ID'], $getWMI);

								//Determine Warning Level
								$freeSpace = $data['WMI_LogicalDisk'][0]['FreeSpace'];
								$size = $data['WMI_LogicalDisk'][0]['Size'];
								$used = $size - $freeSpace;
								$usedPct = round(($used/$size) * 100);
								if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
									$pbColor = "red";
								}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
									$pbColor = "#ffa500";
								}else{ $pbColor = $siteSettings['theme']['Color 4']; }
								$count++;
						?>
						<tr>
							  <td>
								<span title="ID"><?php echo $result['ID']; ?></span>
							  </td>
							  <td>
								<?php
									$icons = array("desktop"=>"desktop","server"=>"server","laptop"=>"laptop");
									if(in_array(strtolower($result['computerType']), $icons)){
										$icon = $icons[strtolower($result['computerType'])];
									}else{
										$icon = "desktop";
									}
								?>
							  <?php echo $json['DefaultPrograms'][10]['Program']; ?>
								<a style="color:<?php echo $siteSettings['theme']['Color 1']; ?>;font-size:12px"  href="#" onclick="loadSection('General', '<?php echo $result['ID']; ?>');">
									<?php if($result['online']=="0") {?>
										<i class="fas fa-<?php echo $icon;?>" style="color:#666;font-size:12px;" title="Offline"></i>
									<?php }else{?>
										<i class="fas fa-<?php echo $icon;?>" style="color:green;font-size:12px;" title="Online"></i>
									<?php }?>
									&nbsp;<?php echo strtoupper($result['hostname']);?> <?php echo strtoupper($result['Hostname']);?>
								</a>
							  </td>
							  <?php
									$username = textOnNull($data['WMI_ComputerSystem'][0]['UserName'], "Unknown");
								?>
							  <td style="cursor:pointer" onclick="$('input[type=search]').val('<?php echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username)); ?>'); $('input[type=search]').trigger('keyup'); $('#dataTable').animate({ scrollTop: 0 }, 'slow');">
								<?php
									echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username));
								?>
							  </td>
							  <td style="cursor:pointer" onclick="$('input[type=search]').val('<?php echo $data['WMI_OS'][0]['Caption'];?>'); $('input[type=search]').trigger('keyup'); c"><?php echo textOnNull(str_replace('Microsoft', '',$data['WMI_OS'][0]['Caption']), "Windows");?></td>
							  <td>
								<a style="color:#000;font-size:12px" href="#" onclick="$('input[type=search]').val('<?php echo textOnNull($result['name'], "N/A");?>');$('input[type=search]').trigger('keyup'); $('#dataTable').animate({ scrollTop: 0 }, 'slow');">
									<?php echo textOnNull($result['name'], "Not Assigned");?>
								</a>
							  </td>
							  <td>
								<div class="progress" style="margin-top:5px;height:10px;background:<?php echo $siteSettings['theme']['Color 3']; ?>;" title="<?php echo $usedPct;?>%">
									<div class="progress-bar" role="progressbar" style=";background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
							  </td>
							  <td>
								<a href="#" onclick="loadSection('Edit', '<?php echo $result['ID']; ?>');" title="Edit Client" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="form-inline btn btn-dark btn-sm">
									<i class="fas fa-pencil-alt"></i>
								</a>
								<a title="View Client" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;background:#0ac282;" onclick="loadSection('General', '<?php echo $result['ID']; ?>');" href="#" class="form-inline btn btn-warning btn-sm">
									<i class="fas fa-eye"></i>
								</a>
							  </td>
							</tr>
						<?php }?>
						<?php  if($count==0){ ?>
							<tr>
								<td colspan=9>
									<p style="text-align:center;font-size:18px;">
										<b>No Assets To Display</b>
									</p>
								</td>
							</tr>
						<?php } ?>
					  </tbody>
					</table>
				</div>
				<!------------- Add Company Computers ------------------->
				<div id="companyComputersModal2" class="modal fade" role="dialog">
				  <div class="modal-dialog modal-sm">
					<div class="modal-content">
					  <div class="modal-header">
						<h5 class="modal-title" id="pageAlert_title">Assign Assets</h5>
					  </div>
					  <div class="modal-body">
						<h6 id="pageAlert_title">Select The Customer You Would Like To Add These Assets Too</h6>
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
						<button type="submit" class="btn btn-sm btn-warning" style="color:#fff;">Add</button>
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
					<a onclick="loadSection('Assets');" style="cursor:pointer" class="text-c-yellow b-b-warning">View All Assets</a>
					<div class="row justify-content-center m-t-10 b-t-default m-l-0 m-r-0">
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
				<button onclick="printData();" title="Export As CSV File" class="btn btn-warning btn-block p-t-15 p-b-15">Export Table</button>		
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
						<li style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item">
							<i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>
							<?php echo ucwords($note[0]);?>
						</li>
					</a>
				<?php } } ?>
				<?php if($count==0){ ?>
					<li>No Notes</li>
				<?php } ?>
			</div>
			<button data-toggle="modal" data-target="#noteModal" title="Create New Note" class="btn btn-warning btn-block p-t-15 p-b-15">Create New Note</button>
		</div>	
		</div>
	</div>
</div>
<!--------------------------------------modals---------------------------------------------->

<!---------------------------------End MODALS------------------------------------->
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
		$('#dataTable').DataTable( {
			"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]]
		} );
	} );
</script>
<script src="js/tagsinput.js"></script>