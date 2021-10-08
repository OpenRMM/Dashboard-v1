<?php	
	$activity = "Technician Logged Out";
	userActivity($activity,$_SESSION['userid']);	
	$_SESSION['userid']="";
	session_unset();
	session_destroy();
?>
<center>
	<h5>You will be redirected to the login page in just a moment.</h5>
	<br>
	<?php 
		exit("<h6>Redirecting</h6>");
		if(!isset($_SESSION['userid'])){
			http_response_code(404);
			die();
		}
	?>
</center>
