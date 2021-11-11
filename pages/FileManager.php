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
$showDate = $_SESSION['date'];
if($computerID<0){ 
	?>
	<br>
	<center>
		<h4>No Computer Selected</h4>
		<p>
			To Select A Computer, Please Visit The <a class='text-dark' style="cursor:pointer" onclick='loadSection("Assets");'><u>Assets page</u></a>
		</p>
	</center>
	<hr>
<?php
	exit;
}

$computerID = (int)base64_decode($_GET['ID']);
$gets = clean(base64_decode($_GET['other']));
if($gets=="force"){ $gets=""; }
$get = explode("{}",$gets);
$drive = $get[0];
$getFolder = $get[1];
if($drive==""){
	$drive="C";
}
if($getFolder!=""){
	$shownFolder=$getFolder;
}else{
	$shownFolder="/";
}	
if($getFolder!=""){
	$back = $getFolder;
	$back2 = explode("/",$back);	
	array_pop($back2);
	$back2 = $drive."{}".implode("/",$back2);
	$len3 = substr($info,1);
}

$json = getComputerData($computerID, array("General", "Filesystem", "LogicalDisk"), $showDate);

$disks = $json['LogicalDisk']['Response'];
$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

?>
<div style="margin-top:0px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">File Manager
		<br>
		<span style="color:#000;font-size:12px">Last Update: <?php echo ago($json['Filesystem_lastUpdate']);?></span>
		<hr>
		<span style="font-size:14px">Current Path:</span><br>
		<a href="javascript:void(0)" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $back2; ?>');" style="font-size:22px;margin-left:20px"><?php echo $drive.":".$shownFolder; ?></a>
		<div style="float:right;">
			<div class="btn-group">
				<button onclick="loadSection('FileManager');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="loadSection('FileManager','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>

				</div>
			</div>
		</div>	
	</h4>
</div>
<div class="row" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
	<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding-bottom:20px;padding-top:0px;border-radius:6px;">			 
			<form method="post" action="/">
				<div class="card table-card" id="printTable" style="margin-top:-40px;padding:10px;overflow-x:auto">  
					<div class="card-header">
						<div class="card-header-right">
							<ul class="list-unstyled card-option">
								<li><i class="feather icon-maximize full-card"></i></li>
								<li><i class="feather icon-minus minimize-card"></i></li>
								<li><i class="feather icon-trash-2 close-card"></i></li>
							</ul>
						</div>
					</div>
					<?php if($getFolder!=""){ ?>
						<a href="javascript:void(0)" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $back2;?>');" style="text-align:left" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i>&nbsp; Go back</a><br>
					<?php } ?>
					<table id="dataTable" style="line-height:20px;overflow:auto;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover table-borderless">				
						<thead>
							<tr style="border-bottom:2px solid #d3d3d3;">			  
								<th scope="col">Type</th>
								<th scope="col">Filename</th>
								<th style="float:right" scope="col">Actions</th>
							</tr>
						</thead>
						<tbody>
						<?php	
							$slots = $json['Filesystem']['Response'][0];
							$files=array();
							//print_r($slots);
							$folders=array();
							$count=0;
							$folderCount=0;	
							$fileCount=0;	
							$slots = str_replace("//","/",$slots);
							$error = $json['Filesystem_error'];
							$getFolder2 = $getFolder;
							foreach($slots as $slot=>$info){ 
								$info = str_replace("C:","",$info);
								if($getFolder!=""){
									$len = strlen($getFolder);
									$len3 = substr($info,0,$len);
									if($getFolder==$len3){
										if($info==$getFolder){		
											continue;	
										}
										$info = str_replace($len3."/","",$info);
										$path = $drive."{}".$getFolder2."/".$info;
										if(in_array($info,$folders)){
											continue;
										}
										if (strpos($info, '/') !== false) {
											$info = explode("/",$info);
											$info = $info[0];
											array_push($folders,$info);
										}
									}else{
										continue;
									}
								}else{
									$path = $drive."{}".$info;
									if(in_array($info,$folders)){
										continue;
									}
									if (strpos($info, '/') !== false) {
										$info = explode("/",$info);
										$info = $info[1];
										array_push($folders,$info);
									}	
								}	
								if($info==""){ 
									continue;
								}
								if(strpos($info, ".") !== false){ 
									$icon = "file";
									$fileCount++;	
								}else{
									$icon = "folder";
									$folderCount++;	
								}	
								$count++;	
						?>		
						<tr <?php if($icon=="folder"){ ?>style="cursor:pointer" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo ($path);?>');"<?php } ?>>
							<td>
								<i style="font-size:18px" class="fas fa-<?php echo $icon; ?> text-secondary"></i>
							</td>
							<td>       	
								<?php echo $info; ?>	
							</td>
							<td style="float:right">
								<a style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;" title="Rename" class="btn btn-sm btn-success" onclick="fileActionsModal('rename','<?php echo $info; ?>');" data-toggle="modal" data-target="#fileAction_modal" href="javascript:void(0)"><i class="fas fa-pen"></i></a>
								<a style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;"title="Move" class="btn btn-sm btn-secondary" onclick="fileActionsModal('move','<?php echo $info; ?>');" data-toggle="modal" data-target="#fileAction_modal" href="javascript:void(0)"><i class="fas fa-arrows-alt"></i></a>
								<a style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;"title="Copy" class="btn btn-sm btn-primary" onclick="fileActionsModal('copy','<?php echo $info; ?>');" data-toggle="modal" data-target="#fileAction_modal" href="javascript:void(0)"><i class="fas fa-copy"></i></a>
								<a style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;"title="Download" class="btn btn-sm btn-warning" onclick="fileActionsModal('download','<?php echo $info; ?>');" data-toggle="modal" data-target="#fileAction_modal" href="javascript:void(0)"><i class="fas fa-download"></i></a>
								<a style="margin-top:-2px;padding:8px;padding-top:6px;padding-bottom:6px;border:none;"title="Delete" class="btn btn-sm btn-danger" onclick="fileActionsModal('delete','<?php echo $info; ?>');" data-toggle="modal" data-target="#fileAction_modal" href="javascript:void(0)"><i class="fas fa-trash"></i></a>
							</td>
						</tr>
					<?php } ?>
					<?php
						if($count==0){ ?>
						<tr>
								<td colspan=4><center><h6>No files found.</h6></center></td>
						</tr>
						<?php } ?>
					</tbody>
				</table> 
			</div>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="padding-left:20px;">
			<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">			
				<div class="card-block text-center">
					<h4 class="m-b-15">Directory Information</h4>
					<div class="row justify-content-center m-t-10 b-t-default m-l-0 m-r-0">
						<div class="col m-t-15 b-r-default">
							<h6 class="text-muted">Folders</h6>
							<h6><?php echo $folderCount; ?></h6>
						</div>
						<div class="col m-t-15">
							<h6 class="text-muted">Files</h6>
							<h6><?php echo $fileCount; ?></h6>
						</div>
					</div>
				</div>
				<button onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','');" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-warning btn-block p-t-15 p-b-15">Go to home directory root</button>		
			</div>
			<div class="card user-card2" style="width:100%;box-shadow:none;background:<?php echo $siteSettings['theme']['Color 1'];?>">				
				<div class="card-block row">
					<?php
						foreach($disks as $disk){
							$freeSpace = $disk['FreeSpace'];
							$size = $disk['Size'];
							$used = $size - $freeSpace ;
							$usedPct = round(($used/$size) * 100);
							if($size!=0){
								$status = round((int)$used/ 1024 /1024 /1024)." of ".round((int)$disk['Size']/ 1024 /1024 /1024)." GB Used";
							}else{
								$status="No Size Avaliable";
							}
							//Determine Warning Level
							if($usedPct > $siteSettings['Alert Settings']['Disk']['Danger'] ){
								$pbColor = "red"; 
							}elseif($usedPct > $siteSettings['Alert Settings']['Disk']['Warning']){
								$pbColor = "#ffa500";
							}else{ $pbColor = "#03925e"; }	
							//check if in network disks
							foreach($mappedDisks as $mappedDisk){
								if(trim($mappedDisk["Name"]) == trim($disk['Name'])){
									continue(2);
								}
							}
							if(strpos($disk["ProviderName"], ".") == false){		
					?>	
						<div class="col-md-4" style="padding:5px;">
							<a href="javascript:void(0)" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo (str_replace(":","",$disk['Name'])."{}".$getFolder);?>');">
								<div class="card bg-dark" style="height:75%;padding:5px;">
									<div style="text-align:center;">
										<h5 class="card-title text-white" style="color:#333;padding-top:5px;padding-bottom:10px;">
											<b><?php echo $disk['Name'];?>\</b>
										</h5>
									</div>
									<div class="progress" style="background:#a4b0bd;" title="<?php echo $usedPct;?>%">
										<div class="progress-bar" role="progressbar" style="background:<?php echo $pbColor;?>;width:<?php echo $usedPct;?>%" aria-valuenow="<?php echo $usedPct;?>" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
									<center>
										<p style="font-size:10px" class="text-white">
											<?php echo $status; ?>
										</p>
									</center>
								</div>
							</a>
						</div>				
					<?php 
							} 
						} 
					if(count($disks) == 0){ ?>
						<div class="col-md-12" style="padding:5px;margin-left:30px;">
							<h6>No physical drives found.</h6>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;margin-top:-35px">
				<div style="height:45px" class="panel-heading">
					<h5 class="panel-title">Upload File</h5>
				</div>
				<form method="post">
					<div class="card-block texst-center">
						<div class="custom-file" >
							<input required="" type="hidden" value="true" name="agentFile">
							<input  required="" accept=".exe" type="file" name="agentUpload" class="custom-file-input" id="agentUpload"/>
							<label style="padding:10px;padding-bottom:30px" class="custom-file-label" for="agentUpload">Choose file</label>
						</div>
					</div>
					<button type="submit" style="background:<?php echo $siteSettings['theme']['Color 5']; ?>;border:none" title="Complete Upload" class="btn btn-warning btn-block p-t-15 p-b-15">Start Upload</button>
				</form>
			</div>	
		</div>
		
	</div>
</div>
<!------------- file action modal ------------------->
<div id="fileAction_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="filename" class="modal-title">
					File Actions
				</h5>
			</div>
			<form method="Post">
				<div class="modal-body">
					<div id="fileActionText" style="overflow:auto;max-height:400px;">
						
					</div>
					<br>
					<div style="display:none" id="actions_input">
						<br>
						<label id="actions_inputLabel">File Path</label>
						<input type="text" placeholder="ex. C:/Demos/test" value="<?php echo $getFolder; ?>" class="form-control" name="filePath">
					</div>
					<div class="modal-footer">
						<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning"  data-dismiss="modal">Close</button>
						<input type="submit" id="actions_btnText" value="Save" class="btn btn-primary btn-sm">
					</div>			
				</div>
			</form>
		</div>	
	</div>
</div>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>	
	$('#dataTable').DataTable( {
		"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
		colReorder: true,
		fixedHeader: true
	});
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>
<script>
$(document).ready(function(){
	$('.fadein').fadeIn("slow");
});
function fileActionsModal(action, filename){
	$("#filename").html("<b>File Actions for "+filename+"</b>");
	if(action=="delete"){
		$("#fileActionText").html("Are you sure you would like to delete this file from the selected asset?");
		$("#actions_input").hide();
		$("#actions_btnText").val("Delete");
		return; 
	}
	if(action=="rename"){
		$("#fileActionText").html("What is the desired name for this file?");
		$("#actions_inputLabel").text("File Name:");
		$("#actions_input").show();
		$("#actions_btnText").val("Rename");
		return;
	}
	if(action=="move"){
		$("#fileActionText").html("Where would you like to move this file to?");
		$("#actions_inputLabel").text("File Path:");
		$("#actions_input").show();
		$("#actions_btnText").val("Move");
		return;
	}
	if(action=="copy"){
		$("#fileActionText").html("Where would you like to copy this file to?");
		$("#actions_inputLabel").text("File Path:");
		$("#actions_input").show();
		$("#actions_btnText").val("Copy");
		return;
	}
	if(action=="download"){
		$("#fileActionText").html("Would you like to Download: " +filename+"?");
		$("#actions_input").hide();
		$("#actions_btnText").val("Download");
		return;
	}
}
</script>
