<?php
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
if(isset($_GET['file'])){
	$filename="downloads/".$_GET['file'];
	header("Content-type: application/zip"); 
	header("Content-Disposition: attachment; filename=".$_GET['file']);
	//header("Content-length: ".filesize($filename));
	header("Pragma: no-cache"); 
	header("Expires: 0"); 
	readfile($filename);
}
	require("includes/db.php");

	//$messageTitle = "New Ideas/Bug Fixes";
	//$messageText .= "Add Site Alert For Conflicting Hostnames. <br><br> Version 1.0.1.6, updates are broke";
	if($_SESSION['excludedPages']==""){
		$_SESSION['excludedPages'] = explode(",",$excludedPages); //use this to clear pages if an error occurs
	}
	if(isset($_POST)){
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$_SESSION['updateIgnore'] = $_POST['ignore'];
		if($_SESSION['updateIgnore']=="true"){
			$_SESSION['count']="0";
			array_push($_SESSION['excludedPages'],$_POST['page']);	
			//$_SESSION['updateIgnore']="";
			header("location: /");
		}
		include("includes/post.php");	
	}

	//Get user data
	$query = "SELECT username,nicename,account_type,hex,user_color FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
	$results = mysqli_query($db, $query);
	$user = mysqli_fetch_assoc($results);
	$username=$user['username'];


	if($nologin==false){
		if($_SESSION['userid']=="" && !in_array(basename($_SERVER['SCRIPT_NAME']), $serverPages)){
			if(strpos(strtolower($_SERVER['SCRIPT_NAME']),"/pages/")!==false){ //fix for ajax pages
				echo ("<center><h3>Error loading page, please make you are loged in.</h3></center>");
			}
		}
	}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>OpenRMM | Remote Management</title>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--- Font Awesome --->
		<link rel="stylesheet" href="assets/css/all.min.css"/>
		<script src="assets/js/all.min.js"></script>
		<link rel="icon" href="assets/images/favicon.ico" type="image/ico" sizes="16x16">
		<!-- jquery-->
	

		<!--- Bootstap --->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css"/>
		<link rel="stylesheet" href="assets/css/tagsinput.css"/>
		<link rel="stylesheet" href="assets/css/bootstrap.min.css"/>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
  		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

		<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
			
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.25.1/trumbowyg.min.js"></script>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.25.1/ui/trumbowyg.min.css" rel="stylesheet">
		<link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css" rel="stylesheet">
		
		<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="assets/css/toastr.css"/>
		<link rel="stylesheet" href="assets/css/custom.css"/>
		<link rel="stylesheet" href="assets/css/style.css"/>

		<script src="https://cdn.datatables.net/colreorder/1.5.4/js/dataTables.colReorder.min.js"></script>	
		<link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.5.4/css/colReorder.dataTables.min.css"/>
		
		<script src="https://cdn.datatables.net/fixedheader/3.2.0/js/dataTables.fixedHeader.min.js"></script>	
		<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.0/css/fixedHeader.dataTables.min.css"/>

		<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
		<link href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap4.min.css" rel="stylesheet">
		<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.colVis.min.js"></script>
		<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
		<script src="assets/js/tagsinput.js"></script>
	</head>
	<script>
		var force="";
	</script>
	<style>
		a { color:#003366; }
		.calert { margin-left:5px;font-size:12px;width:44%;margin-right:5px;float:left;min-height:60px }
		@media screen and (max-width: 850px) {
			.calert { height: 120px; }
			.headall { display: none; }
		}
		.secActive {
			background:#d1ecf1!important;
			color:#0c5460!important;
			border-radius:3px;
		}
		.toast{ z-index:999999; }
		.secbtn:hover{
			background:#282828!important;
			color:#fff!important;  
		}
		.dt-button{box-shadow:none;background:#000;color:#fff;padding:5px}
		.buttons-columnVisibility{margin-top:10px;}
	</style>
	<body style="background-color:#E8E8E8;height:100%; position: relative;min-height: 100vh;">
		<div style="z-index:99999;padding:5px;background-color:#fff;color:#fff;text-align:center;padding-top:4px;padding-left:20px;position:fixed;top:0px;width:100%;box-shadow: 0 0 11px rgba(0,0,0,0.13);">
			<h5>
				<div style="float:left;">
					<button type="button" style="display:inline-block;margin-top:2px;border:none;box-shadow:none" class="btn-sm sidebarCollapse btn" title="Show/Hide Sidebar">
						<i style="font-size:16px" class="fas fa-align-left"></i>
					</button>		
					<div style="display:inline-block;">
						<a style="color:#333;font-size:22px;cursor:pointer" onclick="loadSection('<?php if($_SESSION['userid']!=""){ echo "Dashboard"; }else{ echo "Login"; } ?>');" >Open<span style="color:#0c5460">RMM</span></a>
					</div>
				</div>
				<?php if($_SESSION['userid']!=""){ ?>
					<div style="float:right;">
				
						<button type="button" onclick="loadChat('0');"data-toggle="modal" data-target="#asset_message_modal" style="border:none;box-shadow:none;margin-top:4px" class="btn-sm btn" title="Asset Chat">
							<i style="font-size:16px" class="fas fa-comment-dots"></i>
							<span style="font-size:10px" id="messageCount" class="text-white badge bg-c-pink">0</span>
						</button>
						<div class="btn-group">
						
          					<a href="javascript:void(0)" style="border:none;box-shadow:none" class="dropsdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
								<i style="font-size:16px;color:#000" class="fas fa-bell"></i>
								<span style="margin-top" class="text-white badge bg-c-pink"><?php if($messageText==""){ echo "0"; }else{ echo "1"; } ?></span>
							</a>
          					<div class="dropdown-menu">
								<ul style="font-size:12px"  class="list-group">
									<li class="list-group-item">No New Notifications</li>
								</ul>
							</div>
						</div>
						<div class="btn-group">
							&nbsp;
							<a href="javascript:void(0)" class="dropsdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
								<i style="font-size:16px;color:#000" class="fas fa-plus"></i>
							</a>
          					<div class="dropdown-menu">
								<ul style="font-size:12px"  class="list-group">
									<?php if($siteSettings['Service_Desk']=="Enabled"){ ?>
										<li style="cursor:pointer" onclick="loadSection('Service_Desk_New_Ticket');" class="list-group-item secbtn">Create New Ticket</li>
									<?php } 
									if($_SESSION['accountType']=="Admin"){ ?>
										<li style="cursor:pointer" data-toggle="modal" data-target="#companyModal" class="list-group-item secbtn">Add New <?php echo $msp; ?></li>
										<li style="cursor:pointer"  data-toggle="modal" data-target="#userModal" class="list-group-item secbtn">Add New Technician</li>
									<?php } ?>
								</ul>
							</div>
						</div>
							<button type="button" onclick="loadSection('Init','true');" style="border:none;box-shadow:none;margin-top:4px" class="btn-sm btn" title="Configure OpenRMM">
								<i style="font-size:16px" class="fas fa-cog"></i>
							</button>
						</div>
					</div>
				<?php } ?>
			</h5>
		</div>
		<div class="wrapper">
			<!-- Sidebar -->
			<?php if($_SESSION['userid']!=""){ ?>
				<nav style="background:#343a40;z-index:99998;padding-bottom:5%" id="sidebar">
					<ul class="list-unstyled components" style="padding:20px;margin-top:25px;">
						<div style="text-align:left;width:100%">
							<a style="cursor:pointer" onclick="loadSection('Profile','<?php echo $_SESSION['userid']; ?>');">
								<?php
									list($first, $last) = explode(' ', ucwords(crypto('decrypt',$user['nicename'],$user['hex'])), 2);
									$name = strtoupper("$first[0]{$last[0]}"); 
								?>
								<div style="margin-top:-5px;font-size:22px;margin-right:10px;float:left;display:inline;background:<?php echo $user['user_color']; ?>;color:#fff;padding:5px;border-radius:100px;text-align:center;width:50px;height:50px;padding-top:9px">
									<?php echo $name; ?>
								</div>
								<h6 style="color:#fff;margin-top:10px"><?php echo ucwords(crypto('decrypt',$user['nicename'],$user['hex'])); ?></h6>
							</a>
							<a onclick="loadSection('Profile','<?php echo $_SESSION['userid']; ?>');"  style="cursor:pointer;color:#d3d3d3">Profile</a>
							<span style="color:#fff"> &#8226; </span> 					
							<a onclick="loadSection('Logout');" style="color:<?php echo $siteSettings['theme']['Color 2']; ?>;cursor:pointer">Logout</a>
							<hr>
						</div>					
						<li onclick="loadSection('Dashboard');" id="secbtnDashboard" class="secbtn">
							<i class="fas fa-home"></i>&nbsp;&nbsp;&nbsp; Dashboard
						</li>
						<li onclick="loadSection('Assets');" id="secbtnAssets" class="secbtn">
							<i class="fa fa-desktop" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp; Assets <span id="assetCount" style="float:right;margin-top:3px;" class="badge badge-secondary"><?php echo (int)$assetCount; ?></span>
						</li>
						<?php if($siteSettings['Service_Desk']=="Enabled"){ ?>
						<li id="secbtnService_Desk_Home" onclick="loadSection('Service_Desk_Home');" class="secbtn">
							<i class="fa fa-ticket-alt" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Service Desk <span id="ticketCount" style="float:right;margin-top:3px;" class="badge badge-secondary"><?php echo (int)$ticketCountAll; ?></span>
						</li>
						<?php } ?>
						<li class="secbtn">
							<h6 style="color:#d3d3d3" data-toggle="collapse" data-target="#navConfig"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;&nbsp;Configuration <i class="fa fa-angle-down" aria-hidden="true"></i></h6>
						</li>
						<ul style="margin-left:20px" class="nav nav-list collapse" id="navConfig">
							<?php if($_SESSION['accountType']=="Admin"){ ?>
								<li onclick="loadSection('Customers');" id="secbtnCustomers" style="width:100%" class="secbtn">
									<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;<?php echo $msp; ?>s
								</li>
								<li onclick="loadSection('Technicians');" id="secbtnTechnicians" style="width:100%" class="secbtn">
									<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Technicians
								</li>
							<?php } ?>
								<li onclick="loadSection('Versions');" id="secbtnVersions" class="secbtn" style="width:100%">
									<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Downloads
								</li>
							<?php if($_SESSION['accountType']=="Admin"){ ?>
								<!--li onclick="loadSection('SiteSettings');" id="secbtnSiteSettings" style="width:100%" class="secbtn">
									<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Settings
								</li-->
							<?php } ?>
						</ul>
						<hr style="background:#dedede" >
						<div id="sectionList" style="display:none;">
							<h5 class="sidebarComputerName"></h5>
							<hr>
							<li onclick="loadSection('Asset_General');" id="secbtnAsset_General" class="secbtn">
								<i class="fas fa-stream"></i>&nbsp;&nbsp;&nbsp; Asset Overview
							</li>
							<hr>
							<h6 class="">Tools</h6>
							<li onclick="loadSection('Asset_Commands');" id="secbtnAsset_Commands" class="secbtn">
								<i class="fas fa-terminal"></i>&nbsp;&nbsp;&nbsp; Commands
							</li>
							<li onclick="loadSection('Asset_Alerts');" id="secbtnAsset_Alerts" class="secbtn">
								<i class="fas fa-bell"></i>&nbsp;&nbsp;&nbsp; Alerts
							</li>
							<li onclick="loadSection('Asset_Event_Logs');" id="secbtnAsset_Event_Logs" class="secbtn">
								<i class="fas fa-file-code"></i>&nbsp;&nbsp;&nbsp; Event Logs
							</li>
							<!-- li onclick="loadSection('Registry');" id="secbtnRegistry" class="secbtn">
								<i class="fas fa-cubes"></i>&nbsp;&nbsp;&nbsp; Registry
							</li -->
							<li onclick="loadSection('Asset_File_Manager');" id="secbtnAsset_File_Manager" class="secbtn">
								<i class="fas fa-folder"></i>&nbsp;&nbsp;&nbsp; File Manager
							</li>
							<hr>
							<h6 class="">Asset Details</h6>
							<li onclick="loadSection('Asset_Network');" id="secbtnAsset_Network" class="secbtn">
								<i class="fas fa-network-wired"></i>&nbsp;&nbsp;&nbsp; Network
							</li>
							<li onclick="loadSection('Asset_Programs');" id="secbtnAsset_Programs" class="secbtn">
								<i class="fab fa-app-store-ios"></i>&nbsp;&nbsp;&nbsp; Programs
							</li>
							<!--li onclick="loadSection('DefaultPrograms');" id="secbtnDefaultPrograms" class="secbtn">
								<i class="fab fa-app-store-ios"></i>&nbsp;&nbsp;&nbsp; Default Programs
							</li-->
							<li onclick="loadSection('Asset_Services');" id="secbtnAsset_Services" class="secbtn">
								<i class="fas fa-cogs"></i>&nbsp;&nbsp;&nbsp; Services
							</li>
							<li onclick="loadSection('Asset_Processes');" id="secbtnAsset_Processes" class="secbtn">
								<i class="fas fa-microchip"></i>&nbsp;&nbsp;&nbsp; Processes
							</li>
							<li onclick="loadSection('Asset_Printers');" id="secbtnAsset_Printers" class="secbtn">
								<i class="fas fa-edit"></i>&nbsp;&nbsp;&nbsp; Printers
							</li>
							<li onclick="loadSection('Asset_Disks');" id="secbtnAsset_Disks" class="secbtn">
								<i class="fas fa-hdd"></i>&nbsp;&nbsp;&nbsp; Disks
							</li>
							<li onclick="loadSection('Asset_Memory');" id="secbtnAsset_Memory" class="secbtn">
								<i class="fas fa-memory"></i>&nbsp;&nbsp;&nbsp; Memory
							</li>
							<li onclick="loadSection('Asset_Attached_Devices');" id="secbtnAsset_Attached_Devices" class="secbtn">
								<i class="fab fa-usb"></i>&nbsp;&nbsp;&nbsp; Attached Devices
							</li>
							<li onclick="loadSection('Asset_Optional_Features');" id="secbtnAsset_Optional_Features" class="secbtn">
								<i class="fas fa-list"></i>&nbsp;&nbsp;&nbsp; Optional Features
							</li>
							<li onclick="loadSection('Asset_Users');" id="secbtnAsset_Users" class="secbtn">
								<i class="fas fa-users"></i>&nbsp;&nbsp;&nbsp; User Accounts
							</li>
							<li></li>
						</div>				
						<div class="recents" id="recents" style="margin-top:20px;"></div>							
						<div style="height:500px">&nbsp;</div>		
					</ul>
					<div style="padding:5px;color:#696969;bottom:0" class="footer-copyright text-center">
						<center>
							© <?php echo date('Y');?> Copyright
							<a style="color:#696969;" target="_blank" href="https://github.com/OpenRMM"> OpenRMM</a><br>
							<a style="font-size:12px;cursor:pointer;color:#696969" onclick="loadSection('Versions');"><u>Agent Downloads</u></a>
						</center>
					</div>
				</nav>
			<?php } ?>
			<!-- Page Content -->
			<div id="content" class="containerLeft" style="margin-top:15px;padding:10px;width:100%;">	
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-top:20px;">
						<div class="loadSection">
							<!------ Loads main data from jquery ------>
							<center>
								<h3 style='margin-top:40px;'>
									<div class='spinner-grow text-muted'></div>
									<div class='spinner-grow' style='color:#0c5460'></div>
									<div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 3']; ?>'></div>
									<div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 4']; ?>'></div>
									<div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 5']; ?>'></div>
									<div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div>
									<div class='spinner-grow text-light'></div>
								</h3>
							</center>
							
						</div>
						<div style="height:50px;" class="clearfix">&nbsp;</div>						
					</div>
					<footer style="display:none;" class="page-footer font-small black">
						<div class="footer-copyright text-center">© <?php echo date('Y');?> Copyright
							<a style="color:#fff;" target="_blank" href="https://github.com/OpenRMM"> OpenRMM</a>
							<a style="font-size:12px;cursor:pointer;float:left;padding-right:10px;color:#fff" onclick="loadSection('Versions');"><u>Previous Agent Versions</u></a>
						</div>
					</footer>
				</div>
			</div>
		</div>
		<?php 
		if($_SESSION['userid']!=""){
			require("includes/modals.php"); 
		?>
			<div id="notifications"> </div>
			<script>
			setInterval(function(section=currentSection, ID=computerID, date=sectionHistoryDate,other=otherEntry) {
				$("#notifications").load("includes/notifications.php?ID="+btoa(ID)+"&Date="+btoa(date)+"&page="+btoa(section)+"&other="+btoa(other));	
			}, 5000);
			</script>
		<?php } ?>
	</body>
	<script src="assets/js/extra.js" ></script>
	<script src="assets/js/toastr.js"></script>
	<script src="assets/js/custom.js"></script>
	<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

	<script>
		var computerID = atob(getCookie("ID"));
		var currentSection = atob(getCookie("section"));
		var sectionHistoryDate = "latest";
		$( document ).ready(function() {
        	//$("#sortable").sortable();
        	//$("#sortable").disableSelection();
		});
		//Load Page
		if (document.cookie.indexOf('section') === -1 ) {
			setCookie("section",  btoa("Login"), 365);
		}
		//Load Pages
		var otherEntry = "";
		function loadSection(section=currentSection, ID=computerID, date=sectionHistoryDate,other=otherEntry){
		document.cookie = "section=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
		document.cookie = "ID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
		var loadSection="";
		
		setCookie("section", btoa(section), 365);
		$('.secbtn').removeClass('secActive');
		setCookie("ID", btoa(ID), 365);
	
		computerID = ID;
		currentSection = section;
			
		if(section=="Logout"){
			toastr.options.progressBar = true;
			toastr.warning('Securely Logging You Out.');
			$(".loadSection").load("includes/loader.php?page="+btoa(section));
			setCookie("section", btoa("Login"), 365);
			setTimeout(function() { 
				location.reload(true);
			}, 5000);
		}else{
			if(section=="Asset_File_Manager" || force=="true"){
				$(".sidebarComputerName").text("");
				$(".loadSection").html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow' style='color:#0c5460'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 3']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 4']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 5']; ?>'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3><div class='fadein row col-md-6 mx-auto'><div class='card card-md' style='margin-top:100px;padding:20px;width:100%'><center> <h5>We are getting the latest information for this asset</h5><br><h6>Instead of waiting, would you like to display the outdated assset data?</h6><br><form method='post'><input value='true' type='hidden' name='ignore'><input value='"+section+"' type='hidden' name='page'><button class='btn btn-sm btn-warning' style='background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none;color:#0c5460' type='submit'>View Older Asset Information <i class='fas fa-arrow-right'></i></button></form> <center></div></div>");
			}else{
				$(".loadSection").html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow' style='color:#0c5460'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 3']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 4']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 5']; ?>'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3>");
			}
		
			$("html, body").animate({ scrollTop: 0 }, "slow"); 
			$.ajax({
				url: "includes/loader.php?ID="+btoa(ID)+"&Date="+btoa(date)+"&page="+btoa(section)+"&other="+btoa(other),
				timeout: 60000,
				success: function(data) {
					$(".loadSection").hide().html(data).fadeIn("fast");
				},
				error: function (error) {
					$(".loadSection").hide().html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow' style='color:#0c5460'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 3']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 4']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 5']; ?>'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3><div class='fadein row col-md-6 mx-auto'><div class='card card-md' style='margin-top:100px;padding:20px;width:100%'><center> <h5>Uh oh! There seems to be a problem on our end.</h5><br><h6>Reason: " + error.status + " " + error.statusText +"</h6><br><form method='post'><input value='true' type='hidden' name='ignore'><input value='"+section+"' type='hidden' name='page'><button class='btn btn-sm btn-warning' style='background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none;color:#0c5460' onclick='location.reload();'>Retry &nbsp;<i class='fas fa-sync'></i></button></form> <center></div></div>").fadeIn("fast");				
    			}
				
			});
			var item = '#secbtn'+section;
			$(item).addClass('secActive');	
		}
		if(section == "Asset_Portal" || section == "Service_Desk_New_Ticket" || section == "Service_Desk_Ticket" || section == "Service_Desk_Home" || section == "Profile" || section == "Assets" || section == "Dashboard" || section == "Technicians" || section == "Customers" || section == "Versions" || section == "Init"){
			$('#sectionList').slideUp(400);
			$('#navConfig').collapse('show');
		}else if($('#sectionList').css("display")=="none"){
			$('#sectionList').slideDown(400);
			$('#navConfig').collapse('hide');
		}
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
			$('#sidebar').removeClass('active');
			
		}
	}
		<?php if($_GET['page']==""){ ?>
		if(currentSection==""){ currentSection="Login"; }
			loadSection(currentSection, computerID);
		<?php }else{ ?>
			loadSection("<?php echo ucfirst($_GET['page']);?>", "<?php echo (int)$_GET['ID'];?>");
		<?php 
			}
		 if($_SESSION['userid']!=""){ 
			if($_SESSION['showModal']=="true" && 1==1){
				//show modal once after login
				echo 'pageAlert("'.$messageTitle.'", "'.$messageText.'");';
				$_SESSION['showModal'] = "";
			}
		 } ?>	 	
	</script>
	<script>
		var counter = 2;
		$("#addButton").click(function () {
	 
		var newTextBoxDiv = $(document.createElement('tr')).attr("id", 'TextBoxDiv' + counter);
		newTextBoxDiv.after().html('<th scope="row" style="vertical-align:middle;">AND</th>' +
			'<td>' +
			'<select required class="form-control" style="width:23%;display:inline-block;" name="taskCond' + counter + '">'+
			'<option value="Total Alert Count">Total Alert Count</option><option value="Total Ram/Memory">Total Ram/Memory</option><option value="Available Disk Space">Available Disk Space</option><option value="Total Disk Space">Total Disk Space</option><option value="Domain">Domain</option><option value="Public IP Address">Public IP Address</option><option value="Antivirus">Antivirus</option><option value="Agent Version">Agent Version</option><option value="Total User Accounts">Total User Accounts</option><option value="Command Received">Command Received</option><option value="Agent Comes Online">Agent Comes Online</option><option value="Agent Goes Offline">Agent Goes Offline</option><option value="Windows Activation">Windows Activation</option><option value="Local IP Address">Local IP Address</option><option value="Last Update">Last Update</option></select> <select class="form-control" required style="width:20%;display:inline-block;" name="taskCond' + counter + 'Comparison"><option value="=">Equals</option><option value="!=">Not Equal</option><option value=">">Greater than</option><option value="<">Less than</option><option value=">=">Greater than or equals</option><option value="<=">Less than equals</option><option value="contain">Contains</option><option value="notcontain">Does not Contain</option></select>' +
			' <input style="width:47%;display:inline-block;" type="text" class="form-control" required  name="taskCond' + counter + 
			'Value" id="textbox' + counter + '" value="" ><button onclick="hide('+ counter + ');" type="button" id="removeButton' + counter + '" style="margin-left:10px" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></td>');
			newTextBoxDiv.appendTo("#TextBoxesGroup");
		counter++;
		if(counter><?php echo $taskCondtion_max; ?>){
				$("#addButton" ).hide();
				return false;
		}  
		}); 
		function hide(counter2) {
			counter--;
			$("#addButton" ).show();
			$("#TextBoxDiv" + counter2).remove();
		};
	
	</script>
	<script>
		function loadChat(ID) {
		

			$.ajax({
				url: "includes/chat.php?ID="+btoa(ID),
				timeout: 60000,
				success: function(data) {
					$("#chatDiv").html(data);
					$("#asset_message_id").val(ID);
					$(".sideDiv").removeClass("secActive");
					$("#side"+ID).addClass("secActive");
					$('#chatDiv2').scrollTop($('#chatDiv2')[0].scrollHeight);
				}				
			});
			
			$("#asset_message_id").val(ID);
			$(".sideDiv").removeClass("secActive");
			$("#side"+ID).addClass("secActive");
			$('#chatDiv2').scrollTop($('#chatDiv2')[0].scrollHeight);

			setInterval(function(){ 
				$.ajax({
					url: "includes/chat.php?ID="+btoa(ID),
					timeout: 60000,
					success: function(data) {
						$("#chatDiv").html(data);
						$("#asset_message_id").val(ID);
						$(".sideDiv").removeClass("secActive");
						$("#side"+ID).addClass("secActive");
						$('#chatDiv2').scrollTop($('#chatDiv2')[0].scrollHeight);
					}		
				});
			},10000);

			$.post("index.php", {
				type: "asset_viewed",
				ID: ID
			},
			function(data, status){
				$('#chatDiv2').scrollTop($('#chatDiv2')[0].scrollHeight);
			});
	
}
	

	</script>
</html>