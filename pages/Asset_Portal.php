<?php 
$computerID = base64_decode($_GET['ID']); 
checkAccess($_SESSION['page'],$computerID);

$query = "SELECT online, ID, company_id, name, phone, email,hex, computer_type FROM computers WHERE ID='".$computerID."' LIMIT 1";
$results = mysqli_query($db, $query);
$result = mysqli_fetch_assoc($results);

$query = "SELECT name, phone, email,address,comments,date_added,hex,owner FROM companies WHERE ID='".$result['company_id']."' LIMIT 1";
$companies = mysqli_query($db, $query);
$company = mysqli_fetch_assoc($companies);

$getWMI = array("general","screenshot","logical_disk","bios","processor","agent","battery","windows_activation","agent_log","firewall","okla_speedtest");
$json = getComputerData($result['ID'], $getWMI);

//print_r($json['agent_log']);
$hostname = textOnNull($json['general']['Response'][0]['csname'],"Unavailable");
$online = $result['online'];

if($_SERVER['HTTP_X_FORWARDED_FOR'] != $json['general']['Response'][0]['ExternalIP']["ip"]){

//	 exit("<center><br><br><h5>Sorry, you do not have permission to access this page!</h5><p>If you believe this is an error please contact a site administrator.</p><hr><a href='#' onclick='loadSection(\"Dashboard\");' style='background:#0c5460;color:".$siteSettings['theme']['Color 2']."' class='btn btn-sm'>Back To Dashboard</a></center><div style='height:100vh'>&nbsp;</div>");					
}
?>
<style>
	.btnActive { background:#343a40; color:#fff; }
</style>
<div class="jumbotron jumbotron-fluid" style="background:#35384e;color:#fff;margin:0">
  <div style="height:100px;margin-top:-30px" class="container">
	<h1 style="font-family: Arial, Helvetica, sans-serif;font-size:40px" >Welcome to our<br>Online Helpdesk</h1>
	<p>View basic information about your computer. Find solutions to common problems, or get help from a support agent.</p>
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

<div class="row justify-content-md-center" style="padding:35px;">
<?php if($computerID!="0"){ ?>
	<div class="col-md-2 bg-success secbtn btnActive" onclick="$('#asset').show();$('#chat').hide();$('#ticket').hide();$('.secbtn').removeClass('btnActive');$(this).addClass('btnActive');" style="cursor:pointer;padding:20px;padding-top:20px;padding-bottom:0px;border-radius:5px;height:200px;overflow-x:auto;margin-right:10px">
		<center>
			<i style="font-size:50px" class="fas fa-desktop"></i><br><br>
			<h5>Asset Details</h5><br>
			<p>View basic information about your computer</p>
		</center>
	</div>	
<?php } ?>
	<div class="col-md-2 bg-primary secbtn" onclick="$('#chat').show();$('#asset').hide();$('#ticket').hide();loadChat('<?php echo $computerID; ?>');$('.secbtn').removeClass('btnActive');$(this).addClass('btnActive');" style="cursor:pointer;padding:20px;padding-top:20px;padding-bottom:0px;border-radius:5px;height:200px;overflow-x:auto;margin-right:10px">
		<center>
			<i style="font-size:50px" class="fas fa-comments"></i><br><br>
			<h5>Start Chat</h5><br>
			<p>Have a quick question? Get help from a support agent.</p>
	</div>
	<div class="col-md-2 bg-warning secbtn" onclick="$('#ticket').show();$('#chat').hide();$('#asset').hide();$('.secbtn').removeClass('btnActive');$(this).addClass('btnActive');" style="cursor:pointer;padding:20px;padding-top:20px;padding-bottom:0px;border-radius:5px;height:200px;overflow-x:auto;margin-right:10px">
		<center>
			<i style="font-size:50px" class="fas fa-ticket-alt"></i><br><br>
			<h5>Create Ticket</h5><br>
			<p>Have an issue with you computer? Submit a ticket.</p>
		</center>
	</div>
	</div>
</div>
<?php if($computerID!="0"){ ?>
<div id="asset" style="width:100%;backgrdound:#fff;padding:15px;">
	<form method="POST" action="/">
		<div class="row justify-content-md-center" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
			<div class="col-xs-12 col-sm-12 col-md-10 col-lg-10" style="padding:5px;padding-bottom:20px;padding-top:0px;border-radius:6px;">	
				<div class="card table-card" style="margin-top:0px;padding:10px">  
					<div class="card-header">
						<h4><?php echo $hostname; ?></h4>
					</div>
					<div style="margin-top:-40px" class="card-body row">
						<div class="col-xs-6 col-sm-6 col-md-3 col-lg-4" style="padding:5px;">
							<div class="panel panel-default" style="z-index:999">
								<div class="panel-heading">
									<h5  style="padding:7px" class="panel-title">
										Asset Overview
									</h5>
								</div>
								<div class="panel-body" style="height:285px;">
									<div class="rsow">
										<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
											<a href="javascript:void(0)" style="color:<?php echo $siteSettings['theme']['Color 5']; ?>" data-bs-toggle="modal" data-bs-target="#companyMoreInfo">
												<h5>
													<?php echo crypto('decrypt',$result['name'],$result['hex'])!="" ? ucwords(crypto('decrypt',$result['name'],$result['hex']))." at" : ""; ?>
													<?php echo textOnNull((crypto('decrypt',$company['name'],$company['hex'])!="N/A" ? crypto('decrypt',$company['name'],$company['hex']) : ""), "No ".$msp." Name"); ?>
												</h5>
											</a>
											<span style="color:#666;font-size:14px;"><?php echo textOnNull(phone(crypto('decrypt',$result['phone'],$result['hex'])), "No Phone"); ?> &bull;
												<a href="mailto:<?php echo crypto('decrypt', $result['email'],$result['hex']); ?>">
													<?php echo textOnNull(phone(crypto('decrypt',$result['email'],$result['hex'])), "No Email"); ?>
												</a>
											</span>
										</div>
									</div>
							</div>
						</div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h5  style="padding:7px" class="panel-title">
									Asset Details
								</h5>
							</div>
							<div class="panel-body" style="height:285px;">	
								<div class="roaw">
									<ul class="list-group" style="margin-left:10px">
										<li class="list-group-item  olderdata" style="z-index:2;padding:6px;width:100%"><b>Processor: </b><?php echo textOnNull(str_replace(" 0 ", " ",str_replace("CPU", "",str_replace("(R)","",str_replace("(TM)","",$json['processor']['Response'][0]['Name'])))), "N/A");?></li>
										<li class="list-group-item  olderdata" style="padding:6px"><b>Operating System: </b><?php echo textOnNull(str_replace("Microsoft", "", $json['general']['Response'][0]['Caption']), "N/A");?></li>
										<li class="list-group-item  olderdata" style="padding:6px"><b>Architecture: </b><?php echo textOnNull(str_replace("PC", "",$json['general']['Response'][0]['SystemType']), "N/A");?></li>
										<li class="list-group-item  olderdata" style="padding:6px"><b>BIOS Version: </b><?php echo textOnNull($json['bios']['Response'][0]['Version'], "N/A");?></li>
										<li class="list-group-item  olderdata" style="padding:6px"><b>Public IP Address: </b><?php echo textOnNull($json['general']['Response'][0]['ExternalIP']["ip"], "N/A");?></li>
										<li class="list-group-item  olderdata" style="padding:6px"><span style="margin-left:0px"><b>Local IP Address: </b><?php echo textOnNull($json['general']['Response'][0]['PrimaryLocalIP'], "N/A");?></span></li>
										<?php if(count($json['windows_activation']['Response']) > 0) {
											$status = $json['windows_activation']['Response'][0]['LicenseStatus'];
											if($status!="Licensed")$status="Not activated";
											$color = ($status == "Licensed" ? "text-success" : "text-danger");
										?>
											<li class="list-group-item  olderdata" style="padding:6px"><b>Windows Activation: </b><span class="<?php echo $color; ?>"><?php echo textOnNull($status, "N/A");?></span></li>
										<?php } 
										if((int)$json['battery']['Response'][0]['BatteryStatus']>0){ ?>
										<li class="list-group-item  olderdata" style="padding:6px"><b>Battery Status: </b><?php 								
											$statusArray = [
											"1" => ["Text" => "Discharging", "Color" => "red"],
											"2" => ["Text" => "Unknown", "Color" => "red"],
											"3" => ["Text" => "Fully Charged", "Color" => "green"],
											"4" => ["Text" => "Low", "Color" => "red"],
											"5" => ["Text" => "Critical", "Color" => "red"],
											"6" => ["Text" => "Charging", "Color" => "green"],
											"7" => ["Text" => "Charging And High", "Color" => "green"],
											"8" => ["Text" => "Charging And Low", "Color" => "green"],
											"9" => ["Text" => "Charging And Critical", "Color" => "yellow"],
											"10" =>["Text" => "Undefined", "Color" => "red"],
											"11" =>["Text" => "Partially Charged", "Color"=>"yellow"]];
											$statusInt = $json['battery']['Response'][0]['BatteryStatus'];						
										?>
										<?php echo textOnNull($json['battery']['Response'][0]['EstimatedChargeRemaining'], "Unknown");?>%
										(<span style="color:<?php echo $statusArray[$statusInt]['Color']; ?>"><?php echo $statusArray[$statusInt]['Text']; ?></span>)	
										</li>
										<?php } ?>
									</ul>
								</div>
						</div>
						</div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-4 col-lg-4" style="padding:3px;">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h5 style="padding:7px" class="panel-title">
									
								</h5>
							</div>
							<div class="panel-body" style="height:285px;">
								<div class="">
									<ul class="list-group" style="margin-left:20px">
										<li class="list-group-item  olderdata" style="z-index:2;padding:6px;width:100%"><b>Current User: </b><?php echo textOnNull(basename($json['general']['Response'][0]['UserName']), "Unknown");?></li>
										<li class="list-group-item  olderdata" style="z-index:2;padding:6px;width:100%"><b>Domain: </b><?php echo textOnNull($json['general']['Response'][0]['Domain'], "N/A");?></li>
										<?php
											$lastBoot = explode(".", $json['general']['Response'][0]['LastBootUpTime'])[0];
											$cleanDate = date("m/d/Y h:i A", strtotime($lastBoot));
										?>
										<li class="list-group-item  olderdata" style="z-index:2;padding:6px;width:100%"><b>Uptime: </b><?php if($lastBoot!=""){ echo str_replace(" ago", "", textOnNull(ago($lastBoot), "N/A")); }else{ echo"N/A"; }?></li>
										<?php if(count($json['firewall']) > 0) {

											$public = $json['firewall']['Response'][0]['publicProfile'];
											//if($public=="OFF"){ $public="Disabled"; }else{ $public="Enabled"; }
											$color1 = (($public == "Enabled") ? "text-success" : "text-danger");

											$private = $json['firewall']['Response'][0]['privateProfile'];
											//if($private=="OFF"){ $private="Disabled"; }else{ $private="Enabled"; }
											$color2 = (($private == "Enabled") ? "text-success" : "text-danger");

											$domain = $json['firewall']['Response'][0]['domainProfile'];
											//if($domain=="OFF"){ $domain="Disabled"; }else{ $domain="Enabled"; }
											$color3 = (($domain == "Enabled") ? "text-success" : "text-danger");
										?>
											<li id="Firewall" class="list-group-item olderdata" style="z-index:2;padding:6px;width:100%"><b>Firewall Status: </b><br>
												<center>
													<span style="margin-left:20px">
														Public: <span style="padding-right:20px" class="<?php echo $color1; ?>"><?php echo $public; ?></span>
													</span>
													<span>
														Private: <span style="padding-right:20px" class="<?php echo $color2; ?>"><?php echo $private; ?></span>
													</span>
													<span>
														Domain: <span class="<?php echo $color3; ?>"><?php echo $domain; ?></span>
												</span>
												</center>
											
											</li>
										<?php } 
										
										if(count($json['general']['Response'][0]['Antivirus']) > 0) {
											$status = $json['general']['Response'][0]['Antivirus'];
											$color = ($status == "No Antivirus" ? "text-danger" : "text-success");
										?>
											<li class="list-group-item  olderdata" style="z-index:2;padding:6px;width:100%"><b>Antivirus: </b><span title="<?php echo textOnNull($status, "N/A"); ?>" class="<?php echo $color; ?>"><?php echo mb_strimwidth(textOnNull($status, "N/A"), 0, 30, "...");?></span></li>
										<?php } ?>
										<li class="list-group-item  olderdata" style="z-index:2;padding:6px;width:100%" title="Path: <?php echo $json['agent']['Response'][0]['Path']; ?>"><b>Agent Version: </b><?php echo textOnNull($json['agent']['Response'][0]['Version'],"N/A"); ?></li>
									</ul>
								</div>
						</div>
						</div>
					</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<?php } ?>
<div class="row justify-content-md-center" id="ticket" style="display:none;margin-bottom:10px;border-radius:3px;overflow:hidden;padding:15px">
	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="padding-bottom:20px;">
		<form action="/" method="POST">
			<input type="hidden" name="type" value="NewTicket">
			<input type="hidden" name="asset" value="<?php echo $computerID; ?>">
			<div class="card table-card" style="marsgin-top:-20px;padding:30px;border-radius:6px;"> 
			<h5 style="display:inline">New Ticket</h5>
			<br>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-4 col-lg-6">
					<div style="display:inline" class="form-group">
						<label for="email">Requester (Email or Name) <span style="color:red">*</span></label>
						<input required type="text"  name="requester" class="form-control" id="email">
					</div>
				</div>
				<div class="col-xs-12 col-sm-12 col-md-4 col-lg-6">
					<div style="display:inline" class="form-group">
						<label class="control-label" for="pwd">Title <span style="color:red">*</span></label>
						<input required name="title" type="text" class="form-control" id="pwd" placeholder="">
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
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
				<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
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
			</div>
			<br>			
			<div style="float:right" class="">
				<button type="submit" style="width:150px" class="btn btn-sm btn-primary">Send Ticket &nbsp;<i class="fas fa-paper-plane"></i></button>
			</div>		
			</div>
			</form>
		</div>		
	</div>
</div>
<div id="chat" style="display:none;width:100%;padding:15px;">

		<div class="row justify-content-md-center" style="margin-bottom:10px;margin-top:0px;border-radius:3px;overflow:hidden;padding:0px">
			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="padding:5px;padding-bottom:20px;padding-top:0px;border-radius:6px;">	
				<div class="card table-card" style="margin-top:0px;padding:10px">  
					<div class="card-header"><br>
						<h4>Chat with a Technician</h4>
					
						<hr>
					</div>
					<div class="card-body ">
				
		
					</div>
					<div class="chat">

						<div style="height:300px;overflow-y:auto;" id="chatDiv2" class="chat-history">
							<div id="chatDiv">
							
							
									<br>
									<center>
										<h6>
										<div class='spinner-grow text-muted'></div><div class='spinner-grow' style='color:#0c5460'></div><br> Loading. Please wait!
										</h6>
									</center>
									
							</div>
							
						</div>
						<div class="chat-message clearfix">

							<div class="input-group mb-0">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fas fa-paper-plane"></i></span>
								</div>
								<input type="hidden" name="ID" id="asset_message_id"  value="<?php echo $computerID; ?>">  
								<input type="text" id="asset_message2" required class="form-control" name="message" placeholder="Enter text here...">   
								<button onclick="sendChat();" class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i> &nbsp;Send</button>                      
							</div>

						</div>
				</div>
			</div>
		</div>
</div>
<script>
	$(document).ready(function() {
		$('#<?php echo $_SESSION['userid']; ?>Users').dataTable( {
			colReorder: true,
			stateSave: true
		} );
	});
</script>
<script>
	<?php if($online=="0"){ ?>
		toastr.remove();
		toastr.error('This computer appears to be offline. Some data shown may not be up-to-date or available.');
	<?php } ?>
	var textarea = $('#asset_message2');
		var lastTypedTime = new Date(0); // it's 01/01/1970
		var typingDelayMillis = 5000;

		function refreshTypingStatus() {
  			 if (textarea.val() == '' || new Date().getTime() - lastTypedTime.getTime() > typingDelayMillis) {
				$.post("index.php", {
					type: "assetChat_typing",
					ID: $("#asset_message_id").val(),
					is_typing: "0",
					userid: "<?php echo (int)$_SESSION['userid']; ?>"					
				});
			} else {
				$.post("index.php", {
					type: "assetChat_typing",
					ID: $("#asset_message_id").val(),
					is_typing:"1",
					userid: "<?php echo (int)$_SESSION['userid']; ?>"
					
				});
			}
		}

		function updateLastTypedTime() {
			lastTypedTime = new Date();
		}

		setInterval(refreshTypingStatus, 100);
		textarea.keypress(updateLastTypedTime);
		textarea.blur(refreshTypingStatus);
</script>
<script src="assets/js/tagsinput.js"></script>