<?php
include("db.php");
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  // do nothing
}else {
    // if page is not called via ajax
   exit("<html><head><title>OpenRMM console</title></head><body style='background:#000'><div style='margin-top:20px;color:#fff;font-family: \"Lucida Console\", \"Courier New\", monospace;font-size:14px'><h3>OpenRMM console > An error has occurred</h3></div></body></html>");
}
$name = clean(base64_decode($_GET['name']));
$key = clean(base64_decode($_GET['key']));
$ID = (int)base64_decode($_GET['ID']);
$query = "SELECT * FROM changelog WHERE computer_data_name='".$name."'";
if($key!="null"){
    $query.= " and computer_data_key='".$key."'";
}
$query .= " and computer_id='".$ID."' ORDER BY ID DESC LIMIT 70";
?>
<table id="dataTable4" style="line-height:50px;overflow:auto;font-size:12px;font-family:Arial;width:100%" class="table table-responsive table-hover table-borderless">
    <thead>
        <tr>
            <?php if($key=="null"){ ?>
                <th>Key</th>
            <?php } ?>
            <th>Data</th>
            <th></th>
            <th>Date/Time</th>
        </tr>
    </thead>
    <tbody>
<?php

//Fetch Results
$count = 0;
$results = mysqli_query($db, $query);
$resultCount = mysqli_num_rows($results);	
while($result = mysqli_fetch_assoc($results)){
    $count++;
    $old = textOnNull($result['old_value'],"no data");
    $new = ($result['new_value']);
    $time = ($result['date_added']);

    if(is_numeric($old) and is_numeric($new)){
        if($old > $new){
            $icon = "<i title='Value Went Down' class='fas fa-arrow-down' style='color:green'></i>";
        }else{
            $icon = "<i title='Value Went Up' class='fas fa-arrow-up' style='color:red'></i>";
        }
    }
?>
    <tr >
        <?php if($key=="null"){ ?>
                <td><?php echo textOnNull($result['computer_data_key'],"null"); ?></td>
        <?php } ?>
        <td>
            <?php print_r($old) ?> &nbsp;<i title="Old Value -> New Value" class="fas fa-arrow-right"></i> &nbsp;<?php print_r($new) ?>     
        </td>
        <td>
            <span ><?php echo $icon; ?></span>
        </td>
        <td>
            <span title="Time The Value Changed" ><?php echo $time; ?></span>
        </td>
    </tr>
<?php } ?>
<?php  if($count==0){ ?>
    <tr>
        <td colspan=3>    
            <center>
                <h6>No Older Data To Display</h6>
            </center>   
        </td>
    </tr> 
 <?php } ?>
<?php echo "</tbody></table>"; ?>
<script>
    $('#dataTable4').DataTable({
        "pageLength": 5,
        colReorder: false,
        "lengthChange": false,
        "searching": false
    });
</script>