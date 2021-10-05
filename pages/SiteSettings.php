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
	$settings = new RecursiveIteratorIterator(
	new RecursiveArrayIterator($siteSettings),
	RecursiveIteratorIterator::SELF_FIRST);
	$count=0;
	foreach ($settings as $key => $val) {
		if(is_array($val)) {
			$count=0;
			$text .= "<h6>$key:</h6>";
		} else {
			$count++;
			if($count>1){ $text .= ", "; }
			$text .= "<p>$key => $val</p>";			
		}
	}
?>
<style>
	div.editable {
    border: 1px solid #333;
    padding: 5px;
	}

	strong {
	  font-weight: bold;
	}
	p {margin-left:20px; display:inline}
	h6 {padding-top:10px;font-size:18px}
</style>
<div style="margin-top:20px;padding:15px;margin-bottom:30px;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;border-radius:6px;" class="card card-sm">
	<h4 style="color:<?php echo $siteSettings['theme']['Color 1']; ?>">Site Settings
		<a href="#" title="Refresh" onclick="loadSection('SiteSettings');" class="btn btn-sm" style="float:right;color:#fff;background:<?php echo $siteSettings['theme']['Color 1'];?>;">
			<i class="fas fa-sync"></i>
		</a>
		<a href="#"  data-toggle="modal" data-target="#agentUpload" style="margin-right:5px;display:inline;float:right;background:#fe9365;color:#fff;" class="btn btn-sm">
			<i class="fas fa-upload"></i> Upload Agent
		</a>
	</h4>
</div>
<div class="row">
	<div class="col-md-9">  
		<div class="card table-card panel" id="printTable">
		    <div class="panel-heading">
              <span class="panel-title">Configure Site Settings</span>
            </div>
			<form method="POST" action="index.php" style="padding:10px">
				<input type="hidden" name="type" value="saveSiteSettings"/>
				<div contenteditable="true"><?php echo $text;  ?></div>	

				<div style="margin-top:30px;" class="form-group float-label-control">                 
					<input style="float:right;border:none;width:200px;background:<?php echo $siteSettings['theme']['Color 4']; ?>;color:#fff;" type="submit" class="btn btn-sm" value="Save Details"/>
				</div>
			</form>
		</div>
	</div>
	<div class="col-md-3">  
		<div class="card table-card panel">
		<form method="POST" action="index.php">
	        <div class="panel-heading">
              <span class="panel-title">Sitewide Alert</span>
			
            </div>
			<div style="padding:10px;" class="form-group float-label-control">  
				<textarea placeholder="What Is The Alert For Every Technician" rows=5 class="form-control"></textarea>			
				<center><input style="margin-top:20px" type="submit" class="btn btn-warning btn-sm" value="Save Details"/></center>
			</div>
		</form>
		</div>
	</div>
</div>
<script>
	function getSiteSettings(){
		var retdata;
		$.post("index.php", {
		  type: "getSiteSettings",
		},
		function(data, status){
		  $("#siteSettingsTextArea").val(prettyJson(data));
		});	
		return retdata;
	}
	
	function prettyJson(json) {
		var ugly = json
		var obj = JSON.parse(ugly);
		var pretty = JSON.stringify(obj, undefined, 4);
		return pretty;
	}

	getSiteSettings();
</script>