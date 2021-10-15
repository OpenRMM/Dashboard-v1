<?php
include("../includes/db.php");
$computerID = $_GET['ID'];

if(!isset($_SESSION['userid'])){
	http_response_code(404);
	die();
}
//show recents on sidebar
$recent = array_slice($_SESSION['recent'], -8, 8, true);
echo "<h6>Recently Viewed</h6>";	
$count = 0;
foreach(array_reverse($recent) as $item) {
	$query = "SELECT ID, hostname FROM computerdata where ID='".$item."'";
	$results = mysqli_query($db, $query);
	$data = mysqli_fetch_assoc($results);
	if($data['ID']==""){ continue; }
	$count++;
?>
	<a href="javascript:void(0)" onclick="loadSection('General', '<?php echo $data['ID']; ?>');">
		<li class="secbtn">
			<i class="fas fa-desktop"></i>&nbsp;&nbsp;&nbsp;
			<?php echo strtoupper($data['hostname']);?>
		</li>
	</a>
<?php } 
 if($count==0){ ?>
	<li>No Recent Computers</li> 
<?php } ?>
