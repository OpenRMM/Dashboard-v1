<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page']);

$query = "SELECT ID,username,last_login,active,email,nicename,hex,phone,account_type,user_color FROM users ORDER BY nicename ASC";
$results = mysqli_query($db, $query);
$userCount = mysqli_num_rows($results);
?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h5 style="color:#0c5460">All Technicians (<?php echo $userCount;?>)
		<button href="javascript:void(0)" title="Refresh" onclick="loadSection('Technicians');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</button>
		<button type="button" style="margin:5px;background:#0ac282;;float:right;color:#fff" data-toggle="modal" data-target="#userModal" class="btn-sm btn btn-light" title="Add User">
			 <i class="fas fa-plus"></i> Add Technician
		</button>
	</h5>	
</div>
	<div class="card table-card">
	   <div class="card-header">
			<h5>Listing All Current Technicians</h5>
			<div class="card-header-right">
				<ul class="list-unstyled card-option">
					<li>
						<i class="feather icon-maximize full-card"></i>
					</li>
					<li>
						<i class="feather icon-minus minimize-card"></i>
					</li>
					<li>
						<i class="feather icon-trash-2 close-card"></i>
					</li>
				</ul>
			</div>
		</div>
	<div style="padding:10px;overflow-x:auto">	
		<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover table-borderless">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
					<th scope="col">ID</th>
					<th scope="col">Name</th>
					<th scope="col">Email</th>
					<th scope="col">Phone</th>
					<th scope="col">Username</th>
					<th scope="col">Last Seen</th>
					<th scope="col">Account Type</th>
					<th scope="col">Status</th>
					<th scope="col"></th>
				</tr>
		  </thead>
		  <tbody>
			<?php
				//Fetch Results
				while($user = mysqli_fetch_assoc($results)){
					$count++;
					if($user['active']=="1"){
						$status="Active";
					}else{
						$status="Inactive";
					}						
				?>
				<tr>
					<td><?php echo $user['ID'];?></td>
					<td>
						<a style="font-size:12px" href="javascript:void(0)" onclick="loadSection('Profile','<?php echo $user['ID']; ?>');">
						<?php
							list($first, $last) = explode(' ', ucwords(crypto('decrypt',$user['nicename'],$user['hex'])), 2);
							$name = strtoupper("$first[0]{$last[0]}"); 
						?>
						<div style="font-size:12px;margin-right:10px;float:left;display:inline;background:<?php echo $user['user_color']; ?>;color:#fff;padding:5px;border-radius:100px;text-align:center;width:30px;height:30px;padding-top:5px">
							<?php echo $name; ?>
						</div>
							<?php echo ucwords(crypto('decrypt',$user['nicename'],$user['hex']));?>
						</a>
					</td>
					<td><a style="font-size:12px" href="mailto:<?php echo strtolower(crypto('decrypt', $user['email'], $user['hex']));?>"><?php echo textOnNull(strtolower(crypto('decrypt', $user['email'], $user['hex'])),"No Email");?></a></td>
					<td><a style="font-size:12px" href="tel:<?php echo strtolower(crypto('decrypt', $user['phone'], $user['hex']));?>"><?php echo textOnNull(phone(crypto('decrypt', $user['phone'], $user['hex'])),"No Phone");?></a></td>
					<td><?php echo strtolower($user['username']);?></td>
					<td><?php echo textOnNull(date("m/d/Y\ h:i A", $user['last_login']),"Never");?></td>
					<td><?php echo ucwords(crypto('decrypt',$user['account_type'],$user['hex']));?></td>
					<td><?php echo $status;?></td>
					<td>
						<form>
							<?php if($user['active']=="1"){ ?>
								<button onclick="deleteUser('<?php echo $user['ID']; ?>','0')" id="delUser<?php echo $user['ID']; ?>" <?php if($user['ID']=="1") echo "disabled"; ?> type="button" title="Deactivate User" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
									<i class="fas fa-trash" ></i>				
								</button>
								<button id="actUser<?php echo $user['ID']; ?>" onclick="deleteUser('<?php echo $user['ID']; ?>','1')" type="button" title="Activate User" style="display:none;margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-success btn-sm">
									<i class="fas fa-plus" ></i>
								</button>
							<?php }else{ ?>
								<button id="actUser<?php echo $user['ID']; ?>" onclick="deleteUser('<?php echo $user['ID']; ?>','1')" type="button" title="Activate User" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-success btn-sm">
									<i class="fas fa-plus" ></i>
								</button>
								<button id="delUser<?php echo $user['ID']; ?>" onclick="deleteUser('<?php echo $user['ID']; ?>','0')" <?php if($user['ID']=="1") echo "disabled"; ?> type="button" title="Deactivate User" style="display:none;margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-danger btn-sm">
									<i class="fas fa-trash" ></i>				
								</button>
							<?php } ?>
							<a href="javascript:void(0)" data-toggle="modal" data-target="#userModal" onclick="editUser('<?php echo $user['ID'];?>','<?php echo $user['username'];?>','<?php echo crypto('decrypt',$user['nicename'],$user['hex']);?>','<?php echo crypto('decrypt', $user['email'], $user['hex']); ?>','<?php echo crypto('decrypt', $user['phone'], $user['hex']); ?>','<?php echo crypto('decrypt',$user['account_type'],$user['hex']);?>','<?php echo $user['user_color']; ?>')" title="Edit User" style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" class="btn btn-dark btn-sm">
								<i class="fas fa-pencil-alt"></i>
							</a>
						</form>
					</td>
				</tr>
			<?php }?>
		    </tbody>
		</table>
	</div>	
</div>
<script>
	//Edit User
	function editUser(ID, username, name, email, phone, type, color){
		$("#editUserModal_ID").val(ID);
		$("#editUserModal_username").val(username);
		$("#editUserModal_name").val(name);
		$("#editUserModal_email").val(email);
		$("#editUserModal_color").val(color);
		$("#editUserModal_phone").val(phone);
		$("#editUserModal_type").val(type.toLowerCase());
		$("#editUserModal_type").text(type)
		$("#editUserModal_password").prop('type', 'password').val("");
		$("#editUserModal_password2").prop('type', 'password').val("");
	}
</script>
<script>
$(document).ready(function() {
    $('#dataTable').dataTable( {
		colReorder: true
	} );
});
</script>