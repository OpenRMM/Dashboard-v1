<?php 
$userID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page']);

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

if($userID!=$_SESSION['userid']){
	$activity="The profile of ".ucwords(crypto('decrypt',$user['nicename'],$user['hex']))." was viewed";
	userActivity($activity, $_SESSION['userid']);
	$hideTFA="true";
}else{
	$hideTFA="false";
}
$settings1 =  explode(",",crypto("decrypt",$user['allowed_pages'],$user['hex'])); 
$settings = "\'".implode("\', \'",$settings1)."\'";	
?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h5 style="color:#0c5460">Technician Profile
		<button title="Refresh" onclick="loadSection('Profile');" class="btn btn-sm" style="float:right;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</button>
	</h5>
</div>
<div>
	<section id="content">
	
		<div class="row">
			<div class="col-md-9">
				<div style="margin-top:0px;background:#343a40;padding:10px;color:#fff;border-radius:5px;margin-bottom:30px;width:100%" class="">
					<div class="media clearfix">
						<div class="media-left pr30">
							<?php
								list($first, $last) = explode(' ', ucwords(crypto('decrypt',$user['nicename'],$user['hex'])), 2);
								$name = strtoupper("$first[0]{$last[0]}"); 
							?>
							<div style="margin-top:0px;font-size:36px;margin-right:10px;float:left;display:inline;background:<?php echo $user['user_color']; ?>;color:#fff;padding:5px;border-radius:100px;text-align:center;width:100px;height:100px;padding-top:24px">
								<?php echo $name; ?>
							</div>
						</div>                      
						<div style="margin-top:0px;;width:100%" class="media-body va-m">
							<h4 title="User ID: <?php echo $user['ID']; ?>" style="color:#fff;cursor:default;margin-top:10px" class="media-heading"><?php echo ucwords(crypto('decrypt',$user['nicename'],$user['hex'])); ?> 					
								<p style="font-size:14px" ><small>Last Seen: <?php if($user['last_login']==""){ echo "never"; }else{ echo ago(date('m/d/Y H:i:s',$user['last_login'])); } ?></small></p>
								<div style="float:right;margin-top:-40px">
									<div class="btn-group">
										<button  data-bs-toggle="modal" data-bs-target="#userModal" onclick="$('#TFAMSG').hide();$('#TFA').show();editUser('<?php echo $user['ID'];?>','<?php echo $user['username'];?>','<?php echo crypto('decrypt',$user['nicename'],$user['hex']);?>','<?php echo crypto('decrypt', $user['email'], $user['hex']); ?>','<?php echo crypto('decrypt', $user['phone'], $user['hex']); ?>','<?php echo crypto('decrypt',$user['account_type'],$user['hex']);?>','<?php echo $user['user_color']; ?>','<?php echo $settings; ?>')" title="Edit User" type="button" class="btn btn-primary btn-sm"><i class="fas fa-pencil-alt"></i> Edit</button>
										<button type="button"  class="btn btn-primary dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="fas fa-sort-down"></i>
										</button>
										<div style="padding:0px" class="dropdown-menu">
											<?php if($_SESSION['accountType']=="Admin"){  ?>
												<?php if($user['active']=="1"){ ?>					
													<a id="userDel<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','0')" <?php if($user['ID']=="1") echo "disabled"; ?> title="Deactivate User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="dropdown-item bg-danger">
														<i class="fas fa-trash" ></i> Deactivate			
													</a>
													<a id="userAct<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','1')" title="Activate User" style="display:none;margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="bg-success dropdown-item">
														<i class="fas fa-plus" ></i> Activate
													</a>
												<?php }else{ ?>
													<a id="userAct<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','1')" title="Activate User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="bg-success dropdown-item">
														<i class="fas fa-plus" ></i> Activate
													</a>
													<a id="userDel<?php echo $user['ID']; ?>" onclick="deleteUserProfile('<?php echo $user['ID']; ?>','0')" <?php if($user['ID']=="1") echo "disabled"; ?> title="Deactivate User" style="display:none;margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="dropdown-item bg-danger">
														<i class="fas fa-trash" ></i> Deactivate			
													</a>
												<?php } ?>
											<?php } ?>
										</div>
									</div>
								</div>
							</h4>	
							<?php if($_SESSION['accountType']=="Admin"){ ?>
								<span style="font-size:12px">User ID: <?php echo $user['ID']; ?></span>
							<?php } ?>		
						</div>
					</div>
				</div>
				<?php if($user['active']=="0"){ ?>	
					<div  style="border-radius: 0px 0px 4px 4px;" class="alert alert-danger" role="alert">
						<i class="fas fa-ban"></i>&nbsp;&nbsp;&nbsp;This technician has been deactivated		
					</div>
				<?php } ?>
				<div class="tab-block">
						<ul style="margin-left:1px" class="nav nav-tabs">
							<li class="active">
								<a style="text-decoration:none" href="#tab1" data-bs-toggle="tab">Activity</a>
							</li>
							<?php if($_SESSION['accountType']=="Admin"){  ?>
								<li onclick="deleteActivity()" class="active bg-danger text-white" style="cursor:pointer" >    
									<input type="hidden" id="delActivity" name="delActivity" value="<?php echo $userID; ?>">        
									<a href="javascript:void(0)" style="background:#dedede;color:#696969;cursor:pointer;text-decoration:none" data-bs-toggle="tab">Clear Activity</a>
								</li>
							<?php } ?>
						</ul>
					<div class="tab-content p30"  style="border-radius:0px 6px 6px 6px;margin-top:11px;">
						<div id="tab1" class="tab-pane active">
							<div style="padding:0px;">
								<table id="<?php echo $_SESSION['userid']; ?>Profile" style="width:100%;overflow:hidden;font-size:14px;margin-top:0px;font-family:Arial;" class="table-striped table table-hover table-borderless">
									<thead>
										<tr style="border-bottom:2px solid #d3d3d3;">
										<th scope="col">#</th>
										<th scope="col">Event</th>
										<th scope="col">Time</th>			  
										</tr>
									</thead>
									<tbody id="activity">			
									<?php
										//Fetch Results
										$count=0;
										$query = "SELECT * FROM user_activity WHERE user_id='".$userID."' and active='1' ORDER BY ID DESC";
										$results = mysqli_query($db, $query);
										$userCount = mysqli_num_rows($results);
										while($activity = mysqli_fetch_assoc($results)){
											if(crypto('decrypt',$activity['activity'],$activity['hex']) ==""){
												continue;
											}
											$count++;																
										?>
											<tr>
												<td><?php echo $count; ?></td>
												<td><?php echo crypto('decrypt',$activity['activity'],$activity['hex']); ?></td>
												<td><?php echo date("m/d/y\ h:i",$activity['date']); ?></td>					
											</tr>
											<?php }
											if($count==0){ echo "<td>No recent activity.</td>"; } 
											?>				
									</tbody>
								</table>
							</div>	
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3">  
				<div class="panel">
					<div class="panel-heading">
					<span class="panel-title">Contact Information</span>
					</div>
					<div class="panel-body pb5">              
					<ul class="list-group">
						<li class="list-group-item">
							<a style="text-decoration:none" href="mailto:<?php echo crypto('decrypt', $user['email'], $user['hex']); ?>"><?php echo textOnNull(crypto('decrypt', $user['email'], $user['hex']),"No Phone Number"); ?></a>
						</li>
						<li class="list-group-item">
							<a style="text-decoration:none" href="tel:<?php echo crypto('decrypt', $user['phone'], $user['hex']); ?>"><?php echo textOnNull(phone(crypto('decrypt', $user['phone'], $user['hex'])),"No Phone"); ?></a>
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
						<a title="View Note" class="noteList" onclick="$('#notetitle').text('<?php echo $note[0]; ?>');$('#notedesc').text('<?php echo $note[1]; ?>');" data-bs-toggle="modal" data-bs-target="#viewNoteModal" style="text-decoration:none">
							<li style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item">
								<i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>
								<?php echo ucwords($note[0]);?>
							</li>
						</a>
					<?php } } ?>
					<?php if($count==0){ ?>
						<li class="no_noteList list-group-item">No Notes</li>
					<?php }else{ ?>
					<li class="no_noteList list-group-item" style="display:none" >No Notes</li>
					<?php } ?>
				</div>
				<?php if($userID==$_SESSION['userid']){ ?>
				<button style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none;color:#fff" data-bs-toggle="modal" data-bs-target="#noteModal"  title="Create New Note" class="btn btn-sm">Create New Note</button>
				<?php } ?>
			</div>
			<?php } ?>
		</div>	
	</div>
