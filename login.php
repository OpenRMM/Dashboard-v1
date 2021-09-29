<?php
require("Includes/db.php");
//$_SESSION['userid']="1";
if(isset($_POST['username'], $_POST['password'])){
	$username = stripslashes(mysqli_escape_string($db, clean(strip_tags(trim($_POST['username'])))));
	$password = stripslashes(clean(mysqli_escape_string($db, strip_tags(trim($_POST['password'])))));
	$query = "SELECT * FROM users where active='1' and username='".$username."'";
	$results= mysqli_query($db, $query);
	$count = mysqli_num_rows($results);
	$data = mysqli_fetch_assoc($results);
	$dbPassword=crypto('decrypt', $data['password'], $data['hex']);
	if($password!==$dbPassword or $dbPassword=="")$count=0;
		//echo $password." ".$dbPassword;
		if($count>0){
			$query = "UPDATE users SET last_login='".time()."' WHERE ID=".$data['ID'].";";
			$results = mysqli_query($db, $query);
			$_SESSION['userid']=$data['ID'];
			$_SESSION['username']=$data['username'];
			
			$activity="Technician Logged In";
			userActivity($activity,$data['ID']);
			
			$_SESSION['accountType']=$data['accountType'];	
			$_SESSION['showModal']="true";	
			$_SESSION['recent']=explode(",",$data['recents']);
			if($data['recents']==""){ $_SESSION['recent']=array(); }
			$_SESSION['recentedit']=explode(",",$data['recentedit']);
			if($data['recentedit']==""){ $_SESSION['recentedit']=array(); }
			header("location: index.php");
		}else{
			$message = " <span style='color:red'>Incorrect Login Details.</span>";
		}
}
if($_SESSION['userid']!=""){
	header("location: index.php"); exit; 
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>SMG RMM | Login</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" >
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="css/all.min.css"/>
		<script src="js/all.min.js"></script>
		<script src="js/jquery.js" ></script>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>
		<script src="js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="css/custom.css"/>
	</head>
	<body style="background-color:#f8f9fa">
	<div style="background-color:#fff;color:#fff;text-align:center;padding-top:12px;padding-left:20px;position:fixed;top:0px;width:100%;z-index:99;box-shadow: 0 0 11px rgba(0,0,0,0.13);padding-bottom:10px">
			<h5>
				<div style="float:left;">
					<button disabled type="button" style="display:inline-block;" class="btn-sm sidebarCollapse btn" title="Disabled">
						<i style="font-size:16px" class="fas fa-align-left"></i>
					</button>					
					<div style="color:#333;font-size:25px;display:inline-block;">
						Open<span style="color:#fd7e14">RMM</span>
					</div>
				</div>
			</h5>
		</div>	
		<div class="row" style="text-align:center;margin-bottom:10px;margin-top:10px;border-radius:3px;overflow:hidden;padding:5px;">
			<div style="background:#404E67;margin-bottom:100px" class="sidenav col-xs-3 col-sm-3 col-md-3 col-lg-3">
			 <div class="login-main-text">
				<h2>Remote Monitoring & Management Platform</h2><br>
				<p>Remote Management is managing a computer or a network from a
				remote location. It involves installing software and managing all activities on the systems/network, workstations,
				servers or endpoints of a client, from a remote location.</p>
			 </div>
		  </div>
		  <div style="padding:5px;margin-top:20%;margin-bottom:100px" class="main col-xs-8 col-sm-8 col-md-8 col-lg-8">
			 <div >
				<div class=" col-xs-6 col-sm-6 col-md-6 col-lg-6 ">
				<div style="margin-top:-100px;padding-bottom:50px">
					<h2>SMG Unlimited LLC</h2>
					<p>Login From Here To Access Our Remote Monitoring & Management Platform. <?php echo $message; ?></p>
				</div>
				   <form method="post" class="form-signin">
					  <div style="text-align:left" class="form-label-group">
						<label  for="inputEmail"><b>Username:</b></label>
						<input maxlength="25" minlength="4" type="text" name="username" id="inputEmail" class="form-control" placeholder="Username" required autofocus>
					  </div><br>
					  <div style="text-align:left" class="form-label-group">
						<label for="inputPassword"><b>Password:</b></label>
						<input maxlength="25" minlength="4" type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
					  </div><br>
					  <button style="background:#fe9365;color:#fff" class="btn btn-lg btn-block text-uppercase" type="submit">
						<i class="fas fa-sign-in-alt"></i> Sign in
					  </button>
					</form>
				</div>
			 </div>
		  </div>
		 </div>
		  <footer style="z-index:999;padding:5px;height:30px;position: fixed;left: 0;bottom: 0;width: 100%;color:#fff;text-align: center;background:<?php echo $siteSettings['theme']['Color 1'];?>" class="page-footer font-small black">
				<div class="footer-copyright text-center ">© <?php echo date('Y');?> Copyright
					<a style="color:#fff" href="http://smgunlimited.com"> SMG Unlimited</a>
				</div>
		  </footer>
	</body>
</html>