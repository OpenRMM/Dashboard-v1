<?php 
if($_SESSION['userid']!=""){ ?>
	<script> 
		loadSection('Dashboard');
		setCookie("section", "Dashboard", 365);	
	</script>
<?php 
	exit; 
} elseif(!file_exists("../includes/config.php")){
?>
	<script> 
		loadSection('Init');
		setCookie("section", "Init", 365);	
	</script>
<?php 
	exit;
}else{ 
	
}	
?>
<div class="row" style="text-align:center;margin-bottom:10px;margin-top:10px;border-radius:3px;overflow:hidden;padding:5px;">
	<div style="background:#35384e;margin-bottom:100px" class="sidenav col-xs-3 col-sm-3 col-md-3 col-lg-3">
		<div class="login-main-text">
			<h2>Remote Monitoring & Management Platform</h2>
			<br>
			<p>Remote Management is managing a computer or a network from a
			remote location. It involves installing software and managing all activities on the systems/network, workstations,
			servers or endpoints of a client, from a remote location.</p>
		</div>
	</div>
	<div style="padding:5px;margin-top:10%;margin-bottom:100px" class="main col-xs-8 col-sm-8 col-md-8 col-lg-8">			
		<div class=" col-xs-6 col-sm-6 col-md-6 col-lg-6 ">
			<div style="margin-top:-100px;padding-bottom:50px">
				<h2>OpenRMM</h2>
				<p>Login To Access Our Remote Monitoring & Management Platform.</p>
			</div>
			<form method="post" action="index.php" class="form-signin">
				<div style="text-align:left" class="form-label-group">
					<label  for="inputEmail"><b>Username:</b></label>
					<input maxlength="25" minlength="4" type="text" name="loginusername" id="inputEmail" class="form-control" placeholder="Username" required autofocus>
				</div>
				<br>
				<div style="text-align:left" class="form-label-group">
					<label for="inputPassword"><b>Password:</b></label>
					<input maxlength="25" minlength="4" type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
				</div>					
				<br>
				<button style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;color:#fff" class="btn btn-lg btn-block text-uppercase" type="submit">
				 Sign in &nbsp;<i class="fas fa-sign-in-alt"></i>
				</button>
			</form>
		</div>
	</div>
</div>	
<footer style="z-index:999;padding:5px;height:30px;position: fixed;left: 0;bottom: 0;width: 100%;color:#fff;text-align: center;background:<?php echo $siteSettings['theme']['Color 2'];?>" class="page-footer font-small black">
	<div class="footer-copyright text-center ">© Copyright <?php echo date('Y');?> 
		<a style="color:#fff" href="https://github.com/OpenRMM"> OpenRMM</a>
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
?>
