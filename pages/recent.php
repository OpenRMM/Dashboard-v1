<?php
include("../includes/db.php");
$computerID = (int)base64_decode($_GET['ID']);

if(!isset($_SESSION['userid'])){
	http_response_code(404);
	die();
}
//show recents on sidebar
$recent = array_slice($_SESSION['recent'], -8, 8, true);
echo "<h6>Recently Viewed</h6>";	
$count = 0;
foreach(array_reverse($recent) as $item) {
	$query = "SELECT ID, computer_type FROM computers where ID='".$item."'";
	$results = mysqli_query($db, $query);
	$data = mysqli_fetch_assoc($results);
	if($data['ID']==""){ continue; }
	$json = getComputerData($data['ID'], array("*"), "");
	$hostname =  $json['General']['Response'][0]['csname'];
	$count++;
	
	$icons = array("desktop","server","laptop","tablet","allinone","other");
	if(in_array(strtolower(str_replace("-","",$data['computer_type'])), $icons)){
		$icon = strtolower(str_replace("-","",$data['computer_type']));
		if($icon=="allinone")$icon="tv";
		if($icon=="tablet")$icon="tablet-alt";
		if($icon=="other")$icon="microchip";
	}else{
		$icon = "server";
	} 
?>
	<a href="javascript:void(0)" onclick="loadSection('General', '<?php echo $data['ID']; ?>');$('.sidebarComputerName').text('<?php echo textOnNull(strtoupper($hostname),'Unavailable');?>');">
		<li class="secbtn">
			<i class="fas fa-<?php echo $icon; ?>"></i>&nbsp;&nbsp;&nbsp;
			<?php echo textOnNull(strtoupper($hostname),"Unavailable");?>
		</li>
	</a>
<?php } 
 if($count==0){ ?>
	<li>No Recent Computers</li> 
<?php } ?>
