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
	$ticketID = (int)base64_decode($_GET['ID']);
	$query = "SELECT * FROM tickets WHERE ID='".$ticketID."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$ticket = mysqli_fetch_assoc($results);

	$query = "SELECT username,nicename,hex FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];

	$query = "SELECT username,nicename,hex FROM users WHERE ID='".$ticket['user_id']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user2 = mysqli_fetch_assoc($results);
	$name =  ucwords(crypto('decrypt',$user2['nicename'],$user2['hex'])); 

	$query = "SELECT username,nicename,hex,user_color FROM users WHERE ID='".$ticket['assignee']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user3 = mysqli_fetch_assoc($results);
	$name2 =  ucwords(crypto('decrypt',$user3['nicename'],$user3['hex']));

	

?>
<style>
	.grid-divider {
  overflow-x: hidden; //quickfix to hide divider on left side
  position: relative;
}


</style>
	<div class="casrd carsd-sm">
		<div style="overflow:visible;z-index:99999" class="row grid-divider">
		<div class="col-sm-12 col-md-6 col-lg-2 my-1">
			<div style="cursor:pointer" onclick="loadSection('ServiceDesk');" class="card secbtn">
			<div class="card-body"><i class="fas fa-arrow-left"></i> Ticket #TKT<?php echo $ticket['ID']; ?><br><br></div>
			</div>
		</div>
		<div class="col-sm-12 col-md-6 col-lg-2 my-1">
			<div class="card secbtn">
				<div data-toggle="dropdown" style="cursor:pointer" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown card-body"><b>Status</b><br>
					<span id="status" style="margin-left:10px"><?php echo $ticket['status']; ?></span>
					<div class="dropdown-menu">
						<ul style="font-size:12px;"  class="list-group">
							<li onclick="updateTicket('status','New','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">New</li>
							<li onclick="updateTicket('status','Closed','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Closed</li>
							<li onclick="updateTicket('status','In Progress','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">In Progress</li>
							<li onclick="updateTicket('status','On Hold','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">On Hold</li>
							<li onclick="updateTicket('status','Resolved','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Resolved</li>
							<li onclick="updateTicket('status','Awaiting Input','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Awaiting Input</li>
						</ul>
					</div>
				</div>	
			</div>
		</div>

		<div class="col-sm-12 col-md-6 col-lg-2 my-1">
			<div style="height:82px" class="card secbtn">
				<div data-toggle="dropdown" style="cursor:pointer" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown card-body"><b>Assignee</b><br>
				<span id="assignee" style="margin-left:10px">	
				<?php
					list($first, $last) = explode(' ', $name2, 2);
					$name = strtoupper("$first[0]{$last[0]}"); 
				?>
				<div style="font-size:9px;margin-left:10px;float:left;display:inline;background:<?php echo $user3['user_color']; ?>;color:#fff;padding:5px;border-radius:100px;text-align:center;width:25px;height:25px;padding-top:7px"><?php echo $name; ?></div>
				<?php echo textOnNull($name2,"Unassigned"); ?></span>
					<div class="dropdown-menu">
						<ul style="font-size:12px;"  class="list-group">
							<li onclick="updateTicket('assignee','Unassigned','<?php echo $ticketID; ?>','0');" style="cursor:pointer" class="list-group-item secbtn">Unassigned</li>
							<?php
								$query = "SELECT ID,hex,nicename,user_color FROM users WHERE active='1' ORDER BY ID ASC";
								$results = mysqli_query($db, $query);
								while($result = mysqli_fetch_assoc($results)){ 
									
									$name2 = textOnNull(ucwords(crypto('decrypt',$result['nicename'],$result['hex'])),"Unavailable"); 
									
									list($first, $last) = explode(' ', $name2, 2);
									$name = strtoupper("$first[0]{$last[0]}"); 
						
							?>
								<li onclick="updateTicket('assignee','<?php echo $name2; ?>','<?php echo $ticketID; ?>','<?php echo $result['ID']; ?>');" style="cursor:pointer" class="list-group-item secbtn">
								<div style="font-size:9px;margin-right:10px;float:left;display:inline;background:<?php echo $result['user_color']; ?>;color:#fff;padding:5px;border-radius:100px;text-align:center;width:25px;height:25px;padding-top:6px"><?php echo $name; ?></div>
								<?php echo $name2; ?></li>
							<?php $name=""; } ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12 col-md-6 col-lg-2 my-1">
			<div class="card secbtn">
			<div data-toggle="dropdown" role="button" style="cursor:pointer" aria-haspopup="true" aria-expanded="false" class="dropdown card-body"><b>Priority</b><br><span id="priority" style="margin-left:10px"><?php echo $ticket['priority']; ?></span>
					<div class="dropdown-menu">
						<ul style="font-size:12px;"  class="list-group">
							<li onclick="updateTicket('priority','None','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">None</li>
							<li onclick="updateTicket('priority','Low','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Low</li>
							<li onclick="updateTicket('priority','Medium','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Medium</li>
							<li onclick="updateTicket('priority','High','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">High</li>
							<li onclick="updateTicket('priority','Critical','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Critical</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-12 col-md-6 col-lg-2 my-1">
			<div class="card sewcbtn">
				<div class="card-body"><b>Created</b><br><span style="margin-left:10px"><?php echo ago($ticket['time']); ?></span></div>
			</div>
		</div>
		<div class="col-sm-12 col-md-6 col-lg-2 my-1">
			<div class="card secbtn">
				<div data-toggle="dropdown" style="cursor:pointer" role="button" aria-haspopup="true" aria-expanded="false" class="dropdown card-body"><b>Category</b><br><span id="category" style="margin-left:10px"><?php echo $ticket['category']; ?></span>
				<div class="dropdown-menu">
						<ul style="font-size:12px;"  class="list-group">
							<li onclick="updateTicket('category','Account Management','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Account Management</li>
							<li onclick="updateTicket('category','Applications','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Applications</li>
							<li onclick="updateTicket('category','Facilities','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Facilities</li>
							<li onclick="updateTicket('category','Finance','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Finance</li>
							<li onclick="updateTicket('category','General Inquiries','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">General Inquiries</li>
							<li onclick="updateTicket('category','Hardware','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Hardware</li>
							<li onclick="updateTicket('category','Human Resources','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Human Resources</li>
							<li onclick="updateTicket('category','Networking','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Networking</li>
							<li onclick="updateTicket('category','Other','<?php echo $ticketID; ?>');" style="cursor:pointer" class="list-group-item secbtn">Other</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>	
	<div class="row" style="z-index:1;margin-bottom:10px;margin-top:0px;border-radius:3px;ovesrflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="z-index:1;padding-bottom:20px;padding-top:0px;">
			<form method="post" action="/">
				<div class="card table-card" id="printTable" style="z-index:1;marsgin-top:-20px;padding:30px;border-radius:6px;"> 
					<h4 style="display:inline"><?php echo $ticket['title']; ?>
						<div styles="float:right;display:inline" class="dropdown">
							<a href="javascript:void(0)" title="Refresh" onclick="loadSection('Ticket');" class="btn btn-sm" style="float:right;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
								<i class="fas fa-sync"></i>
							</a>
							<button type="button" class="btn btn-dark dropsdown-toggle btn-sm" style="float:right;margin-right:5px" data-toggle="dropdown">
								Actions <i class="fas fa-sort-down"></i>
							</button>
							<div class="dropdown-menu">
								<a class="dropdown-item" data-toggle="modal" href="javascript:void(0)" data-target="#companyComputersModal2" >Clone</a>
								<a class="dropdown-item" data-toggle="modal" href="javascript:void(0)" data-target="#companyComputersModal2" >Edit</a>
								<a class="dropdown-item" data-toggle="modal" href="javascript:void(0)" data-target="#companyComputersModal2" >Merge</a>
								<hr>
								<a class="dropdown-item bg-danger" data-toggle="modal" href="javascript:void(0)" data-target="#deleteAssets" >Delete</a>
							</div>
							
						</div>	
						<span style="color:#707070;font-size:12px">Created: <?php echo date("m/d/Y h:i A", strtotime($ticket['time'])); ?> by <?php echo $name; ?></span><br><br>
						<div style="margin-left:20px">
							<span style="font-size:14px;"><?php echo $ticket['description']; ?></span>
							<br>
							<div style="margin-top:20px">
							<?php
								$tags = explode(",",$ticket['tags']);
								foreach ($tags as $value) {
									if($value==""){continue;}
							?>								
								<span style="font-size:12px" class="badge badge-primary"><?php echo $value; ?></span>
							<?php } ?>
							</div>
						</div>

						
					</h4>
			<hr>
			<br>
			</form>
			<div class="tab-block">
					<ul class="nav nav-pills">
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center;" class="nav-item">
							<a data-toggle="pill" class="nav-link active" data-toggle="tab" href="#home">Comments</a>
						</li>
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-toggle="pill" class="nav-link"  data-toggle="tab" href="#menu1">Details</a>
						</li>
						<li style="padding:5px;padding-bottom:10px;border-radius:3px;margin-left:5px;width:120px;text-align:center" class="nav-item">
							<a data-toggle="pill" class="nav-link"  data-toggle="tab" href="#menu2">Tasks</a>
						</li>
					</ul>
				</div>
				<?php
					list($first, $last) = explode(' ', crypto('decrypt',$user['nicename'],$user['hex']), 2);
					$name = strtoupper("$first[0]{$last[0]}"); 
				?>
				<div class="tab-content" style="padding-top:10px;overflow:hidden" >
					<div id="home" class="tab-pane fade-in active">
						<div style="margin-right:20px;float:left;display:inline;background:#fe6f33;color:#fff;padding:5px;border-radius:100px;text-align:center;width:40px;height:40px;padding-top:10px"><?php echo $name; ?></div>
						<div style="width:90%;margin-left:50px;">
							<textarea id="trumbowyg-demo" name="message" style="height:200px" ></textarea>
							<div id="msg_private" style="border:1px solid #d7e0e2;border-top:none;background:#ecf0f1;height:35px;padding:8px;cursor:pointer"><i class="fas fa-lock"></i>&nbsp;Private</div>
							<div id="msg_public" style="border:1px solid #d7e0e2;border-top:none;background:#ecf0f1;height:35px;padding:8px;cursor:pointer;display:none"><i class="fas fa-lock-open"></i>&nbsp;Public</div>
							<input type="hidden"name="ID" value="<?php echo $ticketID; ?>">
							<input type="hidden" id="message_type" name="messageType" value="private">
							<button class="btn btn-sm btn-success" style="float:right;width:100px;margin-top:3px" type="submit">Post <i class="fas fa-paper-plane"></i></button>
							<script>
								$('#trumbowyg-demo').trumbowyg({
									btns: [
										['undo', 'redo'], 
										['formatting'],
										['strong', 'em', 'del'],
										['superscript', 'subscript'],
										['link'],
										['insertImage'],
										['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
										['unorderedList', 'orderedList'],
										['horizontalRule'],
										['removeformat'],
										['fullscreen']
									]
								});
								$("#msg_private").click(function(){
									$("#msg_public").toggle();
									$("#msg_private").toggle();
									$("#message_type").val("public");
								});
								$("#msg_public").click(function(){
									$("#msg_private").toggle();
									$("#msg_public").toggle();
									$("#message_type").val("private");
								});
							</script>
						</div>
						<br><br>
						<?php
						$query = "SELECT * FROM ticket_messages where ticket_id='".$ticketID."' ORDER BY ID DESC";
						$results = mysqli_query($db, $query);
						$resultCount = mysqli_num_rows($results);							
						
						//Fetch Results
						$count = 0;
						
						while($result = mysqli_fetch_assoc($results)){
						
							$count++;
							$query2 = "SELECT username,nicename,hex,user_color FROM users WHERE ID='".$result['user_id']."' LIMIT 1";
							$results2 = mysqli_query($db, $query2);
							$user3 = mysqli_fetch_assoc($results2);
							$name2 =  ucwords(crypto('decrypt',$user3['nicename'],$user3['hex']));
							
							list($first, $last) = explode(' ', crypto('decrypt',$user3['nicename'],$user3['hex']), 2);
							$name = strtoupper("$first[0]{$last[0]}"); 
						?>
							<div style="margin-right:20px;float:left;display:inline;background:<?php echo $user3['user_color']; ?>;color:#fff;padding:5px;border-radius:100px;text-align:center;width:40px;height:40px;padding-top:10px">
								<?php echo $name; ?>
							</div>
								<?php if($result['type']=="private"){ ?>
									<i title="Private Message" class="fas fa-lock"></i>&nbsp;&nbsp;
								<?php } ?>	
								<b>
									<?php echo $name2; ?>
									<span style="font-size:18px">&bullet;</span>
								</b> 
								<?php echo date("m/d/Y h:i A", strtotime($result['time'])); ?>
								<br>
								<?php echo clean($result['message']); ?>
								<br><br>
								
						<?php } ?>
					</div>
					<div id="menu1" class="tab-pane fade-in">
						<ul class="list-group">
							<li class="list-group-item">Assigned to: <?php echo $name2; ?></li>							
							<li class="list-group-item">Priority: <?php echo $ticket['priority']; ?></li>
							<li class="list-group-item">Requester: <?php echo ucwords($ticket['requester']); ?></li>
							<li class="list-group-item">Due at: <?php echo $ticket['due']; ?></li>
							<li class="list-group-item">CC: <?php echo $ticket['cc']; ?></li>
							<li class="list-group-item"><?php echo $msp; ?>: <?php echo $ticket['company_id']; ?></li>
							<li class="list-group-item">Category: <?php echo $ticket['category']; ?></li>
							<li class="list-group-item">Tags: <?php echo $ticket['tags']; ?></li>
						</ul>
					</div>
					<div id="menu2" class="tab-pane fade-in">
						<button data-toggle="modal" data-target="#editTrigger" type="button" class="btn btn-sm btn-warning"><i class="fas fa-plus"></i> &nbsp;Add Task</button><hr>
						<table class="table table-hover table-borderless" id="datatable">
							<tr>
								<th>Task Name</th>
								<th>Date Created</td>
								<th style="float:right"></th>
							</tr>
							<?php
							$count=0;
							$query = "SELECT * FROM tasks WHERE active='1' ORDER BY ID ASC";
							$results = mysqli_query($db, $query);
							while($task = mysqli_fetch_assoc($results)){
								$count++;
								if($task['last_run']!=""){ $last_run=$task['last_run'];}else{ $last_run="Never";}
							?>
							<tr id="task<?php echo $task['ID']; ?>">
								<td><?php echo $task['name']; ?></td>
								<td><?php echo $last_run; ?></td>
								<td style="float:right">
									<form action="/" method="post" style="display:inline;">
										<input type="hidden" value="<?php echo $task['ID'];?>" name="ID">
										<input type="hidden" value="startTask" name="type">
										<button type="submit" title="Run Task Now" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-primary btn-sm">
											<i class="fas fa-play"></i>
										</button>
									</form>
									<button type="button" onclick="deleteTask('<?php echo $task['ID']; ?>')" title="Delete Task" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
										<i class="fas fa-trash"></i>
									</button>
								</td>
							</tr>
							<?php } 
							if($count == 0){ ?>
								<tr>
									<td colspan=4><center><h6>Once you create a Task, it will show up here.</h6></center></td>
								</tr>
						<?php }?>
						</table>
					</div>
				</div>
			</div>	
		</div>	
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="padding-left:20px;">
			<div style="margin-top:20px"  class="panel panel-default">
					<div class="panel-heading">
						<h5 class="panel-title">
							Remote Management
						</h5>
					</div>
					<div class="panel-body">
						<ul class="list-group">
							<?php
								$json = getComputerData($ticket['computer_id'], array("*"), $showDate);
								$hostname = textOnNull($json['General']['Response'][0]['csname'],"Unavailable");
								$query = "SELECT online, ID, company_id, name, phone, email,hex, computer_type FROM computers WHERE ID='".$ticket['computer_id']."' LIMIT 1";
								$results = mysqli_query($db, $query);
								$result = mysqli_fetch_assoc($results);
							?>
							<li onclick="loadSection('General', '<?php echo $result['ID']; ?>');$('.sidebarComputerName').text('<?php echo textOnNull(strtoupper($hostname),'Unavailable');?>');" class="list-group-item secbtn" style="text-align:left;cursor:pointer;">
								<?php if($result['online']=="0") {?>
									<i class="fas fa-<?php echo $icon;?>" style="color:#666;font-size:12px;" title="Offline"></i>
								<?php }else{?>
									<i class="fas fa-desktop" style="color:green;font-size:12px;" title="Online"></i>
								<?php }?>
								&nbsp;&nbsp;<?php echo $hostname; ?>
							</li>
						</ul>
					</div>
				</div>
			<div style="margin-top:20px"  class="panel panel-default">
					<div class="panel-heading">
						<h5 class="panel-title">
							Requester's Information
						</h5>
					</div>
					<div class="panel-body">
						<ul class="list-group">
							<li class="list-group-item"><b>Name:</b>
								<a href="javascript:void(0)" onclick="searchItem('<?php echo textOnNull(crypto('decrypt',$company['name'],$company['hex']),"N/A"); ?>');" title="Search Company">
									<?php echo textOnNull(ucwords($ticket['requester']),"N/A"); ?>
								</a>
							</li>
							<li class="list-group-item"><b>Email:</b>
								<a href="mailto:<?php echo crypto('decrypt',$company['email'],$company['hex']); ?>">
									<?php echo textOnNull(ucfirst(crypto('decrypt',$company['email'],$company['hex'])),"N/A"); ?>
								</a>
							</li>
							<li class="list-group-item"><b>Phone:</b> <?php echo textOnNull(phone(crypto('decrypt',$company['phone'],$company['hex'])),"N/A"); ?></li>
							<li class="list-group-item"><b><?php echo $msp; ?>:</b> <?php echo textOnNull(ucfirst(crypto('decrypt',$company['comments'],$company['hex'])),"None"); ?></li>
						</ul>
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
					<button data-toggle="modal" data-target="#noteModal" style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none" title="Create New Note" class="btn btn-warning btn-block p-t-15 p-b-15">Create New Note</button>
				</div>	
			</div>
		</div>
	</div>
</div>
<!--------------------------------------modals---------------------------------------------->

<!---------------------------------End MODALS------------------------------------->
<script>
$('#dataTable').DataTable( {
	"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
	colReorder: true,
	dom: 'Bfrtip'


} );	

</script>
<script src="js/tagsinput.js"></script>
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
