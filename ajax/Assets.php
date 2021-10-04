<?php
	//$sort = (trim($_GET['sort'])!="" ? $_GET['sort'] : "ID");
	$filters = clean($_GET['filters']);
	$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];

?>
	<div class="row" style="margin-bottom:10px;margin-top:20px;border-radius:3px;overflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding:5px;padding-bottom:20px;padding-top:1px;border-radius:6px;">
				<div class="d-none d-md-block">					
					<div style="margin-top:10px;display:inline;float:right" class="input-group">  
					  <div style="" class="input-group-append search-field-dashboard">
						&nbsp;<input id="filterInput" data-role="tagsinput" value="<?php echo $filters; ?>"
						 name="filters" class="form-control form-control-sm form-control-borderless" type="text" placeholder="Filter Results"/>
						<button class="btn btn-sm" style="padding-left:20px;margin-left:-4px;
						border-radius:0px 4px 4px 0px;background:#01a9ac;color:#fff;" type="button" data-toggle="modal" data-target="#searchFilterModal">
							<i class="fas fa-cog"></i>
						</button>
						<a href="#" title="Refresh" onclick="loadSection('Assets');" class="btn btn-sm btn-warning" style="border-radius:3px;float:right;margin-left:5px;">
							<i style="margin-top:7px" class="fas fa-sync"></i>
						</a>
					  </div>
					</div>			 
				</div>	 
			   <form method="post" action="index.php">
				   <div class="card table-card" id="printTable" style="margin-top:15px;padding:10px">  
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
							$getFilters = explode(",", trim($filters, ","));							
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
								//Filters
								if(count($getFilters) > 0 && $filters!=""){
									foreach($getFilters as $search){
										$filter = explode(":", trim($search));
										$filterType = trim($filter[0]);
										$filterValue = trim($filter[1]);
										//verify filter type
										if($siteSettings['Search Filters'][$filterType]['WMI_Name']!=""){
											$filter = $siteSettings['Search Filters'][$filterType];
											$WMI_Name = $filter['WMI_Name'];
											$WMI_Key = $filter['WMI_Key'];
											//validate value
											if(in_array($filterValue, $filter['options']) || in_array("*", $filter['options'])){
												if(strpos(strtolower($data[$WMI_Name."_raw"]), strtolower($filterValue))!==false){}else{continue(2);}
											}
										}
									}
								}
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
								<input class="computerChkBox" name="computers[]" value="<?php echo $result['ID']; ?>" style="display:inline;appearance:none;" type="checkbox" oncheck>
							  &nbsp;
								<?php if($count==$limit - 10){?>
									<div id="l"></div>
								<?php } ?>
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
									<?php if(!$data['Online']) {?>
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
							  <td style="cursor:pointer" onclick="searchFilterAdd('LoggedIn: <?php echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username));?>');">
								<?php
									echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username));
								?>
							  </td>
							  <td style="cursor:pointer" onclick="searchFilterAdd('WinVer: <?php echo $data['WMI_OS'][0]['Caption'];?>');"><?php echo textOnNull(str_replace('Microsoft', '',$data['WMI_OS'][0]['Caption']), "Windows");?></td>
							  <td>
								<a style="color:#000;font-size:12px" href="#" onclick="searchItem('<?php echo textOnNull($result['name'], "N/A");?>');">
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
						<li  style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="list-group-item">
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
<div id="searchFilterModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">
	<div class="modal-content">
	  <div class="modal-header">
		<h6 class="modal-title">Filter Search</h6>
	  </div>
	  <div class="modal-body">
		<p style="font-size:14px"> Select A Tag Or Multiple Tags To Refine Your Search. Please Note, Tags Are Not Case-Sensitive.</p>
		<div class="row">
			<div class="col-md-12" style="padding:5px;">
				<?php
				$count=0;
				foreach($siteSettings['Search Filters'] as $type=>$filter){
					$count++;					
					?>
						<div class="card" style="margin-bottom:5px;padding:5px;">
							<h6 style="font-size:12px;" ><?php echo $filter['Nicename'];?></h6>
							<div class="row" style="margin-left:5px;">
								<?php foreach($filter['options'] as $option){?>
									<?php if($option == "*"){?>
										<?php $id = "sr".rand(100000,1000000000);?>
										<br>
										<div style="margin-left:-10px;margin-top:0px;" class="col-md-8 input-group mbs-3">
										  <input style="height:31px;font-size:12px" type="text" class="form-control" placeholder="Custom" id="<?php echo $id;?>">
										  <div class="input-group-append">
											<button style="height:31px;font-size:12px;;border-color:#696969;color:#696969" class="btn btn-outline btn-sm" type="button" id="button-addon2" onclick="searchFilterAdd('<?php echo $type;?>: '+$('#<?php echo $id;?>').val());">Go</button>
										  </div>
										</div>
									<?php }else{?>
										<button onclick="searchFilterAdd('<?php echo $type.": ".$option;?>');" style="border-radius:6px;margin-right:2px;margin-bottom:8px;padding:5px;font-size:12px;border-color:#696969;color:#696969" class="btn-outline btn btn-sm">
											<?php echo $option;?>
										</button>
									<?php }?>
								<?php }?>
							</div>
						</div>					
				<?php }?>
			</div>
			
		</div>
	  </div>
	  <script>
	  //need to check if part after colon is empty or not. otherwise can filter ex. WinVer: (blank)
		function searchFilterAdd(filter){
			if ($("#filterInput").val().indexOf(filter) !== -1) {
				$("#searchFilterModal").modal('hide');
				$('body').removeClass('modal-open');
				$('.modal-backdrop').remove();
			}else{
				$("#filterInput").val($("#filterInput").val() + ", " + filter);
				$("#filterInput").val($("#filterInput").val().replace(/(^,)|(,$)/g, ""));
				$("#searchFilterModal").modal('hide');
				$('body').removeClass('modal-open');
				$('.modal-backdrop').remove();
				search($('#searchInput').val(),'Assets','', $('#filterInput').val());
			}
		}
	  </script>
	  <div class="modal-footer">
		<button type="button" class="btn btn-sm btn-warning"  data-dismiss="modal">Close</button>
	  </div>
	</div>
  </div>
</div>
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
	function searchItem(text, page="Assets", ID=0, filters="", limit=25){
		$(".loadSection").load("ajax/"+page+".php?limit="+limit+"&search="+encodeURI(text)+"&ID="+ID+"&filters="+encodeURI(filters));
	}
	$('#searchInput').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			searchItem($('#searchInput').val(),'Assets','', $('#filterInput').val());
		}
	});
	$('input[type="checkbox"][name="change"]').change(function() {
		if(this.checked) {
		}else{
		}
	});
	</script>
	<script>
    $(document).ready(function() {
          $('#dataTable').DataTable();
    });
</script>
<script src="js/tagsinput.js"></script>