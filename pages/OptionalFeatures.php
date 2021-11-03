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
	$computerID = (int)base64_decode($_GET['ID']);
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
	//get update
	$query = "SELECT hostname FROM computers WHERE ID='".$computerID."'";
	$results = mysqli_query($db, $query);
	$computer = mysqli_fetch_assoc($results);
	//MQTTpublish($computerID."/Commands/getOptionalFeatures","true",getSalt(20));

	$json = getComputerData($computerID, array("OptionalFeatures"), $showDate);

	$query = "SELECT  online, ID, hostname FROM computers WHERE ID='".$computerID."' LIMIT 1";
	$results = mysqli_fetch_assoc(mysqli_query($db, $query));
	$online = $results['online'];
?>
<div class="row" style="background:#fff;padding:15px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;border-radius:6px;margin-bottom:20px;">
	<div class="col-md-10">
		<h4 style="color:<?php echo $siteSettings['theme']['Color 2'];?>">
			Optional Features (<?php echo count($json['OptionalFeatures']['Response']); ?>)
		</h4>
		<?php if($showDate == "latest"){?>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['OptionalFeatures_lastUpdate']);?>
			</span>
		<?php }else{?>
			<span class="badge badge-warning" style="font-size:12px;cursor:pointer;" data-toggle="modal" data-target="#historicalDateSelection_modal">
				History: <?php echo date("l, F jS", strtotime($showDate));?>
			</span>
		<?php }?>
	</div>
	<div class="col-md-2" style="text-align:right;">
		<div class="btn-group">
			<button onclick="loadSection('OptionalFeatures');" type="button" class="btn btn-warning btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
			<button type="button" class="btn btn-warning dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-sort-down"></i>
			</button>
			<div class="dropdown-menu">
				<a onclick="loadSection('OptionalFeatures','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
			</div>
		</div>
		<a href="javascript:void(0)" title="Select Date" class="btn btn-sm" style="margin:5px;color:#fff;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#historicalDateSelection_modal">
			<i class="far fa-calendar-alt"></i>
		</a>
	</div>
</div>
<div style="padding:10px;background:#fff;border-radius:6px;box-shadow:rgba(0, 0, 0, 0.13) 0px 0px 11px 0px;">
	<table id="dataTable" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
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
			$OptionalFeatures = $json['OptionalFeatures']['Response'];
			$error = $json['OptionalFeatures_error'];
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
					<td colspan=5><center><h5>No Optional Features Found.</h5></center></td>
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
		$('#dataTable').dataTable( {
			colReorder: true
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