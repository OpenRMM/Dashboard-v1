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
$computerID = (int)$_GET['ID'];
$getFolder = clean($_GET['other']);
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
if($getFolder==""){
	sleep(2);
	//get update
	MQTTpublish($computerID."/Commands/getFilesystem","true",$computerID);
}
$json = getComputerData($computerID, array("WMI_ComputerSystem", "WMI_Filesystem"), $showDate);

$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
if($getFolder!=""){
	$back = $getFolder;
	$back2 = explode("\\",$back);	
	array_pop($back2);
	$back2 = implode("\\\\",$back2);
	$len3 = substr($info,1);
}
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			File Manager
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['WMI_Filesystem_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS g:i A", strtotime($json['WMI_Filesystem_lastUpdate']));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<a href="#" title="Refresh" onclick="loadSection('FileManager');" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
    <div style="width:100%" class="container-m-nx container-m-ny bg-lightest mb-3">
        <ol class="breadcrumb text-big container-p-x py-3 m-0">
            <li class="breadcrumb-item">
                <h5>
					<a style="font-size:20px" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $back2;?>');" href="javascript:void(0)">
					Root Directory\<?php if($getFolder!=""){ ?><?php echo $getFolder;?>
					</a>			
				</h5>
				<?php } ?>
			</li>
        </ol>
        <hr class="m-0" />
        <div  class="file-manager-actions container-p-x py-2">
            <div>
                <button type="button" class="btn-sm btn btn-primary mr-2"><i class="fas fa-cloud-upload-alt" aria-hidden="true"></i>&nbsp; Upload</button>
            </div>           
        </div>
        <hr class="m-0" />
    </div>
    <div style="margin-left:5%;display:none" class="fadein file-manager-container file-manager-col-view">
        <div class="file-manager-row-header">
            <div class="file-item-name pb-2">Filename</div>
            <div class="file-item-changed pb-2">Changed</div>
        </div>		
		<?php 
		if($getFolder!=""){
		?>
		<div style="background:#333;color:#fff;width:250px" class="file-item">
			<a class="text-white" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $back2;?>');" href="javascript:void(0)" class="file-item-name">
				<div class="file-item-icon file-item-level-up fas fa-level-up-alt text-white"></div>
						Go Back		
				</div>
			</a>
			<?php
			}
			$slots = $json['WMI_Filesystem']['C'];
			$files=array();
			$folders=array();
			$count=0;
			$error = $json['WMI_Filesystem_error'];
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
						$info = str_replace($len3."\\","",$info);
						if (strpos($getFolder2, '\\') !== false) {
							$getFolder2= str_replace("\\","\\\\",$getFolder2);
						}
						$path = $getFolder2."\\\\".$info;
						if (strpos($info, '\\') !== false) {
							$info = explode("\\",$info);
							$info = $info[0];
							array_push($folders,$info);
						}
						if(in_array($info,$folders)){
							continue;
						}
					}else{
						continue;
					}
				}else{
					$path = $info;
					if (strpos($info, '\\') !== false) {
						$info = explode("\\",$info);
						$info = $info[0];
						array_push($folders,$info);
					}
					if(in_array($info,$folders)){
						continue;
					}
				}	
				if($info==""){ 
					continue;
				}
				if(strpos($info, ".") !== false){ 
					$icon = "file";	
				}else{
					$icon = "folder";
				}	
				$count++;	
			?>
			<div style="<?php if($icon=="folder"){ echo 'cursor:pointer;'; } ?>width:250px" class="file-item bg-light">
				<?php if($icon=="folder"){ ?>
					<a href="javascript:void(0)" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $path;?>');" class="file-item-name">
					<?php } ?> 
				<div class="file-item-select-bg bg-primary"></div>           
				<div class="file-item-icon fas fa-<?php echo $icon; ?> text-secondary"></div>
					<?php echo $info; ?>
				</a>
				<div class="file-item-actions btn-group dropdown">
					<button type="button" data-bs-toggle="dropdown" class="btn btn-default btn-sm icon-btn borderless md-btn-flat hide-arrow dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-ellipsis-h"></i></button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
						<a class="dropdown-item" href="javascript:void(0)">Rename</a>
						<a class="dropdown-item" href="javascript:void(0)">Move</a>
						<a class="dropdown-item" href="javascript:void(0)">Copy</a>
						<a class="dropdown-item" href="javascript:void(0)">Delete</a>
						<a class="dropdown-item" href="javascript:void(0)">Download</a>
					</div>
				</div>
			</div>
        <?php } ?> 
    </div>
	<?php
	if($count==0){ ?>
	<div>
		<center>
			<h6>
				No files found
			</h6>
		</center>
	</div>
	<?php } ?>
</div>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
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
</script>