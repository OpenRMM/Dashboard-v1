<?php	
	$activity = "Technician Logged Out";
	userActivity($activity,$_SESSION['userid']);	
	$_SESSION['userid']="";
	$_SESSION['AccountType']="";
	$_SESSION['page']="";
	$_SESSION['computerHostname']="";
	$_SESSION['computerID']="";
	session_unset();
	session_destroy();
	mysqli_close($db);
?>
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
	<div class='fadein row col-md-6 mx-auto'>
		<div class='card card-md' style='margin-top:100px;padding:20px;width:100%'>
		<center> 
			<h5>You will be redirected to the login page in just a moment.</h5>
			<br>
			<h6>Have a <?php echo welcome(); ?>!</h6>
			<br>
		<center>
		</div>
	</div>
<?php 
	exit(" ");
	if(!isset($_SESSION['userid'])){
		http_response_code(404);
		die();
	}
?>

