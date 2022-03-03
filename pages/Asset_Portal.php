<?php 
$computerID = base64_decode($_GET['ID']); 
//checkAccess($_SESSION['page'],$computerID);

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
$date = strtotime($json['general_lastUpdate']);
if($date < strtotime('-1 days')) {
	$online="0";
}
if($_SERVER['HTTP_X_FORWARDED_FOR'] != $json['general']['Response'][0]['ExternalIP']["ip"]){
	//	 exit("<center><br><br><h5>Sorry, you do not have permission to access this page!</h5><p>If you believe this is an error please contact a site administrator.</p><hr><a href='#' onclick='loadSection(\"Dashboard\");' style='background:#0c5460;color:".$siteSettings['theme']['Color 2']."' class='btn btn-sm'>Back To Dashboard</a></center><div style='height:100vh'>&nbsp;</div>");					
}
?>
<style>
	.btnActive { background:#343a40; color:#fff; }
</style>
<div class="jumbotron jumbotron-fluid shadow p-3 mb-5 rounded" style="background:#35384e;color:#fff;margin:0;padding:10px;border-radius:10px;margin-top:-10px">
	<div  class="row">
		<div class="col-md-6">
			<h2 style="margin-top:50px;font-family: Arial, Helvetica, sans-serif;font-size:40px" >Welcome to our<br>Online Helpdesk</h2>
			<p>Find solutions to common problems, or get help from a support agent.</p>
		</div>
		<div class="col-md-6" >
			<div class="row justify-content-md-center" style="padding:35px">
				<div class="col-md-5 bg-primary secbtn" onclick="$('#chat').show();$('#ticket').hide();loadChat('<?php echo $computerID; ?>');$('.secbtn').removeClass('btnActive');$(this).addClass('btnActive');$('html, body').animate({ scrollTop: $(document).height() - 700 }, 1200);" style="margin-bottom:10px;cursor:pointer;padding:20px;padding-top:20px;padding-bottom:0px;border-radius:5px;height:200px;overflow-x:auto;margin-right:10px">
					<center>
						<i style="font-size:50px" class="fas fa-comments"></i><br><br>
						<h5>Start Chat</h5><br>
						<p>Have a quick question? Get help from a support agent.</p>
				</div>
				<div class="col-md-5 bg-warning secbtn" onclick="$('#ticket').show();$('#chat').hide();$('.secbtn').removeClass('btnActive');$(this).addClass('btnActive');$('html, body').animate({ scrollTop: $(document).height() - 1000 }, 1200);" style="cursor:pointer;padding:20px;padding-top:20px;padding-bottom:0px;border-radius:5px;height:200px;overflow-x:auto;margin-right:10px">
					<center>
						<i style="font-size:50px" class="fas fa-ticket-alt"></i><br><br>
						<h5>Create Ticket</h5><br>
						<p>Have an issue with you computer? Submit a ticket.</p>
					</center>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-md-center" id="ticket" style="display:none;margin-bottom:10px;border-radius:3px;overflow:hidden;padding:15px">
	<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="padding-bottom:20px;">
		<form action="/" method="POST">
			<input type="hidden" name="type" value="NewTicket">
			<input type="hidden" name="asset" value="<?php echo $computerID; ?>">
			<div class="card table-card" style="padding:30px;border-radius:6px;"> 
			<h5 style="display:inline">New Helpdesk Ticket</h5>
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
				<button type="submit" style="width:150px;float:right" class="btn btn-sm btn-primary">Send Ticket &nbsp;<i class="fas fa-paper-plane"></i></button>
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
								<span class="input-group-text"><i class="fas fa-envelope"></i></span>
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