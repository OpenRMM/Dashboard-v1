<?php 
if($_SESSION['userid']!=""){ ?>
	<script> 
		loadSection('Dashboard');
		setCookie("section", btoa("Dashboard"), 365);	
	</script>
<?php 
	exit; 
} elseif(!file_exists("../includes/config.php")){
?>
	<script> 
		loadSection('Init');
		setCookie("section", btoa("Init"), 365);	
	</script>
<?php 
	exit;
}else{ 
	
}	
?>
<div class="row" style="height:100vh;margin-top:-50px;margin-left:-40px;text-align:center;margin-bottom:-150px;border-radius:3px;padding:5px;background: url('/assets/images/BackgroundImage1.jpg') no-repeat fixed right">
	<div style="background:#35384e;margin-bottom:100px;margin-top:40px;" class="sidenav col-xs-3 col-sm-3 col-md-3 col-lg-3 shadow">
		<div class="login-main-text" >
			<h4>Remote Monitoring & Management Platform</h4>
			<br>
			<p style="text-align:left">Remote Management is managing a computer or a network from a
			remote location. It involves installing software and managing all activities on the systems/network, workstations,
			servers or endpoints of a client, from a remote location.</p>
			<hr>
			<p style="text-align:left">RMM technology gives IT service providers the ability to manage more clients than traditional break/fix IT providers, and to do so more efficiently.<br><br> Through RMM, technicians can remotely install software and updates, administer patches, and more and this can often all be done from a single, unified dashboard. Technicians can administer tasks simultaneously to many computers at once, and no longer have to travel from office to office to handle routine maintenance.</p>
		</div>
	</div>
	<div style="padding:20px;margin-top:13%;margin-bottom:10px;" class="main col-xs-12 col-sm-12 col-md-12 col-lg-8">
		<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 col-lg-6 shadow" style="border-radius:5px 5px 0px 0px;background:#333;color:#fff;margin-top:-100px;padding-bottom:50px;height:100px;">
			<h2 style="padding-top:10px">OpenRMM</h2>
			<p>Login To Access Our Remote Monitoring & Management Platform.</p>
		</div>			
		<div style="border-radius:0px 0px 5px 5px;padding:10px;" class="col-xs-6 col-sm-6 col-md-6 col-lg-6 shadow p-3 mb-5 bg-body">
		
			<form method="post" action="/" autocomplete="off" class="form-signin">
				
				<div style="text-align:left" class="form-label-group">
					<label for="inputEmail"><b>Username:</b></label>
					<input <?php if($_SESSION['tfa_pass']=="false"){ echo "readonly"; } ?> maxlength="25" minlength="4" type="text" value="<?php echo clean($_SESSION['loginusername']); ?>" name="loginusername" id="inputEmail" class="form-control" placeholder="Username" required <?php if($_SESSION['loginusername']==""){ echo"autofocus"; } ?>>
				</div>
				<br>
				<div style="text-align:left" class="form-label-group">
					<?php if($_SESSION['tfa_pass']=="false"){ ?>
						<label for="inputPassword"><b>Code from authenticator app (ex. Google Authenticator):</b></label>
						<input autocomplete="off" <?php if($_SESSION['loginusername']!=""){ echo "autofocus"; } ?> maxlength="25" minlength="4" type="password" name="tfaLoginpassword" id="inputPassword" class="form-control" placeholder="" required>
					<?php }else{ ?>
						<label for="inputPassword"><b>Password:</b></label>
						<input <?php if($_SESSION['loginusername']!=""){ echo"autofocus"; } ?> maxlength="25" minlength="4" type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
						<div style='margin-left:5px;padding-top:10px;font-size:12px;' class="tooltips tooltipHelper">Forgot Password?
							<span class="tooltiptext">Contact Your System Administrator To Reset Your Password</span>
						</div>
					<?php } $_SESSION['tfa_pass']=""; ?>
				</div>					
				<br>
				<button style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;color:#0c5460;float:right;" class="btn btn-sm btn-block text-uppercase" type="submit">
				 Sign in &nbsp;<i class="fas fa-sign-in-alt"></i>
				</button>
				<br><br>
			</form>
		</div>
	</div>
</div>	
<footer style="z-index:999;padding:5px;height:30px;position: fixed;left: 0;bottom: 0;width: 100%;color:#0c5460;text-align: center;background:#333" class="page-footer font-small white">
	<div class="footer-copyright text-center" style="color:#fff">© Copyright <?php echo date('Y');?> 
		<a style="color:#fff" href="https://github.com/OpenRMM"> OpenRMM</a>
		<a style="float:right;font-size:12px;cursor:pointer;color:#fefefe;padding-right:20px" onclick="loadSection('Downloads');"><u>Agent Downloads</u></a>
	</div>
</footer>
<?php 	
	if($_SESSION['loginMessage']!=""){ 
		if($_SESSION['loginCount']=="")$_SESSION['loginCount']=0;  
?>
		<script>
			toastr.error('<?php echo $_SESSION['loginMessage']; ?>');
		</script>
<?php 
		if($_SESSION['loginCount']==1){
			$_SESSION['loginMessage']=0;
			$_SESSION['loginCount']=0;	
		}
		$_SESSION['loginCount']++;
	} 
	$_SESSION['loginusername']="";
?>
