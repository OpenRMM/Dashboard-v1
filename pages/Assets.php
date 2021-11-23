<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page']);

$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);
$username=$user['username'];

//assets
$query = "SELECT ID FROM computers where active='1' and online='1'";
$assets1 = mysqli_num_rows(mysqli_query($db, $query));
$query = "SELECT ID FROM computers where active='1' and online='0'";
$assets2 = mysqli_num_rows(mysqli_query($db, $query));
?>
	<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
		<h5 style="color:#0c5460">Asset List 
			<button title="Refresh" onclick="loadSection('Assets');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-sync"></i>
			</button>	
			<p>View, Sort, Assign or Export all of the assets. </p>
		</h5>
	</div>	
	<div class="row" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding-bottom:20px;padding-top:0px;">
		<form method="post" action="/">
			<div class="card table-card" id="printTable" style="marsgin-top:-20px;padding:10px;border-radius:6px;"> 
				<div styles="float:right;" class="dropdown">
					<button type="button" class="btn btn-dark dropsdown-toggle btn-sm" style="float:right" data-toggle="dropdown">
						Actions <i class="fas fa-sort-down"></i>
					</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" data-toggle="modal" href="javascript:void(0)" data-target="#companyComputersModal2" >Assign Selected To <?php echo $msp; ?></a>
						<?php if($_SESSION['accountType']=="Admin"){ ?>
							<hr>
							<a class="dropdown-item bg-danger" data-toggle="modal" href="javascript:void(0)" data-target="#deleteAssets" >Delete Selected Assets</a>
						<?php } ?>
					</div>
				</div>
				<br>
				<div style="overflow-x:auto">
				   <table id="<?php echo $_SESSION['userid']; ?>Assets" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;width:100%" class="table table-hover  table-borderless">				
							<thead>
								<tr style="border-bottom:2px solid #d3d3d3;">
									<th >
										<div class="form-check">
											<input class="form-check-input" type="checkbox" value="<?php echo $result['ID']; ?>" style="margin-top:-15px" name="computers[]" id="checkall">	
										</div>
									</th>
									
									<th scope="col">ID</th>		  
									<th scope="col">Hostname</th>
									<th scope="col">Logged In</th>
									<th scope="col">Version</th>
									<th scope="col"><?php echo $msp; ?></th>
									<th scope="col">Client Name</th>
									<th scope="col">Client Phone</th>
									<th scope="col">Client Email</th>
									<th scope="col">Domain</th>
									<th scope="col">Disk</th>
									<th scope="col">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php
									//Get Total Count					
									$query = "SELECT * FROM computers WHERE active='1' ORDER BY ID ASC";
									//Fetch Results
									$count = 0;
									$results = mysqli_query($db, $query);
									$resultCount = mysqli_num_rows($results);	
									while($result = mysqli_fetch_assoc($results)){
										$getWMI = array("logical_disk","general");
										$data = getComputerData($result['ID'], $getWMI);
										//Determine Warning Level
										$freeSpace = $data['logical_disk']['Response']['C:']['FreeSpace'];
										$size = $data['logical_disk']['Response']['C:']['Size'];
										$used = $size - $freeSpace;
										$usedPct = round(($used/$size) * 100);
										if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
											$pbColor = "red";
										}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
											$pbColor = "#ffa500";
										}else{ $pbColor = "#03925e"; }
										$count++;
										$query2 = "SELECT * FROM companies where active='1' and ID='".$result['company_id']."'";
										$results2 = mysqli_query($db, $query2);
										$company = mysqli_fetch_assoc($results2)
								?>
								<tr id="row<?php echo $result['ID']; ?>">
									<td>
										<div class="form-check">
											<input class="form-check-input checkbox" type="checkbox" value="<?php echo $result['ID']; ?>" name="computers[]" id="flexCheckDefault">	
										</div>
									</td>
									<td>
										<span title="ID"><?php echo $result['ID']; ?></span>
										<span style="display:none" ><?php if($result['online']=="1"){echo"Online";}else{echo "Offline";} ?></span>
									</td>
									<td onclick="loadSection('Asset_General', '<?php echo $result['ID']; ?>');">
										<?php
											$icons = array("desktop","server","laptop","tablet","allinone","other");
											if(in_array(strtolower(str_replace("-","",$result['computer_type'])), $icons)){
												$icon = strtolower(str_replace("-","",$result['computer_type']));
												if($icon=="allinone")$icon="tv";
												if($icon=="tablet")$icon="tablet-alt";
												if($icon=="other")$icon="microchip";
											}else{
												$icon = "server";
											}  
										?>
										<span style="color:#000;font-size:12px;cursor:pointer" >
											<?php if($result['online']=="0") {?>
												<i class="fas fa-<?php echo $icon;?>" style="color:#666;font-size:12px;" title="Offline"></i>
											<?php }else{?>
												<i class="fas fa-<?php echo $icon;?>" style="color:green;font-size:12px;" title="Online"></i>
											<?php }?>
											&nbsp;<?php echo textOnNull(strtoupper($data['general']['Response'][0]['csname']),"Unavailable");?>
										</span>
									</td>
										<?php
											$username = textOnNull($data['general']['Response'][0]['UserName'], "Unknown");
										?>
									<td style="cursor:pointer" onclick="$('input[type=search]').val('<?php echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username)); ?>'); $('input[type=search]').trigger('keyup'); $('#dataTable').animate({ scrollTop: 0 }, 'slow');">
										<?php
											echo ucwords((strpos($username, "\\")!==false ? explode("\\", $username)[1] : $username));
										?>
									</td>
									<td style="cursor:pointer" onclick="$('input[type=search]').val('<?php echo textOnNull(str_replace('Microsoft', '',$data['general']['Response'][0]['Caption']), "Windows");?>'); $('input[type=search]').trigger('keyup'); "><?php echo textOnNull(str_replace('Microsoft', '',$data['general']['Response'][0]['Caption']), "Windows");?></td>
									<td onclick="$('input[type=search]').val('<?php echo textOnNull(crypto('decrypt',$company['name'],$company['hex']), "N/A");?>');$('input[type=search]').trigger('keyup'); $('#dataTable').animate({ scrollTop: 0 }, 'slow');">
										<span id="col<?php echo $result['ID']; ?>" style="color:#000;font-size:12px;cursor:pointer">
											<?php echo textOnNull(crypto('decrypt',$company['name'],$company['hex']), "Not Assigned");?>
										</span>
									</td>
									<td><?php echo textOnNull(crypto('decrypt',$result['name'],$result['hex']), "N/A");?></td>
									<td><?php echo textOnNull(crypto('decrypt',$result['phone'],$result['hex']), "N/A");?></td>
									<td><?php echo textOnNull(crypto('decrypt',$result['email'],$result['hex']), "N/A");?></td>
									<td><?php echo textOnNull($data['general']['Response'][0]['Domain'], "N/A");?></td>
									<td>
										<div class="progress" style="margin-top:5px;height:10px;background:#a4b0bd" title="<?php echo $usedPct;?>%">
											<div class="progress-bar" role="progressbar" style=";background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
										</div>
									</td>
									<td>
										<button onclick="loadSection('Asset_Edit', '<?php echo $result['ID']; ?>');" title="Edit Client" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="form-inline btn btn-dark btn-sm">
											<i class="fas fa-pencil-alt"></i>
										</button>
										<button title="View Asset" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;background:#0ac282;" onclick="loadSection('Asset_General', '<?php echo $result['ID']; ?>');" class="form-inline btn btn-warning btn-sm">
											<i class="fas fa-eye"></i>
										</button>
									</td>
								</tr>
							<?php }?>
							<?php  if($count==0){ ?>
								<tr>
									<td colspan=9>
										<center>
											<h6>No Assets To Display</h6>
										</center>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<!------------- Add Company Computers ------------------->
				<div id="companyComputersModal2" class="modal fade" role="dialog">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="pageAlert_title">Assign Assets</h5>
							</div>
							<div class="modal-body">
								<h6 id="pageAlert_title">Select The <?php echo $msp; ?> You Would Like To Add These Assets To</h6><hr>
								<div class="list-group" style="padding-bottom:10%">
								<?php							
									$query = "SELECT ID, name,hex FROM companies ORDER BY ID DESC LIMIT 100";
									$results = mysqli_query($db, $query);
									$companyCount = mysqli_num_rows($results);
									while($company = mysqli_fetch_assoc($results)){		
								?>		
									<input type="radio" company="<?php echo $company['ID']; ?>" required name="companies" value="<?php echo crypto('decrypt', $company['name'], $company['hex']); ?>" class="form-check-input" id="CompanyCheck<?php echo $company['ID']; ?>">
									<label class="list-group-item" for="CompanyCheck<?php echo $company['ID']; ?>">
										<span style="text-align:left"><?php echo crypto('decrypt', $company['name'], $company['hex']); ?></span>
                           			 </label>
								<?php } ?>
									<input type="radio" company="0" required name="companies" value="Not Assigned" class="form-check-input" id="CompanyCheck<?php echo $company['ID']; ?>">
									<label class="list-group-item" for="CompanyCheck<?php echo $company['ID']; ?>">
										<span style="text-align:left">Not Assigned</span>
                           			 </label>
								</div>
							</div>
							<div class="modal-footer">
								<input type="hidden" name="type" value="CompanyComputers">
								<button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
								<button type="button" data-dismiss="modal" onclick="assignAssets()" class="btn btn-sm btn-warning" style="color:#fff;">Add</button>
							</div>
						</div>
					</div>
				</div>
				<div id="deleteAssets" class="modal fade" role="dialog">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="pageAlert_title">Delete Assets</h5>
							</div>
							<div class="modal-body">
								<h6 id="pageAlert_title">Are you sure you would like to delete the selected assets?</h6><hr>		
							</div>
							<div class="modal-footer">								
								<button type="button" class="btn btn-sm" data-dismiss="modal">Close</button>
								<button type="button"  onclick="deleteAssets()" data-dismiss="modal" class="btn btn-sm btn-danger" style="color:#fff;">Delete</button>
							</div>
						</div>
					</div>
				</div>
				</form>
			</div>		
			<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="padding-left:20px;">
				<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">			
					<div class="card-block text-center">
						<h6 style="cursor:pointer;font-weight:bold" onclick="$('input[type=search]').val(''); $('input[type=search]').trigger('keyup');" class="m-b-15"><?php echo $resultCount; ?> Total Assets</h6>
					
						<div class="row justify-content-center m-t-10 b-t-default m-l-0 m-r-0">
							<div style="cursor:pointer;padding:7px;border-radius:4px" onclick="$('input[type=search]').val('Online'); $('input[type=search]').trigger('keyup');" class="col m-t-15 secbtn">
								<h6 class="text-muted"><b>Online</b></h6>
								<h6><?php echo $assets1; ?> Asset</h6>
							</div>
							<div style="cursor:pointer;padding:7px;border-radius:4px" onclick="$('input[type=search]').val('Offline'); $('input[type=search]').trigger('keyup');" class="col m-t-15 secbtn">
								<h6 class="text-muted"><b>Offline</b></h6>
								<h6><?php echo $assets2; ?> Asset</h6>
							</div>
						</div>
					</div>
				</div>
				<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
					<div style="height:45px" class="panel-heading">
						<h5 class="panel-title">Notes
							<button id="delNote" type="button" onclick="deleteNote('1');" class="delNote btn btn-danger btn-sm" style="float:right;padding:5px;"><i class="fas fa-trash"></i>&nbsp;&nbsp;&nbsp;Clear All</button>
						</h5>
					</div>
					<div id="TextBoxesGroup" class="card-block texst-center">
						<?php
						$count = 0;
						$query = "SELECT ID, notes,hex FROM users where ID='".$_SESSION['userid']."'";
						$results = mysqli_query($db, $query);
						$data = mysqli_fetch_assoc($results);
						$notes = crypto('decrypt',$data['notes'],$data['hex']);
						if($notes!=""){
							$allnotes = explode("|",$notes);
							foreach(array_reverse($allnotes) as $note) {
								if($note==""){ continue; }
								if($count>=5){ break; }
								$note = explode("^",$note);
								$count++;
						?>
							<a title="View Note" class="noteList" onclick="$('#notetitle').text('<?php echo $note[0]; ?>');$('#notedesc').text('<?php echo $note[1]; ?>');" data-toggle="modal" data-target="#viewNoteModal">
								<li style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item">
									<i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>
									<?php echo ucwords($note[0]);?>
								</li>
							</a>
						<?php } } ?>
						<?php if($count==0){ ?>
							<li  class="no_noteList list-group-item">No Notes</li>
						<?php }else{ ?>
						<li class="no_noteList list-group-item" style="display:none" >No Notes</li>
						<?php } ?>
					</div>
					<button data-toggle="modal" data-target="#noteModal" style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none;color:#fff" title="Create New Note" class="btn btn-sm">Create New Note</button>
				</div>	
			</div>
		</div>
	</div>
</div>
<!--------------------------------------modals---------------------------------------------->

<!---------------------------------End MODALS------------------------------------->
<script>
$('#<?php echo $_SESSION['userid']; ?>Assets').DataTable( {
	"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
	colReorder: true,
	dom: 'Bfrtip',
	stateSave: true,
	columnDefs: [
        {
            "targets": [1,6,7,8],
            "visible": false
        }],
	buttons: ['pageLength',
				{
					extend: 'colvis',
					title: 'Column Visibility',
					text:'Column Visibility',
					//Columns to export
					columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]  
				},{
					extend: 'excelHtml5',
					title: 'OpenRMM Asset List',
					text:'Export to excel',
					//Columns to export
					exportOptions: {
						columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
					}
				},{
					extend: 'pdfHtml5',
					title: 'OpenRMM Asset List',
					text: 'Export to PDF',
					//Columns to export
					exportOptions: {
						columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
					}
				}
	]
} );	
</script>
<script type='text/javascript'>
 $(document).ready(function(){
   // Check or Uncheck All checkboxes
   $("#checkall").change(function(){
     var checked = $(this).is(':checked');
     if(checked){
       $(".checkbox").each(function(){
         $(this).prop("checked",true);
       });
     }else{
       $(".checkbox").each(function(){
         $(this).prop("checked",false);
       });
     }
   });
 
  // Changing state of CheckAll checkbox 
  $(".checkbox").click(function(){
 
    if($(".checkbox").length == $(".checkbox:checked").length) {
      $("#checkall").prop("checked", true);
    } else {
      $("#checkall").prop("checked", false);
    }

  });
});
</script>
<?php if($_GET['other']!=""){
?>
<script>
	$('input[type=search]').val('<?php echo clean(base64_decode($_GET['other'])); ?>');
	$('input[type=search]').trigger('keyup');
</script>
<?php
}
?>
