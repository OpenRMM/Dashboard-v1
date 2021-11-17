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
$computerID = (int)base64_decode($_GET['ID']);
$json = getComputerData($computerID, array("general"));
$hostname =  textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
$query = "SELECT ID, show_alerts, company_id, phone,hex, email, online, name, comment,computer_type FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$data = mysqli_fetch_assoc($results);

$query = "SELECT ID, name, phone, address, email, comments,hex FROM companies WHERE ID='".$data['company_id']."' LIMIT 1";
$companys = mysqli_query($db, $query);
$company = mysqli_fetch_assoc($companys);

$online = $data['online'];
?>
<?php if($data['ID']==""){ ?>
	<br>
	<center>
		<h4>No Asset Selected</h4>
		<p>
			To Select An Asset, Please Visit The <a class='text-dark' style="cursor:pointer" onclick='loadSection("Assets");'><u>Assets page</u></a>
		</p>
	</center>
	<hr>
<?php exit; }?>
<div class="card">
	<div class="row" style="padding:15px;">
		<div class="col-md-10">
			<h5 style="color:#0c5460">Editing Asset: <span style="color:#333"><?php echo $hostname; ?></span>
				<br>
				<p>
					Here You Can Add Information About The Asset, Client And The <?php echo $msp; ?> It's Assigned To
				</p>
			</h5>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<button title="Refresh" onclick="loadSection('Edit');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-sync"></i>
			</button>
			<button title="Agent Configuration" onclick="loadSection('AgentSettings');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
				<i class="fas fa-cogs"></i>
			</button>
		</div>
	</div>
</div>
<div style="width:100%;backgrdound:#fff;padding:15px;">
	<form method="POST" action="/">
		<div class="row">
			<div style="padding:20px;border-radius:6px" class="card card-sm col-sm-8">
				<input type="hidden" name="type" value="EditComputer"/>
				<input type="hidden" name="ID" value="<?php echo $data['ID']; ?>"/>
				<div class="form-group float-label-control">
					<label><?php echo $msp; ?>:</label>
					<select name="company" class="form-control">
						<option value="<?php echo $company['ID']; ?>"><?php echo textOnNull(crypto('decrypt',$company['name'],$company['hex']),"Select A Company"); ?></option>
						<?php
							$query = "SELECT ID, name,hex FROM companies WHERE active='1' ORDER BY ID ASC";
							$results = mysqli_query($db, $query);
							while($result = mysqli_fetch_assoc($results)){ 
								if($result['ID']==$company['ID']){continue;}		
						?>
								<option value='<?php echo $result['ID'];?>'><?php echo crypto('decrypt',$result['name'],$result['hex']);?></option>
						<?php }?>
						<option value="0">Not Assigned</option>
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
					<input type="text" name="name" value="<?php echo crypto('decrypt',$data['name'],$data['hex']); ?>" class="form-control" placeholder="What's Their Name?">
				</div>
				<div class="form-group float-label-control">
					<label>Client Phone:</label>
					<input type="text" name="phone" value="<?php echo crypto('decrypt',$data['phone'],$data['hex']); ?>" class="form-control" placeholder="What's Their Phone Number?">
				</div>
				<div class="form-group float-label-control">
					<label>Client Email Address:</label>
					<input type="email" name="email" value="<?php echo crypto('decrypt',$data['email'],$data['hex']); ?>" class="form-control" placeholder="What's Their Email Address?">
				</div>
				<div class="form-group float-label-control">
					<textarea rows=12 style="resize:vertical" placeholder="Any Comments?" name="comment" class="form-control"><?php echo crypto('decrypt',$data['comment'],$data['hex']); ?></textarea>
				</div>
			</div>				
			<div class="col-sm-4">
				<div class="panel panel-default" style="height:auto;color:#fff;color#000;padding:20px;border-radius:6px;margin-bottom:20px;">
					<center>
						<button style="width:55%;margin-top:-3px;border:none;" type="submit" class="btn btn-success btn-sm">
							<i class="fas fa-save"></i> &nbsp; Save Details
						</button>
						<?php if($_SESSION['accountType']=="Admin"){ ?>
							<a style="width:35%;margin-top:-3px;border:none;" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delModal" href="javascript:void(0)">
								<i class="fas fa-trash"></i> &nbsp; Delete Asset
							</a>
						<?php } ?>
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
								<a href="javascript:void(0)" onclick="searchItem('<?php echo textOnNull(crypto('decrypt',$company['name'],$company['hex']),"N/A"); ?>');" title="Search Company">
									<?php echo textOnNull(crypto('decrypt',$company['name'],$company['hex']),"N/A"); ?>
								</a>
							</li>
							<li class="list-group-item"><b>Email:</b>
								<a href="mailto:<?php echo crypto('decrypt',$company['email'],$company['hex']); ?>">
									<?php echo textOnNull(ucfirst(crypto('decrypt',$company['email'],$company['hex'])),"N/A"); ?>
								</a>
							</li>
							<li class="list-group-item"><b>Phone:</b> <?php echo textOnNull(phone(crypto('decrypt',$company['phone'],$company['hex'])),"N/A"); ?></li>
							<li class="list-group-item"><b>Address:</b> <?php echo textOnNull(crypto('decrypt',$company['address'],$company['hex']),"N/A"); ?></li>
							<li class="list-group-item"><b>Additional Info:</b> <?php echo textOnNull(ucfirst(crypto('decrypt',$company['comments'],$company['hex'])),"None"); ?></li>
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
								$query = "SELECT * FROM computers where ID='".$item."'";
								$results = mysqli_query($db, $query);
								$data = mysqli_fetch_assoc($results);
								if($data['ID']==""){continue;}
								$json = getComputerData($data['ID'], array("general"));
								$hostname =  $json['general']['Response'][0]['csname'];
								$count++;
								$icons = array("desktop","server","laptop","tablet","allinone","other");
								if(in_array(strtolower(str_replace("-","",$data['computer_type'])), $icons)){
									$icon = strtolower(str_replace("-","",$data['computer_type']));
									if($icon=="allinone")$icon="tv";
									if($icon=="tablet")$icon="tablet-alt";
									if($icon=="other")$icon="microchip";
								}else{
									$icon = "server";
								}  
							?> 
							<a href="javascript:void(0)" class="text-dark" onclick="loadSection('Edit', '<?php echo $data['ID']; ?>');$('.sidebarComputerName').text('<?php echo strtoupper($hostname);?>');">
								<li class="list-group-item secbtn">
									<?php if($data['online']=="0") {?>
										<i class="fas fa-<?php echo $icon;?>" style="color:#666;font-size:12px;" title="Offline"></i>
									<?php }else{?>
										<i class="fas fa-<?php echo $icon;?>" style="color:green;font-size:12px;" title="Online"></i>
									<?php }?>
									&nbsp;&nbsp;<?php echo strtoupper($hostname);?>
								</li>
							</a>
							<?php } ?>
							<?php if($count==0){ ?>
								<li class="list-group-item">No recently edited assets</li>
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
		  <form action="/" method="POST">
			<input type="hidden" name="type" value="DeleteComputer"/>
			<input type="hidden" name="ID" value="<?php echo $data['ID'];?>"/>
			<input type="hidden" name="hostname" value="<?php echo $hostname;?>"/>
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
	<?php if($online=="0"){ ?>
		toastr.remove()
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>