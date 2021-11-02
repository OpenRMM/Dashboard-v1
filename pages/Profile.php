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
$userID = (int)$_GET['ID'];
if($userID==0 or $userID==""){
		$userID=$_SESSION['userid'];
}
$query = "SELECT * FROM users WHERE ID='".$userID."' LIMIT 1";
$results = mysqli_query($db, $query);
$user = mysqli_fetch_assoc($results);

if($user['ID']==""){ echo "No user found";  exit;}
if($_SESSION['accountType']=="Standard" & $userID!=$_SESSION['userid']){
	$activity="Technician Attempted Access To: ".basename($_SERVER['SCRIPT_NAME'])." ID: ".$userID;
	userActivity($activity,$_SESSION['userid']);
	exit("<center><br><br><h4>Sorry, You Do Not Have Permission To Access This Page!</h4><p>If you believe this is an error please contact a site administrator.</p><hr><a href='#' onclick='loadSection(\"Dashboard\");' class='btn btn-warning btn-sm'>Back To Dashboard</a></center><div style='height:100vh'>&nbsp;</div>");	
}
//echo $_SESSION['accountType'];
$userActivity2 = explode("|",crypto('decrypt',$user['user_activity'],$user['hex']));
$userActivity = array_reverse($userActivity2,true);
if($userID!=$_SESSION['userid']){
	$activity="Technician Viewed The Profile Of ".ucwords(crypto('decrypt',$user['nicename'],$user['hex']))."'";
	userActivity($activity, $_SESSION['userid']);
}
?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h4 style="color:#333">Technician Profile
		<a href="javascript:void(0)" title="Refresh" onclick="loadSection('Profile');" class="btn btn-sm" style="float:right;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</a>
	</h4>
