<?php
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
			header("location: index.php");
		}
		include("includes/post.php");	
	}

	//Get user data
	$query = "SELECT username,nicename,accountType FROM users WHERE ID='".$_SESSION['userid']."' LIMIT 1";
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
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--- Font Awesome --->
		<link rel="stylesheet" href="assets/css/all.min.css"/>
		<script src="assets/js/all.min.js"></script>
		<link rel="icon" href="assets/images/favicon.ico" type="image/ico" sizes="16x16">
		<!-- jquery-->
		<script src="assets/js/tagsinput.js"></script>
		<script src="assets/js/jquery.js" ></script>
		<!--- Bootstap --->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css"/>
		<link rel="stylesheet" href="assets/css/tagsinput.css"/>
		<link rel="stylesheet" href="assets/css/bootstrap.min.css"/>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet">
		
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
	</head>
	<style>
		a { color:#003366; }
		.calert { margin-left:5px;font-size:12px;width:44%;margin-right:5px;float:left;min-height:60px }
		@media screen and (max-width: 850px) {
			.calert { height: 120px; }
			.headall { display: none; }
		}
		.secActive {
			background:<?php echo $siteSettings['theme']['Color 2']; ?>!important;
			color:#fff!important;
			border-radius:3px;
		}
		.secbtn:hover{
			background:#282828!important;
			color:#fff!important;  
		}
	</style>
	<body style="background-color:<?php echo $siteSettings['theme']['Color 1']; ?>;height:100%; position: relative;min-height: 100vh;">
		<div style="padding:5px;background-color:#fff;color:#fff;text-align:center;padding-top:4px;padding-left:20px;position:fixed;top:0px;width:100%;z-index:99;box-shadow: 0 0 11px rgba(0,0,0,0.13);">
			<h5>
				<div style="float:left;">
					<button type="button" style="display:inline-block;margin-top:2px;border:none;box-shadow:none" class="btn-sm sidebarCollapse btn" title="Show/Hide Sidebar">
						<i style="font-size:16px" class="fas fa-align-left"></i>
					</button>		
					<div style="display:inline-block;">
						<a style="color:#333;font-size:22px;cursor:pointer" onclick="loadSection('<?php if($_SESSION['userid']!=""){ echo "Dashboard"; }else{ echo "Login"; } ?>');" >Open<span style="color:<?php echo $siteSettings['theme']['Color 2']; ?>">RMM</span></a>
					</div>
				</div>
				<?php if($_SESSION['userid']!=""){ ?>
					<div style="float:right;">
						<div>
							<button type="button" style="border:none;box-shadow:none" onclick='pageAlert("<?php echo $messageTitle; ?>", "<?php echo textOnNull($messageText ,"No Messages"); ?>");' class="btn-sm btn" title="Configure Alerts">
								<i style="font-size:16px" class="fas fa-bell"></i>
								<span style="margin-top" class="text-white badge bg-c-pink"><?php if($messageText==""){ echo "0"; }else{ echo "1"; } ?></span>
							</button>
							<?php if($user['accountType']=="Admin"){ ?>
								<button type="button" onclick="loadSection('Init','true');"style="border:none;box-shadow:none" class="btn-sm btn" title="Configure OpenRMM">
									<i style="font-size:16px" class="fas fa-cog"></i>
								</button>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</h5>
		</div>
		<div class="wrapper">
			<!-- Sidebar -->
			<?php if($_SESSION['userid']!=""){ ?>
				<nav style="background:#35384e" id="sidebar">
					<ul class="list-unstyled components" style="padding:20px;margin-top:25px;">
						<div style="text-align:center;width:100%">
							<a style="cursor:pointer" onclick="loadSection('Profile','<?php echo $_SESSION['userid']; ?>');">
								<i style="color:#282828;font-size:68px;text-align:center" class="fa fa-user" ></i>
								<h6 style="color:#fff;margin-top:10px"><?php echo ucwords($user['nicename']); ?></h6>
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
							<i class="fa fa-desktop" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp; Asset List
						</li>
						<li class="secbtn">
							<h6 style="color:#d3d3d3" data-toggle="collapse" data-target="#navConfig"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;&nbsp;Configuration <i class="fa fa-angle-down" aria-hidden="true"></i></h6>
						</li>
						<ul style="margin-left:20px" class="nav nav-list collapse" id="navConfig">
							<?php if($_SESSION['accountType']=="Admin"){ ?>
								<li onclick="loadSection('AllCompanies');" id="secbtnAllCompanies" style="width:100%" class="secbtn">
									<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;<?php echo $msp; ?>s
								</li>
								<li onclick="loadSection('AllUsers');" id="secbtnAllUsers" style="width:100%" class="secbtn">
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
							<li onclick="loadSection('General');" id="secbtnGeneral" class="secbtn">
								<i class="fas fa-stream"></i>&nbsp;&nbsp;&nbsp; Asset Overview
							</li>
							<hr>
							<h6 class="">Tools</h6>
							<li onclick="loadSection('Commands');" id="secbtnCommands" class="secbtn">
								<i class="fas fa-terminal"></i>&nbsp;&nbsp;&nbsp; Commands
							</li>
							<li onclick="loadSection('Alerts');" id="secbtnAlerts" class="secbtn">
								<i class="fas fa-bell"></i>&nbsp;&nbsp;&nbsp; Alerts
							</li>
							<li onclick="loadSection('EventLogs');" id="secbtnEventLogs" class="secbtn">
								<i class="fas fa-file-code"></i>&nbsp;&nbsp;&nbsp; Event Logs
							</li>
							<li onclick="loadSection('Registry');" id="secbtnRegistry" class="secbtn">
								<i class="fas fa-cubes"></i>&nbsp;&nbsp;&nbsp; Registry
							</li>
							<li onclick="loadSection('FileManager');" id="secbtnFileManager" class="secbtn">
								<i class="fas fa-folder"></i>&nbsp;&nbsp;&nbsp; File Manager
							</li>
							<hr>
							<h6 class="">Asset Details</h6>
							<li onclick="loadSection('Network');" id="secbtnNetwork" class="secbtn">
								<i class="fas fa-network-wired"></i>&nbsp;&nbsp;&nbsp; Network
							</li>
							<li onclick="loadSection('Programs');" id="secbtnPrograms" class="secbtn">
								<i class="fab fa-app-store-ios"></i>&nbsp;&nbsp;&nbsp; Programs
							</li>
							<!--li onclick="loadSection('DefaultPrograms');" id="secbtnDefaultPrograms" class="secbtn">
								<i class="fab fa-app-store-ios"></i>&nbsp;&nbsp;&nbsp; Default Programs
							</li-->
							<li onclick="loadSection('Services');" id="secbtnServices" class="secbtn">
								<i class="fas fa-cogs"></i>&nbsp;&nbsp;&nbsp; Services
							</li>
							<li onclick="loadSection('Processes');" id="secbtnProcesses" class="secbtn">
								<i class="fas fa-microchip"></i>&nbsp;&nbsp;&nbsp; Processes
							</li>
							<li onclick="loadSection('Printers');" id="secbtnPrinters" class="secbtn">
								<i class="fas fa-edit"></i>&nbsp;&nbsp;&nbsp; Printers
							</li>
							<li onclick="loadSection('Disks');" id="secbtnDisks" class="secbtn">
								<i class="fas fa-hdd"></i>&nbsp;&nbsp;&nbsp; Disks
							</li>
							<li onclick="loadSection('Memory');" id="secbtnMemory" class="secbtn">
								<i class="fas fa-memory"></i>&nbsp;&nbsp;&nbsp; Memory
							</li>
							<li onclick="loadSection('AttachedDevices');" id="secbtnAttachedDevices" class="secbtn">
								<i class="fab fa-usb"></i>&nbsp;&nbsp;&nbsp; Attached Devices
							</li>
							<li onclick="loadSection('OptionalFeatures');" id="secbtnOptionalFeatures" class="secbtn">
								<i class="fas fa-list"></i>&nbsp;&nbsp;&nbsp; Optional Features
							</li>
							<li onclick="loadSection('Users');" id="secbtnUsers" class="secbtn">
								<i class="fas fa-users"></i>&nbsp;&nbsp;&nbsp; User Accounts
							</li>
							<li></li>
						</div>				
						<div class="recents" id="recents" style="margin-top:20px;"></div>							
						<div style="height:500px">&nbsp;</div>		
					</ul>
				</nav>
			<?php } ?>
			<!-- Page Content -->
			<div id="content" style="margin-top:15px;padding:30px;width:100%;">
			
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-top:20px;">
						<div class="loadSection">
							<!------ Loads main data from jquery ------>
							<center>
								<h1 style="margin-top:40px;">
									<i class="fas fa-spinner fa-spin"></i>
								</h1>
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
		<?php if($_SESSION['userid']!=""){ ?>
		<!-------------------------------MODALS------------------------------------>
		<!--------------- Notification Modal ------------->
			<div id="alertModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-md">
					<div class="modal-content" >
						<div class="modal-header">
							<h6 class="modal-title"><b>Notifications</b></h6>
						</div>
						<div class="modal-body">
							<ul>					
							<li>No New Notifications</li>
							</ul>
						</div>
						<div class="modal-footer">
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-warning btn-sm" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!--------------- User Modal ------------->
			<div id="userModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Add/Edit User
								</b>
							</h6>
						</div>
						<form id="userform" method="POST">
							<input type="hidden" name="type" value="AddEditUser"/>
							<input type="hidden" name="ID" id="editUserModal_ID"/>
							<div class="modal-body">
								<p>This will configure a new user and will allow them access to this platform.</p>
								<div class="form-group">
									<input placeholder="Name" type="text" name="name" class="form-control" id="editUserModal_name"/>
								</div>
								<div class="form-group">
									<input placeholder="Email"  type="email" name="email" class="form-control" id="editUserModal_email"/>
								</div>
								<div class="form-group">
									<input placeholder="Username"  required type="text" name="username" class="form-control" id="editUserModal_username"/>
								</div>
								<div class="form-group">
									<input placeholder="Phone" type="text" name="phone" class="form-control" id="editUserModal_phone"/>
								</div>
								<?php if($_SESSION['accountType']=="Admin"){  ?>
									<div class="form-group">
										<label for="editUserModal_type">Access Type</label>
										<select required name="accountType" class="form-control">
											<option id="editUserModal_type" value="">Select Option</option>
											<option value="Standard">Standard</option>
											<option value="Admin">Admin</option>
										</select>
									</div>
								<?php } ?>
								<div class="input-group">
									<input placeholder="Password" style="display:inline" type="password" id="editUserModal_password" name="password" class="form-control"/>
									<span class="input-group-btn">
										<a style="border-radius:0px;padding:6px;pointer:cursor;color:#fff;" class="btn btn-md btn-success" onclick="generate();" >Generate</a>
									</span>
								</div>
								<br>
								<div class="form-group">
									<input placeholder="Confirm Password" type="password" id="editUserModal_password2" name="password2" class="form-control"/>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
								<button type="submit" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning">
									<i class="fas fa-check"></i> Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!--------------- Version Modal ------------->
			<div id="versionModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Delete Version
								</b>
							</h6>
						</div>
						<form id="user" method="POST">
							<input type="hidden" name="version" value="" id="delVersion_ID"/>
							<div class="modal-body">
								<p>This will delete this agent version. Are you sure?</p>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
								<button type="submit" style="color:#fff" class="btn btn-sm btn-danger">
									<i class="fas fa-trash"></i> Delete
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!--------------- Note Modal ------------->
			<div id="noteModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Create A New Note
								</b>
							</h6>
						</div>
						<form id="note" method="POST">
							<div class="modal-body">
								<p>This Will Create A New Note That Only You And Other Administrators Can See.</p>
								<div class="form-group">
									<label for="noteTitle">Title</label>
									<input type="text" class="form-control" placeholder="" name="noteTitle">
								</div>
								<div class="form-group">
									<label for="note">Note</label>
									<textarea rows="6" required maxlength="300" name="note" class="form-control"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
								<button type="submit"  style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning">
									<i class="fas fa-save"></i> Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!--------------- View note Modal ------------->
			<div id="viewNoteModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h4 id="notetitle">
								<b>
									View Note
								</b>
							</h4>
						</div>
						<div class="modal-body">					
								<h6 style="margin-top:20px"><span id="notedesc"></span></h6>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-primary" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!---------- Company Modal ------------>
			<div id="companyModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Add/Edit <?php echo $msp; ?>
								</b>
							</h6>
						</div>
						<form method="POST">
							<input type="hidden" name="type" value="AddEditCompany"/>
							<input type="hidden" name="ID" value="" id="editCompanyModal_ID"/>
							<div class="modal-body">
								<p>This Will Add <?php echo $msp; ?> Information. To Better Assist And Organize Content.</p>
								<div class="form-group">
									<input placeholder="Name" type="text" name="name" class="form-control" id="editCompanyModal_name"/>
								</div>
								<div class="form-group">
									<input placeholder="Address" type="text" name="address" class="form-control" id="editCompanyModal_address"/>
								</div>
								<div class="form-group">
									<input placeholder="Phone" type="phone" name="phone" class="form-control" id="editCompanyModal_phone"/>
								</div>
								<div class="form-group">
									<input placeholder="Email" type="email" name="email" class="form-control" id="editCompanyModal_email"/>
								</div>
								<div class="form-group">
									<textarea placeholder="Additional Info" style="resize:vertical" name="comments" class="form-control" placeholder="Optional" id="editCompanyModal_comments"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
								<button type="submit" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning">
									<i class="fas fa-check"></i> Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!----------- Terminal ---------------->
			<div id="terminalModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Terminal
								</b>
							</h6>
						</div>
						<div class="modal-body" style="background-color:#000;color:#fff;font-family: 'Courier New', Courier, monospacepadding:20px;">
							<div style="max-height:400px;margin-bottom:10px;min-height:100px;overflow:auto;">
								<div id="terminalResponse" style="color:#fff;font-family:font-family:monospace;">
									Microsoft Windows [Version 10.0.<?php echo rand(100000,9999999);?>]<br/>
									(c) <?php echo date("Y");?> Microsoft Corporation. All rights reserved.
									<br/><br/>
								</div>
							</div>
							<div style="min-height:50px;">
								<?php echo strtoupper($data['hostname']);?>> <input type="text" id="terminaltxt" style="outline: none;border:none;background:#000;width:300px;color:#fff;font-family:font-family:monospace;"/>
							</div>
						</div>
					</div>
				</div>
			</div>
				<!------------- Alerts (not sure if used)------------------->
			<div id="confirm" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6 id="computerAlertsHostname">
								<b>
									Confirm Action
								</b>
							</h6>
						</div>
						<div class="modal-body">
							<p>Are You Sure You Would Like To Complete This Action></p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 4']; ?>;color:#fff;" data-dismiss="modal">Close</button>
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning"  data-dismiss="modal">Confirm</button>
						</div>
					</div>
				</div>
			</div>
			<!------------- Alerts ------------------->
			<div id="computerAlerts" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6 id="computerAlertsHostname">
								<b>
									Alerts
								</b>
							</h6>
						</div>
						<div class="modal-body">
							<div id="computerAlertsModalList"></div>
						</div>
						<div class="modal-footer">
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!------------- Historical ------------------->
			<div id="historicalData_modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">
								Historical Data
							</h5>
						</div>
						<div class="modal-body">
							<div id="historicalData" style="overflow:auto;max-height:400px;"></div>
						</div>
						<div class="modal-footer">
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning"  data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!------------- Historical Date Selection  ------------------->
			<div id="historicalDateSelection_modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">
								View Historical Data
							</h5>
						</div>
						<div class="modal-body" style="overflow:auto;max-height:400px;">
							<table class="table table-striped">
								<tr>
									<td>Latest</td>
									<td>
										<form method="post">
											<input type="hidden" value="latest" name="historyDate">
											<button type="submit" class="btn btn-sm btn-secondary" >
												Select
											</button>
										</form>
									</td>
								</tr>
								<?php
									$showLast = $siteSettings['Max_History_Days']; //Show last 31 days
									$count = 0;
									while($count <= $showLast){ $count++;
									$niceDate = date("l, F jS", strtotime("-".$count." day"));
									$formatedDate = date("n/j/Y", strtotime("-".$count." day"));
									$formatedDate2 = date("Y-n-j", strtotime("-".$count." day"));
								?>
								<tr>
									<td><?php echo $niceDate; ?></td>
									<td>
										<form method="post">
											<input type="hidden" value="<?php echo $formatedDate2; ?>" name="historyDate"> 
											<button type="submit" class="btn btn-sm btn-warning">Select</button>
										</form>
										</td>
								</tr>
								<?php }?>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-secondary"  data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!------------- Upload .exe File ------------------->
			<div id="agentUpload" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6><b>Upload New Agent (.exe)</b></h6>
						</div>
						<form enctype="multipart/form-data" method="POST">
							<div class="modal-body">
							<p>This Will Create A Downloadable .Zip File. It Will Also Rewrite The Existing Update Directory.</p>
								<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text" id="inputGroupFileAddon01">Agent Version</span>
								</div>
								<input style="padding:20px" type="text" name="agentVersion" required minlength=7 class="form-control" placeholder="ex. 1.0.0.4" value="<?php echo $siteSettings['general']['agent_latest_version']; ?>"/>&nbsp;
								<div class="input-group-prepend">
									<span class="input-group-text" id="inputGroupFileAddon01">Upload .exe</span>
								</div>
								<div class="custom-file" >
									<input required="" type="hidden" value="true" name="agentFile">
									<input  required="" accept=".exe" type="file" name="agentUpload" class="custom-file-input" id="agentUpload"/>
									<label style="padding:10px;padding-bottom:30px" class="custom-file-label" for="agentUpload">Choose file</label>
								</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm"  data-dismiss="modal">Close</button>
								<button type="submit" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning" >Upload</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="modal fade in" id="editTrigger" tabinsdex="-1" role="dialog" aria-hidden="true" >
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" >Create New Task</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
							</button>
						</div>
						<form method="post">
							<div class="modal-body">
								<label>Task Name</label>
								<input maxlength="30" required type="text" class="form-control" name="name">
								<br><hr>
								<h4>Conditions</h4>
								<table class="table">
									<tbody>
										<tr>
											<th scope="row" style="vertical-align:middle;">IF</th>
											<td>
												<input type="hidden" value="newTask" name="type">
												<select required class="form-control" style="width:23%;display:inline-block;" name="taskCond1">
													<option value="Total Alert Count">Total Alert Count</option>
													<option value="Total Ram/Memory">Total Ram/Memory</option>
													<option value="Available Disk Space">Available Disk Space</option>
													<option value="Total Disk Space">Total Disk Space</option>
													<option value="Domain">Domain</option>
													<option value="Public IP Address">Public IP Address</option>
													<option value="Antivirus">Antivirus</option>

													<option value="Agent Version">Agent Version</option>
													<option value="Total User Accounts">Total User Accounts</option>
													<option value="Command Received">Command Received</option>
													<option value="Agent Comes Online">Agent Comes Online</option>
													<option value="Agent Goes Offline">Agent Goes Offline</option>
													<option value="Windows Activation">Windows Activation</option>
													<option value="Local IP Address">Local IP Address</option>
													<option value="Last Update">Last Update</option>

												</select>

												<select class="form-control" required style="width:20%;display:inline-block;" name="taskCond1Comparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>

												<input type="text" required placeholder="Value" class="form-control" style="width:57%;display:inline-block;" name="taskCond1Value">     
											</td>
										</tr>
										<tr>
											<td colspan=2 style="float:left"><h6>(optional)</h6></td>
										</tr>
										<tr>
											<th scope="row" style="vertical-align:middle;">AND</th>
											<td>
												<select class="form-control" style="width:23%;display:inline-block;"name="taskCond2">	
													<option value="Total Ram/Memory">Total Ram/Memory</option>
													<option value="Total Alert Count">Total Alert Count</option>
													<option value="Available Disk Space">Available Disk Space</option>
													<option value="Total Disk Space">Total Disk Space</option>
													<option value="Domain">Domain</option>
													<option value="Public IP Address">Public IP Address</option>
													<option value="Antivirus">Antivirus</option>

													<option value="Agent Version">Agent Version</option>
													<option value="Total User Accounts">Total User Accounts</option>
													<option value="Command Received">Command Received</option>
													<option value="Agent Comes Online">Agent Comes Online</option>
													<option value="Agent Goes Offline">Agent Goes Offline</option>
													<option value="Windows Activation">Windows Activation</option>
													<option value="Local IP Address">Local IP Address</option>
													<option value="Last Update">Last Update</option>

												</select>

												<select class="form-control" style="width:20%;display:inline-block;" name="taskCond2Comparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>

												<input type="text" placeholder="Value" class="form-control" style="width:57%;display:inline-block;" name="taskCond2Value">     
											</td>
										</tr>
										<tr>
											<th scope="row" style="vertical-align:middle;">OR</th>
											<td>
												<select class="form-control" style="width:23%;display:inline-block;" name="taskCond3">
													<option value="Available Disk Space">Available Disk Space</option>
													<option value="Total Ram/Memory">Total Ram/Memory</option>
													<option value="Total Alert Count">Total Alert Count</option>
													<option value="Total Disk Space">Total Disk Space</option>
													<option value="Domain">Domain</option>
													<option value="Public IP Address">Public IP Address</option>
													<option value="Antivirus">Antivirus</option>

													<option value="Agent Version">Agent Version</option>
													<option value="Total User Accounts">Total User Accounts</option>
													<option value="Command Received">Command Received</option>
													<option value="Agent Comes Online">Agent Comes Online</option>
													<option value="Agent Goes Offline">Agent Goes Offline</option>
													<option value="Windows Activation">Windows Activation</option>
													<option value="Local IP Address">Local IP Address</option>
													<option value="Last Update">Last Update</option>

												</select>

												<select class="form-control" style="width:20%;display:inline-block;" name="taskCond3Comparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>

												<input type="text" placeholder="Value" class="form-control" style="width:57%;display:inline-block;" name="taskCond3Value">     
											</td>
										</tr>
									</tbody>
								</table>
								<hr>
								<h4>Action</h4>
								<table class="table" id="actionsTable">
									<thead>
										<tr>
											<th scope="col">Type</th>
											<th scope="col">Title/Pushover Email</th>
											<th scope="col">Command/Message</th>
										</tr>
									</thead>
									<tbody>		
										<tr>
											<td>
												<select required class="form-control" name="taskAct1">
													<option value="Log">Action Log</option>
													<option value="Command">Send Command</option>
													<option value="Pushover">Pushover</option>
												</select>
											</td>
											<td>
												<input required type="text" class="form-control" name="taskAct1Title">  
											</td>
											<td>
												<input type="text" required placeholder="Message" class="form-control" name="taskAct1Message">     
											</td>
											</tr>
									</tbody>
								</table>
								<p><b>Note:</b> To add log items to the message use: [condition state] [date] [time]</p>	
							</div>
							<div class="modal-footer">	
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-warning btn-sm" >Create Task</button>
							</div>
						</form>
					</div>
				</div>
			</div>
									
		<!---------------------------------End MODALS------------------------------------->	
		<?php } ?>
		<div id="notifications"> </div>
		<script>
		setInterval(function(section=currentSection, ID=computerID, date=sectionHistoryDate,other=otherEntry) {
			$("#notifications").load("includes/notifications.php?ID="+ID+"&Date="+date+"&page="+section+"&other="+other);	
		}, 3000);
		</script>
	</body>
	<script src="assets/js/extra.js" ></script>
	<script src="assets/js/toastr.js"></script>
	<script src="assets/js/custom.js"></script>
	<script>
		
		var computerID = getCookie("ID");
		var currentSection = getCookie("section");
		var sectionHistoryDate = "latest";
		$( document ).ready(function() {
        	$("#sortable").sortable();
        	$("#sortable").disableSelection();
		});
		//Load Page
		if (document.cookie.indexOf('section') === -1 ) {
			setCookie("section", "Login", 365);
		}
		//Load Pages
		var otherEntry = "";
		
		//Load Pages
		function loadSection(section=currentSection, ID=computerID, date=sectionHistoryDate,other=otherEntry){
		document.cookie = "section=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
		document.cookie = "ID=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
		var loadSection="";
		
		setCookie("section", section, 365);
		$('.secbtn').removeClass('secActive');
		setCookie("ID", ID, 365);
		computerID = ID;
		currentSection = section;
		if(section=="Logout"){
			toastr.options.progressBar = true;
			toastr.warning('Securely Logging You Out.');
			$(".loadSection").load("includes/loader.php?page="+section);
			setCookie("section", "Login", 365);
			setTimeout(function() { 
				location.reload(true);
			}, 5000);
		}else{
			if(section!="Logout" && section!="Dashboard"  && section!="Assets"  && section!="Dashboard"  && section!="Profile"  && section!="AllUsers"  && section!="AllCompanies" && section!="Versions" && section!="Init"){
				$(".loadSection").html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 2']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 3']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 4']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 5']; ?>'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3><div class='fadein row col-md-6 mx-auto'><div class='card card-md' style='margin-top:100px;padding:20px;width:100%'><center> <h5>We are getting the latest information for this asset</h5><br><h6>Instead of waiting, would you like to display the outdated assset data?</h6><br><form method='post'><input value='true' type='hidden' name='ignore'><input value='"+section+"' type='hidden' name='page'><button class='btn btn-sm btn-warning' style='background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none;' type='submit'>View Older Asset Information <i class='fas fa-arrow-right'></i></button></form> <center></div></div>");
			}else{
				$(".loadSection").html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 2']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 3']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 4']; ?>'></div><div class='spinner-grow' style='color:<?php echo $siteSettings['theme']['Color 5']; ?>'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3>");
	
			}
		
			$(".recents").load("pages/recent.php?ID="+ID);
			$("html, body").animate({ scrollTop: 0 }, "slow"); 
			//$(".loadSection").load("includes/loader.php?ID="+ID+"&Date="+date+"&page="+section+"&other="+other);
			loadSection = $.ajax({
				url: "includes/loader.php?ID="+ID+"&Date="+date+"&page="+section+"&other="+other,
				success: function(data) {
				$(".loadSection").hide().html(data).fadeIn("fast");
				request.transport.abort();
				}
			
			});
			
			var item = '#secbtn'+section;
			$(item).addClass('secActive');
			
		}
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
			$('#sidebar').removeClass('active');
		}
	}
		
		<?php if($_GET['page']==""){ ?>
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


</html>