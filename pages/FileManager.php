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
	sleep(3);
	//get update
	MQTTpublish($computerID."/Commands/getFilesystem","true",$computerID);
}
$json = getComputerData($computerID, array("WMI_ComputerSystem", "WMI_Filesystem"), $showDate);

$query = "SELECT  online, ID, hostname FROM computerdata WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
$getsFolder="Demos\dde";
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			File Manager (<?php echo count($json['WMI_Filesystem']);?>)
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
<div class="row "  style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
    <div style="width:100%" class="container-m-nx container-m-ny bg-lightest mb-3">
        <ol class="breadcrumb text-big container-p-x py-3 m-0">
            <li class="breadcrumb-item">
                <a href="javascript:void(0)">Root Directory</a>\<?php if($getFolder!=""){ ?><a onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $getFolder;?>');" href="javascript:void(0)"><?php echo $getFolder;?></a>
				<?php } ?>
			</li>
        </ol>

        <hr class="m-0" />

        <div  class="file-manager-actions container-p-x py-2">
            <div>
                <button type="button" class="btn btn-primary mr-2"><i class="ion ion-md-cloud-upload"></i>&nbsp; Upload</button>
                <button type="button" class="btn btn-secondary icon-btn mr-2" disabled=""><i class="ion ion-md-cloud-download"></i>>&nbsp; Download</button>
            </div>           
        </div>
        <hr class="m-0" />
    </div>

    <div class="file-manager-container file-manager-col-view">
        <div class="file-manager-row-header">
            <div class="file-item-name pb-2">Filename</div>
            <div class="file-item-changed pb-2">Changed</div>
        </div>		
		<?php 
		if($getFolder!=""){
			$back = $getFolder;
			$back2 = explode("\\",$back);	
			array_pop($back2);
			$back2 = implode("\\\\",$back2);
			$len3 = substr($info,1);
		?>
			<div style="masrgin-right:100%;background:#333;color:#fff" class="file-item">
				<div class="file-item-icon file-item-level-up fas fa-level-up-alt text-white"></div>
				<a class="text-white" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $back2;?>');" href="javascript:void(0)" class="file-item-name">
					Go Back
				</a>
			</div>
		<?php
		}
		$slots = $json['WMI_Filesystem']['C'];
		$files=array();
		$folders=array();
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
					if(strpos($info, ".") !== false){ 
						$icon = "file";	
					}else{
						$icon = "folder";
					}
					$info = str_replace($len3."\\","",$info);
					if (strpos($getFolder2, '\\') !== false) {
						$getFolder2= str_replace("\\","\\\\",$getFolder2);
					}
					$path = $getFolder2."\\\\".$info;
					if (strpos($info, '\\') !== false) {
						$icon="folder";
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
				if(strpos($info, ".") !== false){ 
					$icon = "file";	
				}else{
					$icon = "folder";
				}
				$path = $info;
				if (strpos($info, '\\') !== false) {
					$icon="folder";
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
		?>
        <div <?php if($icon=="folder"){ echo 'style="cursor:pointer"'; } ?>class="file-item bg-light">
            <div class="file-item-select-bg bg-primary"></div>
            <label class="file-item-checkbox custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" />
                <span class="custom-control-label"></span>
            </label>
            <div class="file-item-icon far fa-<?php echo $icon; ?> text-secondary"></div>
			<?php if($icon=="folder"){ ?>
            <a href="javascript:void(0)" onclick="loadSection('FileManager', '<?php echo $computerID; ?>','latest','<?php echo $path;?>');" class="file-item-name">
			<?php } echo $info;?>
            </a>
            <div class="file-item-actions btn-group">
                <button type="button" class="btn btn-default btn-sm rounded-pill icon-btn borderless md-btn-flat hide-arrow dropdown-toggle" data-toggle="dropdown"><i class="ion ion-ios-more"></i></button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="javascript:void(0)">Rename</a>
                    <a class="dropdown-item" href="javascript:void(0)">Move</a>
                    <a class="dropdown-item" href="javascript:void(0)">Copy</a>
                    <a class="dropdown-item" href="javascript:void(0)">Remove</a>
                </div>
            </div>
        </div>
        <?php } ?> 
    </div>
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