</div>
<div id="printTable"style="padding:15px" >
	<section id="content">
		<div style="margin-top:-20px;background:#35384e;padding:20px;color:#fff;border-radius:6px;margin-bottom:30px" class="page-heading">
			<div class="media clearfix">
				<div class="media-left pr30">
					<a style="color:#fff" href="javascript:void(0)">
					<i style="font-size:100px;text-align:center" class="fa fa-user" ></i>
					</a>
				</div>                      
				<div style="margin-top:20px" class="media-body va-m">
					<h4 style="color:#fff" class="media-heading"><?php echo ucwords(crypto('decrypt',$user['nicename'],$user['hex'])); ?> 
					<span style="float:right;font-size:12px">User ID: <?php echo $user['ID']; ?></span>
					<span style="font-size:14px" ><small>Last Seen: <?php if($user['last_login']==""){ echo "never"; }else{ echo ago(date('m/d/Y H:i:s',$user['last_login'])); } ?></small></span>
					</h4>
					<p style="color:#dedede">View All Assets Added By This User. You Can Also Access Contact Information And Recent Activity. </p>
					<hr>
					<?php if($_SESSION['accountType']=="Admin"){  ?>
						<?php if($user['active']=="1"){ ?>
							
							<button id="userDel<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','0')" <?php if($user['ID']=="1") echo "disabled"; ?> type="button" title="Deactivate User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
								<i class="fas fa-trash" ></i> Deactivate			
							</button>
							<button type="button" id="userAct<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','1')" title="Activate User" style="display:none;margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-success btn-sm">
								<i class="fas fa-plus" ></i> Activate
							</button>
						<?php }else{ ?>
							<button type="button" id="userAct<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','1')" title="Activate User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-success btn-sm">
								<i class="fas fa-plus" ></i> Activate
							</button>
							<button id="userDel<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','0')" <?php if($user['ID']=="1") echo "disabled"; ?> type="button" title="Deactivate User" style="display:none;margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
								<i class="fas fa-trash" ></i> Deactivate			
							</button>
						<?php } ?>
					<?php } ?>
					<a href="javascript:void(0)" data-toggle="modal" data-target="#userModal" onclick="editUser('<?php echo $user['ID'];?>','<?php echo $user['username'];?>','<?php echo crypto('decrypt',$user['nicename'],$user['hex']);?>','<?php echo crypto('decrypt', $user['email'], $user['hex']); ?>','<?php echo crypto('decrypt', $user['phone'], $user['hex']); ?>','<?php echo crypto('decrypt',$user['account_type'],$user['hex']);?>')" title="Edit User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-primary btn-sm">
						<i class="fas fa-pencil-alt"></i> Edit
					</a>
					
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-8">
				<div class="tab-block">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#tab1" data-toggle="tab">Activity</a>
							</li>
							<?php if($_SESSION['accountType']=="Admin"){  ?>
								<li>    
									<input type="hidden" id="delActivity" name="delActivity" value="<?php echo $userID; ?>">        
									<button style="display:inline;margin-top:-10px;border:none;box-shadow:none" onclick="deleteActivity()" class="btn btn-sm" type="button" >Clear Activity</button>
								</li>
							<?php } ?>
						</ul>
					<div class="tab-content p30"  style="border-radius:6px;margin-top:10px;">
						<div id="tab1" class="tab-pane active">
							<div style="padding:0px;">
								<table id="dataTable" style="width:100%;line-height:10px;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table table-hover  table-borderless">
									<thead>
										<tr style="border-bottom:2px solid #d3d3d3;">
										<th scope="col">#</th>
										<th scope="col">Event</th>
										<th scope="col">Time</th>			  
										</tr>
									</thead>
									<tbody id="activity">			
											<?php 
											$count=0;
											foreach($userActivity as $key=>$item) {
												$count++;
												$part = explode("@",$item);
											?>
											<tr>
												<td><?php echo $count; ?></td>
												<td><?php echo $part[0]; ?></td>
												<td><?php echo gmdate("m/d/y\ h:i",$part[1]); ?></td>					
											</tr>
											<?php }
											if($count==0){ echo "<td>No Recent Activity</td>"; } 
											?>				
									</tbody>
								</table>
							</div>	
						</div>
						<div id="tab2" class="tab-pane"></div>
						<div id="tab3" class="tab-pane"></div>
						<div id="tab4" class="tab-pane"></div>
					</div>
				</div>
			</div>
			<div class="col-md-4">  
				<div class="panel">
					<div class="panel-heading">
					<span class="panel-title">Contact Information</span>
					</div>
					<div class="panel-body pb5">              
					<ul class="list-group">
						<li class="list-group-item">
							<a href="mailto:<?php echo crypto('decrypt', $user['email'], $user['hex']); ?>"><?php echo textOnNull(crypto('decrypt', $user['email'], $user['hex']),"No Phone Number"); ?></a>
						</li>
						<li class="list-group-item">
							<a href="tel:<?php echo crypto('decrypt', $user['phone'], $user['hex']); ?>"><?php echo textOnNull(phone(crypto('decrypt', $user['phone'], $user['hex'])),"No Phone"); ?></a>
						</li>
					</ul>
					</div>
				</div>
				<?php if($_SESSION['accountType']=="Admin" or $user['ID']==$_SESSION['userid']){  ?>
				<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
					<div style="height:45px" class="panel-heading">
						<h5 class="panel-title">Notes
						<?php if($userID==$_SESSION['userid']){ ?>
							<button id="delNote" type="button" onclick="deleteNote('1');" class="delNote btn btn-danger btn-sm" style="float:right;padding:5px;"><i class="fas fa-trash"></i>&nbsp;&nbsp;&nbsp;Clear All</button>
						<?php } ?>
						</h5>
					</div>
					<div id="TextBoxesGroup" class="card-block texst-center">
					<?php
					$count = 0;
					$query = "SELECT ID, notes,hex FROM users where ID='".$userID."'";
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
						<li class="list-group-item">No Notes</li>
					<?php }else{ ?>
					<li class="no_noteList list-group-item" style="display:none" >No Notes</li>
					<?php } ?>
				</div>
				<?php if($userID==$_SESSION['userid']){ ?>
				<button style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none;color:#fff" data-toggle="modal" data-target="#noteModal"  title="Create New Note" class="btn btn-block p-t-15 p-b-15">Create New Note</button>
				<?php } ?>
			</div>
			<?php } ?>
		</div>	
	</div>
</section>
</div>
<script>
$(document).ready(function() {
		var table = $('#dataTable').DataTable();

// Sort by column 1 and then re-draw
table
.order( [ 0, 'asc' ] )
.draw();
		
});
</script>
<script>
//Edit User
function editUser(ID, username, name, email, phone, type){
	$("#editUserModal_ID").val(ID);
	$("#editUserModal_username").val(username);
	$("#editUserModal_name").val(name);
	$("#editUserModal_email").val(email);
	$("#editUserModal_phone").val(phone);
	$("#editUserModal_type").val(type.toLowerCase());
	$("#editUserModal_type").text(type)
	$("#editUserModal_password").prop('type', 'password').val("");
	$("#editUserModal_password2").prop('type', 'password').val("");
}
</script>
<?php 
$userID = "";
$_GET['ID'] ="";
?>