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
$query = "SELECT ID,username,last_login,active,email,nicename,hex,phone,accountType FROM users ORDER BY nicename ASC";
$results = mysqli_query($db, $query);
$userCount = mysqli_num_rows($results);
?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">All Technicians (<?php echo $userCount;?>)
		<a href="#" title="Refresh" onclick="loadSection('AllUsers');" class="btn btn-sm" style="float:right;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<button type="button" style="margin:5px;background:#0ac282;;float:right;color:#fff" data-toggle="modal" data-target="#userModal" class="btn-sm btn btn-light" title="Add User">
			 <i class="fas fa-plus"></i> Add Technician
		</button>
	</h4>	
</div>
	<div class="card table-card" id="printTable" >
	   <div class="card-header">
			<h5>Listing All Current Technicians</h5>
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
			<col width="50">
			<col width="200">
			<col width="250">
			<col width="100">
			<col width="200">
			<col width="80">
			<col width="100">
			<thead>
				<tr style="border-bottom:2px solid #d3d3d3;">
					<th scope="col">User ID</th>
					<th scope="col">Name</th>
					<th scope="col">Email</th>
					<th scope="col">Phone</th>
					<th scope="col">Username</th>
					<th scope="col">Last Login</th>
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
					<td><a href="#" onclick="loadSection('Profile','<?php echo $user['ID']; ?>');"><?php echo ucwords($user['nicename']);?></a></td>
					<td><a href="mailto:<?php echo strtolower(crypto('decrypt', $user['email'], $user['hex']));?>"><?php echo textOnNull(strtolower(crypto('decrypt', $user['email'], $user['hex'])),"No Email");?></a></td>
					<td><a href="tel:<?php echo strtolower(crypto('decrypt', $user['phone'], $user['hex']));?>"><?php echo textOnNull(phone(crypto('decrypt', $user['phone'], $user['hex'])),"No Phone");?></a></td>
					<td><?php echo strtolower($user['username']);?></td>
					<td><?php echo textOnNull(gmdate("m/d/Y\ h:i A", $user['last_login']),"Never");?></td>
					<td><?php echo ucwords($user['accountType']);?></td>
					<td><?php echo $status;?></td>
					<td>
						<form action="index.php" method="POST">
							<input type="hidden" name="type" value="DeleteUser"/>
							<input type="hidden" name="ID" value="<?php echo $user['ID']; ?>"/>
							<?php if($user['active']=="1"){ ?>
								<input type="hidden" value="0" name="useractive"/>
								<button <?php if($user['ID']=="1") echo "disabled"; ?> type="submit" title="Deactivate User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
									<i class="fas fa-trash" ></i>				
								</button>
							<?php }else{ ?>
								<input type="hidden" value="1" name="useractive"/>
								<button type="submit" title="Activate User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-success btn-sm">
									<i class="fas fa-plus" ></i>
								</button>
							<?php } ?>
							<a href="#" data-toggle="modal" data-target="#userModal" onclick="editUser('<?php echo $user['ID'];?>','<?php echo $user['username'];?>','<?php echo $user['nicename'];?>','<?php echo crypto('decrypt', $user['email'], $user['hex']); ?>','<?php echo crypto('decrypt', $user['phone'], $user['hex']); ?>','<?php echo $user['accountType'];?>')" title="Edit User" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-dark btn-sm">
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
<script>
$(document).ready(function() {
    $('#dataTable').dataTable( {
		colReorder: true
	} );
});
</script>