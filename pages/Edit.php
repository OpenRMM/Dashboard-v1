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
$computerID = (int)clean($_GET['ID']);

$query = "SELECT ID, show_alerts, hostname, CompanyID, phone, email, online, name, comment,computer_type FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$data = mysqli_fetch_assoc($results);

$query = "SELECT CompanyID, name, phone, address, email, comments FROM companies WHERE CompanyID='".$data['CompanyID']."' LIMIT 1";
$companys = mysqli_query($db, $query);
$company = mysqli_fetch_assoc($companys);

$online = $data['online'];
?>
<?php if($data['hostname']==""){ ?>
	<br>
	<center>
		<h4>No Asset Selected</h4>
		<p>
			To Select A Asset, Please Visit The
			<a class='text-dark' style="cursor:pointer" onclick="loadSection('Assets');" ><u>Assets Page</u></a>
		</p>
	</center>
	<hr>
<?php exit; }?>
<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">Editing Asset: <?php echo $data['hostname']; ?>
	<a href="javascript:void(0)" title="Refresh" onclick="loadSection('Edit');" class="btn btn-sm" style="float:right;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
		<i class="fas fa-sync"></i>
	</a>
	<a href="javascript:void(0)" title="Agent Configuration" onclick="loadSection('AgentSettings');" class="btn btn-sm" style="float:right;margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
		<i class="fas fa-cogs"></i>
	</a>
</h4>
<hr>
<div style="width:100%;backgrdound:#fff;padding:15px;">
	<p class="lead">
	   <small class="text-muted"> Here You Can Add Information About The Asset, Client And The <?php echo $msp; ?> It's Assigned To.</small>
	</p>
	<hr />
	<form method="POST" action="index.php">
		<div class="row">
			<div style="padding:20px;border-radius:6px" class="card card-sm col-sm-8">
				<input type="hidden" name="type" value="EditComputer"/>
				<input type="hidden" name="ID" value="<?php echo $data['ID']; ?>"/>
				<div class="form-group float-label-control">
					<label><?php echo $msp; ?>:</label>
					<select name="company" class="form-control">
						<option value="<?php echo $company['CompanyID']; ?>"><?php echo textOnNull($company['name'],"Select A Company"); ?></option>
						<?php
							$query = "SELECT CompanyID, name FROM companies WHERE active='1' ORDER BY CompanyID ASC";
							$results = mysqli_query($db, $query);
							while($result = mysqli_fetch_assoc($results)){ ?>
								<option value='<?php echo $result['CompanyID'];?>'><?php echo $result['name'];?></option>
						<?php }?>
					</select>
					<br>
					<label>Asset Type:</label>
					<select name="pctype" class="form-control">
						<option value="<?php echo $data['computer_type']; ?>"><?php echo textOnNull($data['computer_type'],"Select An Asset Type"); ?></option>
						<option value="Laptop">Laptop</option>
						<option value="Desktop">Desktop</option>
						<option value="All-in-One">All-in-One</option>
						<option value="Tablet">Tablet</option>
						<option value="Server">Server</option>
						<option value="Other">Other</option>
					</select>
				</div>
				<hr>
				<h4 class="page-header">Client Information</h4><br>
				<div class="form-group float-label-control">
					<label>Client Name:</label>
					<input type="text" name="name" value="<?php echo $data['name']; ?>" class="form-control" placeholder="What's Their Name?">
				</div>
				<div class="form-group float-label-control">
					<label>Client Phone:</label>
					<input type="text" name="phone" value="<?php echo $data['phone']; ?>" class="form-control" placeholder="What's Their Phone Number?">
				</div>
				<div class="form-group float-label-control">
					<label>Client Email Address:</label>
					<input type="email" name="email" value="<?php echo $data['email']; ?>" class="form-control" placeholder="What's Their Email Address?">
				</div>
				<div class="form-group float-label-control">
					<textarea rows=12 style="resize:vertical" placeholder="Any Comments?" name="comment" class="form-control"><?php echo $data['comment']; ?></textarea>
				</div>
				<div style="margin-top:30px;" class="form-group float-label-control">
					<input style="background:#0ac282;color:#fff" type="submit" class="form-control" value="Save Details">
				</div>
			</div>				
			<div class="col-sm-4">
				<div class="panel panel-default" style="height:auto;color:#fff;color#000;padding:20px;border-radius:6px;margin-bottom:20px;">
					<center>
						<a style="width:65%;margin-top:-3px;border:none;" class="btn btn-danger btn-md" data-toggle="modal" data-target="#delModal" href="javascript:void(0)">
							<i class="fas fa-trash"></i> Delete Asset
						</a>
					</center>
				</div>
				<hr>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							Asset Settings
						</h4>
					</div>
					<div  class="panel-body">
						<div class="form-check" style="border-radius:6px;margin-bottom:10px;padding:10px;padding-left:50px;color:#333;">
							<input value="1" <?php if($data['show_alerts']=="1"){ echo "checked"; } ?>  name="show_alerts" type="checkbox" class="form-check-input" id="noalerts">
							<label class="form-check-label" for="show_alerts">Show Alerts For This Asset</label>
						</div>
					</div>
				</div>		
				<div style="margin-top:20px"  class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
						<?php echo $msp; ?> Information
						</h4>
					</div>
					<div class="panel-body">
						<ul class="list-group">
							<li class="list-group-item"><b>Name:</b>
								<a href="javascript:void(0)" onclick="searchItem('<?php echo textOnNull($company['name'],"N/A"); ?>');" title="Search Company">
									<?php echo textOnNull($company['name'],"N/A"); ?>
								</a>
							</li>
							<li class="list-group-item"><b>Email:</b>
								<a href="mailto:<?php echo $company['email']; ?>">
									<?php echo textOnNull(ucfirst($company['email']),"N/A"); ?>
								</a>
							</li>
							<li class="list-group-item"><b>Phone:</b> <?php echo textOnNull(phone($company['phone']),"N/A"); ?></li>
							<li class="list-group-item"><b>Address:</b> <?php echo textOnNull($company['address'],"N/A"); ?></li>
							<li class="list-group-item"><b>Additional Info:</b> <?php echo textOnNull(ucfirst($company['comments']),"None"); ?></li>
						</ul>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							Recently Edited
						</h4>
					</div>
					<div class="panel-body">
						<ul class="list-group">
							<?php
							$count = 0;
							$recentedit = array_slice($_SESSION['recentedit'], -4, 4, true);
							foreach(array_reverse($recentedit) as $item) {
								if($item==""){continue;}
								$query = "SELECT * FROM computerdata where ID='".$item."'";
								$results = mysqli_query($db, $query);
								$data = mysqli_fetch_assoc($results);
								if($data['hostname']==""){continue;}
								$count++;
							?>
							<a href="javascript:void(0)" class="text-dark" onclick="loadSection('Edit', '<?php echo $data['ID']; ?>');">
								<li class="list-group-item">
									<i class="fas fa-desktop"></i>&nbsp;
									<?php echo strtoupper($data['hostname']);?>
								</li>
							</a>
							<?php } ?>
							<?php if($count==0){ ?>
								<li class="list-group-item">No Recently Edited Assets</li>
							<?php } ?>
						</ul>
					</div>
				</div>			
			</div>
		</div>
	</form>