</section>
</div>
<script>
$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Profile').dataTable( {
			colReorder: true,
			stateSave: true,
			order: [0, 'asc']
		} );
    });
</script>
<script>

function editUser(ID, username, name, email, phone, type, color, allowed_pages){
	$('select>option:eq(0)').prop('selected', true);
	if(type=="Standard"){
		$("#allowed_pages").slideDown();
	}else{
		$("#allowed_pages").slideUp();
	}
	$('.settingsCheckbox').prop('checked',false);
	$("#editUserModal_ID").val(ID);
	$('#disableTFA2').attr('onClick', 'disableTFA("' + ID + '");');
	$("#editUserModal_username").val(username);
	$("#editUserModal_name").val(name);
	$("#editUserModal_email").val(email);
	$("#editUserModal_color").val(color);
	$("#editUserModal_phone").val(phone);
	$("#editUserModal_type").val(type.toLowerCase());
	$("#editUserModal_type").text(type)
	$("#editUserModal_password").prop('type', 'password').val("");
	$("#editUserModal_password2").prop('type', 'password').val("");
	var setting = allowed_pages.split(",");
	function iterate(item) {
		item = item.replace(/[^a-zA-Z0-9]/g,'')
		$('#'+ item).prop('checked', true);
	}
	setting.forEach(iterate);
	update();
}
</script>
<?php 
$userID = "";
$_GET['ID'] ="";
?>