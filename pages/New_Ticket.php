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
	$query = "SELECT username,nicename,hex FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];

	//assets
	$query = "SELECT ID FROM computers where active='1' and online='1'";
	$assets1 = mysqli_num_rows(mysqli_query($db, $query));
	$query = "SELECT ID FROM computers where active='1' and online='0'";
	$assets2 = mysqli_num_rows(mysqli_query($db, $query));
?>
<style>
	.grid-divider {
  overflow-x: hidden;
  position: relative;
}
</style>
<form method="post" action="/">
	<div class="row" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
		<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mx-auto" style="padding-bottom:20px;padding-top:0px;">
			
				<input type="hidden" name="type" value="NewTicket">
				<div class="card table-card" style="marsgin-top:-20px;padding:30px;border-radius:6px;"> 
				<h5 style="display:inline">New Ticket</h5>
				<br>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="email">Requester (Email or Name) <span style="color:red">*</span></label>
							<input required type="text"  name="requester" class="form-control" id="email">
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="email">State <span style="color:red">*</span></label>
							<select required name="state" class="form-control" id="pwd">
								<option value="New">New</option>
								<option value="Closed">Closed</option>
							</select>
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label class="control-label" for="pwd">Title <span style="color:red">*</span></label>
							<input required name="title" type="text" class="form-control" id="pwd" placeholder="">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
						<div classs="txtSize" class="form-group">
							<label class="control-label" for="pwd">Description</label>
							<textarea id="trumbowyg-demo"  name="description" class="form-control" id="pwd" placeholder=""></textarea>
							<script>
								$('#trumbowyg-demo').trumbowyg();
							</script>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="email">Category <span style="color:red">*</span></label>
							<select required type="text"  name="category" class="form-control" id="pwd">
								<option></option>
								<option value="Account Management">Account Management</option>
								<option value="Applications">Applications</option>
								<option value="Facilities">Facilities</option>
								<option value="Finance">Finance</option>
								<option value="General Inquiries">General Inquiries</option>
								<option value="Hardware">Hardware</option>
								<option value="Human Resources">Human Resources</option>
								<option value="Networking">Networking</option>
								<option value="Other">Other</option>
							</select>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="pwd">Tags</label>
							<input type="text" value="" name="tags" class="form-control" style="border:#707070" data-role="tagsinput" />
							<!--<select type="text"  name="subcategory" class="form-control" id="pwd">
								<option></option>
							</select>-->
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="email">Assigned to </label>
							<select type="text"  name="assigned" class="form-control" id="pwd">
								<option></option>
								<?php
									$query = "SELECT ID,hex,nicename FROM users WHERE active='1' ORDER BY ID ASC";
									$results = mysqli_query($db, $query);
									while($result = mysqli_fetch_assoc($results)){ 
										$json = getComputerData($result['ID'], array("general"));
										$hostname = textOnNull($json['General']['Response'][0]['csname'],"Unavailable");		
									?>
										<option value='<?php echo $result['ID'];?>'><?php echo crypto('decrypt',$result['nicename'],$result['hex']);?></option>
									<?php }?>
							</select>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="pwd">Priority</label>
							<select type="text"  name="priority" class="form-control" id="pwd">
								<option value="None">None</option>
								<option value="Low">Low</option>
								<option value="Medium">Medium</option>
								<option value="High">High</option>
								<option value="Critical">Critical</option>
							</select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
						<div style="display:inline" class="form-group">
							<label for="email">Due at </label>
							<input style="width:66%"  name="due" type="date" class="form-control" id="pwd" placeholder="">
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label" for="pwd">CC</label>
					<input style="width:66%"  name="cc" type="text" class="form-control" id="pwd" placeholder="">
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="email">Asset </label>
							<select type="text"  name="asset" class="form-control" id="pwd">
								<option></option>
								<?php
									$query = "SELECT ID FROM computers WHERE active='1' ORDER BY ID ASC";
									$results = mysqli_query($db, $query);
									while($result = mysqli_fetch_assoc($results)){ 
										$json = getComputerData($result['ID'], array("general"));
										$hostname = textOnNull($json['general']['Response'][0]['csname'],"Unavailable");		
									?>
										<option value='<?php echo $result['ID'];?>'><?php echo $hostname;?></option>
								<?php }?>
							</select>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<div style="display:inline" class="form-group">
							<label for="pwd"><?php echo $msp; ?></label>
							<select type="text" name="company" class="form-control" id="pwd">
								<option></option>
								<?php
									$query = "SELECT ID, name,hex FROM companies WHERE active='1' ORDER BY ID ASC";
									$results = mysqli_query($db, $query);
									while($result = mysqli_fetch_assoc($results)){ 
										if($result['ID']==$company['ID']){continue;}		
								?>
										<option value='<?php echo $result['ID'];?>'><?php echo crypto('decrypt',$result['name'],$result['hex']);?></option>
								<?php }?>
							</select>
						</div>
					</div>
				</div>
		</div>		
	</div>	
	<div style="bottom:0;position:fixed;float:right;width:100%;background:#fff;border-top:1px solid #d3d3d3d3;padding:10px;margin-left:-15px;z-index:1;overflow:hidden">
		<center>
			<button onclick="loadSection('ServiceDesk');" style="width:100px" class="btn btn-light btn-sm">Cancel</button>
			<button type="submit" style="width:100px" class="btn btn-primary btn-sm">Create</button>
		</center>
	</div>	
</form>

<script>
$('#dataTable').DataTable( {
	"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
	colReorder: true,
	dom: 'Bfrtip'


} );	

</script>
<script type='text/javascript'>
 $(document).ready(function(){
   // Check or Uncheck All checkboxes
   $("#checkall").change(function(){
     var checked = $(this).is(':checked');
     if(checked){
       $(".checkbox").each(function(){
         $(this).prop("checked",true);
       });
     }else{
       $(".checkbox").each(function(){
         $(this).prop("checked",false);
       });
     }
   });
 
  // Changing state of CheckAll checkbox 
  $(".checkbox").click(function(){
 
    if($(".checkbox").length == $(".checkbox:checked").length) {
      $("#checkall").prop("checked", true);
    } else {
      $("#checkall").prop("checked", false);
    }

  });
});
</script>
<?php if($_GET['other']!=""){
?>
<script>
	$('input[type=search]').val('<?php echo clean(base64_decode($_GET['other'])); ?>');
	$('input[type=search]').trigger('keyup');
</script>
<?php
}
?>
<script src="assets/js/tagsinput.js"></script>