</div>
<!-----------------------------------------modal------------------------------->
<div id="delModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<h6 class="modal-title">Delete Asset?</h6>
	  </div>
	  <?php if($_SESSION['accountType']=="Admin"){ ?>
	  <div class="modal-body">
		<p>Are You Sure You Would Like To Delete This Asset? This Cannot Be Undone.</p>
	  </div>
	  <div class="modal-footer">
		  <form action="index.php" method="POST">
			<input type="hidden" name="type" value="DeleteComputer"/>
			<input type="hidden" name="ID" value="<?php echo $data['ID'];?>"/>
			<input type="hidden" name="hostname" value="<?php echo $data['hostname'];?>"/>
			<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			<button type="submit" class="btn btn-danger">Confirm</button>
		  <form>
	  </div>
	  <?php }else{ ?>
		<div class="modal-body text-center"><br>
			<p>Sorry, You Do Not Have Permissions To Delete Assets</p>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Cancel</button>
		</div>
	  <?php } ?>
	</div>
  </div>
</div>
<script>
    tinymce.init({
      selector: 'textarea',
      plugins: 'a11ychecker advcode casechange formatpainter linkchecker autolink lists checklist media mediaembed pageembed permanentpen powerpaste table advtable tinycomments',
      toolbar: 'a11ycheck addcomment showcomments casechange checklist code formatpainter pageembed permanentpen table',
      toolbar_mode: 'floating',
      tinycomments_mode: 'embedded',
      tinycomments_author: 'SMG_RMM',
    });
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove()
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>