<?php 
$computerID = (int)base64_decode($_GET['ID']);
checkAccess($_SESSION['page'],$computerID);

$json = getComputerData($computerID, array("printers"));

$query = "SELECT  online, ID FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_fetch_assoc(mysqli_query($db, $query));
$online = $results['online'];

$printers = $json['printers']['Response'];
$error = $json['printers_error'];
?>
<div style="padding:20px;margin-bottom:-1px;" class="card">
	<div class="row" style="padding:15px;">	
		<div class="col-md-9">
			<h5 style="color:#0c5460">
				Printers (<?php echo count($printers);?>)
			</h5>
			<span style="font-size:12px;color:#666;"> 
				Last Update: <?php echo ago($json['printers_lastUpdate']);?>
			</span>
		</div>
		<div class="col-md-3" style="text-align:right;">
			<div class="btn-group">
				<button style="background:#0c5460;color:#d1ecf1" onclick="loadSection('Asset_Printers');" type="button" class="btn btn-sm"><i class="fas fa-sync"></i> &nbsp;Refresh</button>
				<button style="background:#0c5460;color:#d1ecf1" type="button" class="btn dropdown-toggle-split btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-sort-down"></i>
				</button>
				<div class="dropdown-menu">
					<a onclick="force='true'; loadSection('Asset_Printers','<?php echo $computerID; ?>','latest','force');" class="dropdown-item" href="javascript:void(0)">Force Refresh</a>
				</div>
			</div>
			<button title="Change Log" class="btn btn-sm" style="margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;" data-toggle="modal" data-target="#olderDataModal" onclick="olderData('<?php echo $computerID; ?>','Printers','null');">
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
	<table id="<?php echo $_SESSION['userid']; ?>Printers" style="line-height:20px;overflow:hidden;font-size:12px;margin-top:8px;font-family:Arial;" class="table table-hover  table-borderless">
	  <thead>
		<tr style="border-bottom:2px solid #d3d3d3;">
		  <th scope="col">#</th>
		  <th scope="col">Name</th>
		  <th scope="col">Port</th>
		  <th scope="col">Shared</th>
		</tr>
	  </thead>
	  <tbody>
		<?php
			foreach($printers as $key=>$print){
				$count++;
		?>
			<tr>
			  <th scope="row"><?php echo $count;?></th>
			  <td>
				<?php echo textOnNull($print['Caption'], "[No Name]");?>
				<?php 
					if($print['Default'] == "True"){echo "<b>(Default)</b>";}
				?>
			  </td>
			  <td><?php echo textOnNull(substr($print['PortName'],0,25), "Not Set");?></td>
			  <td><?php echo textOnNull($print['Shared'], "False");?></td>
			</tr>
		<?php }
			if(count($printers) == 0){ ?>
				<tr>
					<td colspan=4><center><h6>No printers found.</h6></center></td>
				</tr>
		<?php }?>
	   </tbody>
	</table>
</div>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Printers').dataTable( {
			colReorder: true,
			stateSave: true
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