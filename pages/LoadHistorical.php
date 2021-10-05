<?php

	include("../includes/db.php");

	

	$hostname = $_POST['hostname'];

	$type = $_POST['type'];

	$date = $_POST['date'];

	

	$count = 0;

	$max = 50;

	

	//Format Entered date

	if(trim($date) != ""){

		$specificDate = date("n/j/Y", strtotime($date));

	}

?>



<?php ##################################### CPU Usage

if($type == "CPUUsage"){

	$wmi_name = "CPUUsage";

	

	if($specificDate != ""){//change to computer id

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}



	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){	

		$decoded = jsonDecode($result['WMI_Data'], true);

		$cpu = round($decoded['json']['Value']);

		

		if($cpu != $cpu_last){

			$retArray[] = array("Value"=>$cpu, "Date"=>$result['last_update']);

			$count++;

		}

		$cpu_last = $cpu;	

		if($count == $max){break;}

	}

	?>

	

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Processor Load</b></td></tr>

		<?php foreach($retArray as $item){

				if($item['Value'] > $siteSettings['Alert Settings']['Processor']['Danger'] ){

					$pbColor = "red"; //Danger

				}elseif($item['Value'] > $siteSettings['Alert Settings']['Processor']['Warning']){

					$pbColor = "#ffa500"; //Warning

				}else{ $pbColor = $siteSettings['theme']['Color 4']; }		

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td style="color:<?php echo $pbColor;?>">

					<b><?php echo $item['Value'];?>%</b> Used

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### Free Memory

if($type == "FreeMemory"){

	$wmi_name = "WMI_OS";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){	

		$decoded = jsonDecode($result['WMI_Data'], true);

		$freeMemory_mb = (int)$decoded['json'][0]['FreePhysicalMemory'] / 1024; //MB

		$freeMemory = ($freeMemory_mb>=1024 ? round($freeMemory_mb/1024,1)." GB" : round($freeMemory_mb)." MB");

		

		if($freeMemory != $freeMemory_last){

			$retArray[] = array("Value"=>$freeMemory, "Date"=>$result['last_update']);

			$count++;

		}

		$freeMemory_last = $freeMemory;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Free Memory</b></td></tr>

		<?php foreach($retArray as $item){

				if($item['Value'] > $siteSettings['Alert Settings']['Memory']['Free']['Danger'] ){

					$pbColor = "red"; //Danger

				}elseif($item['Value'] > $siteSettings['Alert Settings']['Memory']['Free']['Warning']){

					$pbColor = "#ffa500"; //Warning

				}else{ $pbColor = $siteSettings['theme']['Color 4']; }

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td style="color:<?php echo $pbColor;?>">

					<b><?php echo $item['Value'];?></b> Free

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### Total Memory

if($type == "TotalMemory"){

	$wmi_name = "WMI_ComputerSystem";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$totalMemory = (int)round($decoded['json'][0]['TotalPhysicalMemory'] / 1024 /1024/1024); //GB

					

		if($totalMemory != $totalMemory_last){

			$retArray[] = array("Value"=>$totalMemory, "Date"=>$result['last_update']);

			$count++;

		}

		$totalMemory_last = $totalMemory;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Total Memory</b></td></tr>

		<?php foreach($retArray as $item){

				if($item['Value'] < $siteSettings['Alert Settings']['Memory']['Total']['Danger'] ){

					$pbColor = "red"; //Danger

				}elseif($item['Value'] < $siteSettings['Alert Settings']['Memory']['Total']['Warning']){

					$pbColor = "#ffa500"; //Warning

				}else{ $pbColor = $siteSettings['theme']['Color 4']; }

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td style="color:<?php echo $pbColor;?>">

					<b><?php echo $item['Value'];?> GB</b> Total

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### Disk

if($type == "Disk"){

	$wmi_name = "WMI_LogicalDisk";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){	

		$decoded = jsonDecode($result['WMI_Data'], true);

		

		$freeSpace = $decoded['json'][0]['FreeSpace'];

		$size = $decoded['json'][0]['Size'];

		$used = $size - $freeSpace ;

		$usedPct = round(($used/$size) * 100);

		

		if($usedPct != $usedPct_last){

			$retArray[] = array("Value"=>$usedPct, "Date"=>$result['last_update']);

			$count++;

		}

		$usedPct_last = $usedPct;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Storage Used</b></td></tr>

		<?php foreach($retArray as $item){

				if($item['Value'] > $siteSettings['Alert Settings']['Disk']['Danger'] ){

					$pbColor = "red"; //Danger

				}elseif($item['Value'] > $siteSettings['Alert Settings']['Disk']['Warning']){

					$pbColor = "#ffa500"; //Warning

				}else{ $pbColor = $siteSettings['theme']['Color 4']; }

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td style="color:<?php echo $pbColor;?>">

					<b><?php echo $item['Value'];?>%</b> Used

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### Logged In User

if($type == "LoggedInUser"){

	$wmi_name = "WMI_ComputerSystem";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$username = $decoded['json'][0]['UserName'];

		

		if($username != $username_last){

			$retArray[] = array("Value"=>$username, "Date"=>$result['last_update']);

			$count++;

		}

		$username_last = $username;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Logged In User</b></td></tr>

		<?php foreach($retArray as $item){?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td><b><?php echo $item['Value'];?></b></td>

			</tr>

		<?php }?>

	</table>

<?php }?>







<?php ##################################### Operating System

if($type == "OperatingSystem"){

	$wmi_name = "WMI_OS";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$OS = $decoded['json'][0]['Caption'];

		

		if($OS != $OS_last){

			$retArray[] = array("Value"=>$OS, "Date"=>$result['last_update']);

			$count++;			

		}

		$OS_last = $OS;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Operating System</b></td></tr>

		<?php foreach($retArray as $item){?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td><b><?php echo $item['Value'];?></b></td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### PC Model

if($type == "PCModel"){

	$wmi_name = "WMI_ComputerSystem";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){	

		$decoded = jsonDecode($result['WMI_Data'], true);

		$model = $decoded['json'][0]['Model'];

		

		if($model != $model_last){

			$retArray[] = array("Value"=>$model, "Date"=>$result['last_update']);

			$count++;

		}

		$model_last = $model;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Computer Make/Model</b></td></tr>

		<?php foreach($retArray as $item){?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td><b><?php echo $item['Value'];?></b></td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### Firewall

if($type == "Firewall"){

	$wmi_name = "Firewall";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$status = $decoded['json']['Status'];

		

		if($status != $status_last){

			$retArray[] = array("Value"=>$status, "Date"=>$result['last_update']); 

			$count++;

		}

		$status_last = $status;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Firewall</b></td></tr>

		<?php foreach($retArray as $item){

				$color = ($item['Value'] == "True" ? "text-success" : "text-danger");

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td class="<?php echo $color;?>">

					<b><?php echo ($item['Value'] == "True" ? "Enabled" : "Disabled");?></b>

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### SQL Usernmae

if($type == "SQLUsername"){

	$wmi_name = "SQLUsername";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$status = $decoded['json']['Value'];

		

		if($status != $status_last){

			$retArray[] = array("Value"=>$status, "Date"=>$result['last_update']); 

			$count++;

		}

		$status_last = $status;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Current Database User</b></td></tr>

		<?php foreach($retArray as $item){				

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td>

					<b><?php echo $item['Value'];?></b>

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### WindowsActivation

if($type == "WindowsActivation"){

	$wmi_name = "WindowsActivation";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$status = $decoded['json']['Value'];

		

		if($status != $status_last){

			$retArray[] = array("Value"=>$status, "Date"=>$result['last_update']); 

			$count++;

		}

		$status_last = $status;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Windows Activation Status</b></td></tr>

		<?php foreach($retArray as $item){

				$color = ($item['Value'] == "Activated" ? "text-success" : "text-danger");

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td class="<?php echo $color;?>">

					<b><?php echo ($item['Value'] == "Activated" ? "Activated" : "Not Activated");?></b>

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### Antivirus

if($type == "Antivirus"){

	$wmi_name = "Antivirus";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$status = $decoded['json']['Value'];

		

		if($status != $status_last){

			$retArray[] = array("Value"=>$status, "Date"=>$result['last_update']); 

			$count++;

		}

		$status_last = $status;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Antivirus</b></td></tr>

		<?php foreach($retArray as $item){

				$color = ($item['Value'] == "No Antivirus" ? "text-danger" : "text-success");

		?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td class="<?php echo $color;?>">

					<b><?php echo ($item['Value'] == "" ? "No Antivirus" : $item['Value'] );?></b>

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### IP Address

if($type == "IPAddress"){

	$wmi_name = "IPAddress";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$ipAddress = $decoded['json']['Value'];

		

		if($ipAddress != $ipAddress_last){

			$retArray[] = array("Value"=>$ipAddress, "Date"=>$result['last_update']); 

			$count++;

		}

		$ipAddress_last = $ipAddress;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Local IP Address</b></td></tr>

		<?php foreach($retArray as $item){ ?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td class="<?php echo $color;?>">

					<b><?php echo ($item['Value'] == "" ? "No IP Address" : $item['Value'] );?></b>

				</td>

			</tr>

		<?php }?>

	</table>

<?php }?>



<?php ##################################### BIOS

if($type == "BIOSVersion"){

	$wmi_name = "WMI_BIOS";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$BIOS = $decoded['json'][0]['Version'];



		if($BIOS != $BIOS_last){

			$retArray[] = array("Value"=>$BIOS, "Date"=>$result['last_update']); 	

			$count++;

		}

		$BIOS_last = $BIOS;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>BIOS Version</b></td></tr>

		<?php foreach($retArray as $item){?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td><b><?php echo $item['Value'];?></b></td>

			</tr>

		<?php }?>

	</table>

<?php }?>





<?php ##################################### Agent Version

if($type == "AgentVersion"){

	$wmi_name = "AgentVersion";

	

	if($specificDate != ""){

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' AND last_update LIKE '".$specificDate."%' ORDER BY ID DESC LIMIT 200";

	}else{

		$query = "SELECT * FROM wmidata WHERE Hostname = '".$hostname."' AND WMI_Name='".$hostname."|".$wmi_name."' ORDER BY ID DESC LIMIT 200";

	}

	

	$results = mysqli_query($db, $query);

	while($result = mysqli_fetch_assoc($results)){

		$decoded = jsonDecode($result['WMI_Data'], true);

		$Version = $decoded['json']['Value'];



		if($Version != $Version_last){

			$retArray[] = array("Value"=>$Version, "Date"=>$result['last_update']); 	

			$count++;

		}

		$Version_last = $Version;

		if($count == $max){break;}

	}

	?>

	<table class="table table-striped">

		<tr style="text-align:center;"><td colspan=2><b>Agent Version</b></td></tr>

		<?php foreach($retArray as $item){?>

			<tr>

				<td title="<?php echo $item['Date'];?>">

					<?php echo ago($item['Date']);?>

				</td>

				<td><b><?php echo $item['Value'];?></b></td>

			</tr>

		<?php }?>

	</table>

<?php }?>