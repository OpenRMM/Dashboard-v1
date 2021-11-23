<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$json = getComputerData($computerID, array("optional_features"));

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">	
		<div class="col-md-10">
			<h5 style="color:#0c5460">
				Optional Features (<?php echo count($json['optional_features']['Response']); ?>)
			</h5>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['optional_features_lastUpdate']);?>
			</span>
		</div>
		<div class="col-md-2" style="text-align:right;">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Optional_Features');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button style="background:#0c5460;color:#d1ecf1" type="button" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Optional_Features','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','optional_features','null');">
				<i class="fas fa-scroll"></i>
			</button>
		</div>
	</div>
</div>
<?php if($online=="0"){ ?>
	<div  style="border-radius: 0px 0px 4px 4px;" class="alert alert-danger" role="alert">
		&nbsp;&nbsp;&nbsp;This Agent is offline		
	</div>
<?php 
}else{
	echo"<br>";
}
?>
<div style="overflow-x:auto;padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
	<table id="<?php echo $_SESSION['userid']; ?>Optional_Features" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
		<thead>
		<tr style="border-bottom:2px solid #d3d3d3;">
			<th scope="col">#</th>
			<th scope="col">Name</th>
			<th scope="col">Caption</th>
			<th scope="col">Description</th>
			<th scope="col">Install State</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$installState = array(
				"1"=>array("state"=>"Enabled", "color"=>"green"), 
				"2"=>array("state"=>"Disabled", "color"=>"red"), 
				"3"=>array("state"=>"Absent", "color"=>"gray"),
				"4"=>array("state"=>"Unknown", "color"=>"gray")
			);
			$OptionalFeatures = $json['optional_features']['Response'];
			$error = $json['optional_features_error'];
			//Sort The array by Name ASC
			usort($OptionalFeatures, function($a, $b) {
				return $a['Name'] <=> $b['Name'];
			});
			foreach($OptionalFeatures as $key=>$feature){
				if($search!=""){ 
					if(stripos($feature['Name'], $search) !== false){ }else{ continue; }		
				}
				$count++;
				$state = $installState[$feature['InstallState']];
		?>
			<tr>
				<th scope="row"><?php echo $count;?></th>
				<td><?php echo textOnNull($feature['Name'],"N/A");?></td>
				<td><?php echo textOnNull($feature['Caption'],"N/A");?></td>
				<td><?php echo textOnNull($feature['Description'],"N/A");?></td>
				<td style="color:<?php echo $state['color'];?>;">
				<b><?php echo textOnNull($state['state'],"Unknown");?></b>
				</td>
			</tr>
		<?php }
				if($count == 0){ ?>
				<tr>
					<td colspan=5><center><h6>No optional features found.</h6></center></td>
				</tr>
		<?php }?>
		</tbody>
	</table>
</div>
<script>
	$('#searchInputOptf').keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			search($('#searchInputOptf').val(),'OptionalFeatures','<?php echo $computerID; ?>');
		}
	});
</script>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Optional_Features').dataTable( {
			colReorder: true,
			stateSave: true,
		} );
	});
</script>
<script>
    $(".sidebarComputerName").text("<?php echo strtoupper($_SESSION['ComputerHostname']);?>");
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
</script>