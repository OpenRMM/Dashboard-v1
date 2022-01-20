<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page']);

$get = clean(base64_decode($_GET['other']));
$query = "SELECT username,nicename FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);
$username=$user['username'];

//counts
$query = "SELECT ID FROM tickets where status<>'Closed' and active='1'";
$ticketCount = mysqli_num_rows(mysqli_query($db, $query));
$query = "SELECT ID FROM tickets where active='1'";
$ticketCountAll = mysqli_num_rows(mysqli_query($db, $query));
$query = "SELECT ID FROM tickets where status='Closed' and active='1'";
$ticketCount2 = mysqli_num_rows(mysqli_query($db, $query));
$query = "SELECT ID FROM tickets where assignee='".$_SESSION['userid']."' and active='1'";
$ticketCount3 = mysqli_num_rows(mysqli_query($db, $query));
$query = "SELECT ID FROM tickets where assignee='0' and active='1'";
$ticketCount4 = mysqli_num_rows(mysqli_query($db, $query));
?>
	<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
		<h5 style="color:#0c5460">Service Desk <span style="color:#707070;font-size:16px">(<?php echo $ticketCount; ?> Open Tickets)</span>
			<button title="Refresh" onclick="loadSection('Service_Desk_Home');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-sync"></i>
			</button>	
		</h5>
	</div>	
	<div class="row" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="padding-left:20px;">
			<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
					<div style="height:45px" class="panel-heading">
						<h5 class="panel-title">Ticket filters
						</h5>
					</div>
					<ul class="list-group">
						<li onclick="loadSection('Service_Desk_Home','','','all');" style="cursor:pointer;<?php if($get=="all" or $get==""){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
							<div class="bg-default" style="font-size:12px;margin-right:10px;float:left;display:inline;color:#000;padding:5px;border-radius:100px;text-align:center;min-width:20px;max-width:40px;height:20px;padding-top:1px">
								<?php echo $ticketCountAll; ?>
							</div>
							All Tickets
						</li>
						<li onclick="loadSection('Service_Desk_Home','','','me');" style="cursor:pointer;<?php if($get=="me"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
							<div class="bg-default" style="font-size:12px;margin-right:10px;float:left;display:inline;color:#000;padding:5px;border-radius:100px;text-align:center;min-width:20px;max-width:40px;height:20px;padding-top:1px">
								<?php echo $ticketCount3; ?>
							</div>
							Assigned to me
						</li>
						<li onclick="loadSection('Service_Desk_Home','','','noone');" style="cursor:pointer;<?php if($get=="noone"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
							<div class="bg-default" style="font-size:12px;margin-right:10px;float:left;display:inline;color:#000;padding:5px;border-radius:100px;text-align:center;min-width:20px;max-width:40px;height:20px;padding-top:1px">
								<?php echo $ticketCount4; ?>
							</div>
							Unassigned
						</li>
						<li onclick="loadSection('Service_Desk_Home','','','closed');" style="cursor:pointer;<?php if($get=="closed"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
							<div class="bg-default" style="font-size:12px;margin-right:10px;float:left;display:inline;color:#000;padding:5px;border-radius:100px;text-align:center;min-width:20px;max-width:40px;height:20px;padding-top:1px">
								<?php echo $ticketCount2; ?>
							</div>
							Closed
						</li>
					</ul>
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
							<a style="text-decoration:none" title="View Note" class="noteList" onclick="$('#notetitle').text('<?php echo $note[0]; ?>');$('#notedesc').text('<?php echo $note[1]; ?>');" data-bs-toggle="modal" data-bs-target="#viewNoteModal">
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
					<button data-bs-toggle="modal" data-bs-target="#noteModal" style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none;color:#fff" title="Create New Note" class="btn btn-sm">Create New Note</button>
				</div>	
			</div>
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding-bottom:20px;padding-top:0px;">
		<form method="post" action="/">
			<div class="card table-card" id="printTable" style="marsgin-top:-20px;padding:10px;border-radius:6px;"> 
				<div styles="float:right;" class="dropdown">
				
					<button type="button" class="btn btn-dark dropsdown-toggle btn-sm" style="float:right;margin-left:5px" data-bs-toggle="dropdown">
						Actions <i class="fas fa-sort-down"></i>
					</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" data-bs-toggle="modal" href="javascript:void(0)" data-bs-target="#companyComputersModal2" >Assign Selected To <?php echo $msp; ?></a>
						
						<?php if($_SESSION['accountType']=="Admin"){ ?>
							<hr>
							<a class="dropdown-item bg-danger" data-bs-toggle="modal" href="javascript:void(0)" data-bs-target="#deleteTickets" >Delete Selected Tickets</a>
						<?php } ?>
					</div>
					<button title="Create New Ticket" onclick="loadSection('Service_Desk_New_Ticket');" type="button" class="btn btn-sm" style="float:right;background:#0c5460;color:#d1ecf1">
						<i class="fas fa-plus"></i>
					</button>
				</div>
				<br>
				<div style="overflow-x:auto">
				   <table id="<?php echo $_SESSION['userid']; ?>Tickets" style="line-height:20px;font-size:12px;margin-top:8px;font-family:Arial;width:100%" class="table table-hover table-striped table-borderless">				
							<thead>
								<tr style="border-bottom:2px solid #d3d3d3;">
									<th >
										<div class="form-check">
											<input class="form-check-input" type="checkbox" value="<?php echo $result['ID']; ?>" style="margin-top:10px" name="computers[]" id="checkall">	
										</div>
									</th>
									
									<th scope="col">#</th>		  
									<th style="min-width:80px" scope="col">State</th>
									<th scope="col">Title</th>
									<th scope="col">Priority</th>
									<th scope="col">Category</th>
									<th scope="col">Assignee</th>
									<th scope="col">Requestor</th>
									<th scope="col">Date/Time</th>
								</tr>
							</thead>
							<tbody>
								<?php
									if($get=="all" or $get==""){$where="";}
									if($get=="me"){$where=" and assignee='".$_SESSION['userid']."'";}
									if($get=="closed"){$where=" and status='closed'";}
									if($get=="noone"){$where=" and assignee='0'";}
									$query = "SELECT * FROM tickets where active='1'".$where." ORDER BY ID DESC";
									$results = mysqli_query($db, $query);
									$resultCount = mysqli_num_rows($results);							
									
									//Fetch Results
									$count = 0;
									
									while($result = mysqli_fetch_assoc($results)){
										$getWMI = array("*");
										$data = getComputerData($result['ID'], $getWMI);
										$count++;
										$query = "SELECT username,nicename,hex,user_color FROM users WHERE ID='".$result['assignee']."' LIMIT 1";
										$results2 = mysqli_query($db, $query);
										$user3 = mysqli_fetch_assoc($results2);
										$name2 =  ucwords(crypto('decrypt',$user3['nicename'],$user3['hex']));
								?>
								<tr>
									<td>
										<div class="form-check">
											<input class="form-check-input checkbox" type="checkbox" value="<?php echo $result['ID']; ?>" name="computers[]" id="flexCheckDefault<?php echo $count;?>">	
										</div>
									</td>
									<td onclick="loadSection('Service_Desk_Ticket', '<?php echo $result['ID']; ?>');" id="row<?php echo $result['ID']; ?>">
										TKT<?php echo $result['ID']; ?>
									</td>
									<td style="min-width:80px">
										<div  class="btn-group">
											<button id="status<?php echo $result['ID']; ?>" style="padding:4px;font-size:12px;border-radius:10px 0px 0px 10px;background:#cce5ff" type="button" class="btn btn-sm"><?php echo $result['status']; ?></button>
											<button style="padding:4px;font-size:12px;border-radius:0px 10px 10px 0px;background:#cce5ff" type="button" class="btn dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
												<i class="fas fa-sort-down"></i>
											</button>
											<div class="dropdown-menu">
												<a onclick="updateTicket('status','New','<?php echo $result['ID']; ?>');" class="dropdown-item" href="javascript:void(0)">New</a>
												<a onclick="updateTicket('status','Closed','<?php echo $result['ID']; ?>');"class="dropdown-item" href="javascript:void(0)">Closed</a>
												<a onclick="updateTicket('status','In Progress','<?php echo $result['ID']; ?>');"class="dropdown-item" href="javascript:void(0)">In Progress</a>
												<a onclick="updateTicket('status','On Hold','<?php echo $result['ID']; ?>');"class="dropdown-item" href="javascript:void(0)">On Hold</a>
												<a onclick="updateTicket('status','Resolved','<?php echo $result['ID']; ?>');"class="dropdown-item" href="javascript:void(0)">Resolved</a>
												<a onclick="updateTicket('status','Awaiting Input','<?php echo $result['ID']; ?>');"class="dropdown-item" href="javascript:void(0)">Awaiting Input</a>
											</div>
										</div>
									</td>
									<td onclick="loadSection('Service_Desk_Ticket', '<?php echo $result['ID']; ?>');" id="row<?php echo $result['ID']; ?>" style="cursor:pointer" onclick="$('input[type=search]').val(''); $('input[type=search]').trigger('keyup'); $('#dataTable').animate({ scrollTop: 0 }, 'slow');">
										<a style="font-size:14px;color:#17a2b8" href="javascript:void(0)"><?php echo $result['title']; ?></a>
										<p style="font-size:12px">
											<?php 
											if(strlen($result['description']) > 30) {
												echo substr($result['description'],0 ,30)."..."; 
											}else{
												echo $result['description']; 
											}
											?>
										</p>
									</td>
									<td style="cursor:pointer" onclick="$('input[type=search]').val(''); $('input[type=search]').trigger('keyup'); "><?php echo $result['priority']; ?></td>
									<td><?php echo $result['category']; ?></td>
									<td <?php if($result['assignee']!="0"){ ?> style="cursor:pointer" onclick="loadSection('Profile', '<?php echo $result['assignee']; ?>');" <?php } ?>>
										<?php
										if($result['assignee']!="0"){
											list($first, $last) = explode(' ', $name2, 2);
											$name = strtoupper("$first[0]{$last[0]}"); 
										?>
										<div title="<?php echo $name2; ?>" style="font-size:12px;margin-right:10px;float:left;display:inline;background:<?php echo $user3['user_color']; ?>;color:#fff;padding:5px;border-radius:100px;text-align:center;width:30px;height:30px;padding-top:5px;margin-top:-5px">
											<?php echo $name; ?>
										</div>
										<?php } ?>				
									</td>
									<td><?php echo ucwords($result['requester']); ?></td>
									<td><?php echo ago($result['time']); ?></td>
								</tr>
							<?php }?>
							<?php  if($count==0){ ?>
								<tr>
									<td colspan=9>
										<center>
											<h6>No tickets to display.</h6>
										</center>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<!------------- Add Company Computers ------------------->
				<div id="companyComputsersModal2" class="modal fade" role="dialog">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="pageAlert_title">Assign Assets</h5>
							</div>
							<div class="modal-body">
								<h6 id="pageAlert_title">Select the <?php echo strtolower($msp); ?> you would like to add these assets to.</h6><hr>
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
								<button type="button" class="btn btn-sm" data-bs-dismiss="modal">Close</button>
								<button type="button" data-bs-dismiss="modal" onclick="assignAssets()" class="btn btn-sm btn-warning" style="color:#fff;">Add</button>
							</div>
						</div>
					</div>
				</div>
				<div id="deleteAssets" class="modal fade" role="dialog">
					<div class="modal-dialog modal-md">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="pageAlert_title">Delete Tickets</h5>
							</div>
							<div class="modal-body">
								<h6 id="pageAlert_title">Are you sure you would like to delete the selected tickets?</h6><hr>		
							</div>
							<div class="modal-footer">								
								<button type="button" class="btn btn-sm" data-bs-dismiss="modal">Close</button>
								<button type="button"  onclick="deleteAssets()" data-bs-dismiss="modal" class="btn btn-sm btn-danger" style="color:#fff;">Delete</button>
							</div>
						</div>
					</div>
				</div>
				</form>
			</div>		
		</div>
	</div>
</div>
<!--------------------------------------modals---------------------------------------------->

<!---------------------------------End MODALS------------------------------------->
<script>
$('#<?php echo $_SESSION['userid']; ?>Tickets').DataTable( {
	"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
	colReorder: true,
	dom: 'Bfrtip',
	stateSave: true,
	buttons: ['pageLength',
				{
					extend: 'colvis',
					title: 'Column Visibility',
					text:'Column Visibility',
					columns: [1, 2, 3, 4, 5, 6, 7, 8]  
				},{
					extend: 'excelHtml5',
					title: 'OpenRMM Asset List',
					text:'Export to excel',
					exportOptions: {
						columns: [1, 2, 3, 4, 5, 6, 7, 8]
					}
				},{
					extend: 'pdfHtml5',
					title: 'OpenRMM Asset List',
					text: 'Export to PDF',
					exportOptions: {
						columns: [1, 2, 3, 4, 5, 6, 7, 8]
					}
				}
	]
} );	
</script>
<script type='text/javascript'>
 $(document).ready(function(){
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
 
 
  $(".checkbox").click(function(){
 
    if($(".checkbox").length == $(".checkbox:checked").length) {
      $("#checkall").prop("checked", true);
    } else {
      $("#checkall").prop("checked", false);
    }

  });
});
</script>

