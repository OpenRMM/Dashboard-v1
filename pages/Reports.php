<?php
$computerID = $_GET['ID'];
$query = "SELECT * FROM users";
$results = mysqli_num_rows(mysqli_query($db, $query));

$gets = clean(base64_decode($_GET['other']));
$get = explode("|", $gets);

?>

<form method="POST">
    <div id="initPage" style="width:100%;padding:15px;">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="padding-left:20px;">
                <div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
                    <div style="height:45px" class="panel-heading">
                        <h6 class="panel-title">
                            Reports Category
                        </h6>
                    </div>
                    <ul class="list-group">
                        <li onclick="loadSection('Reports','','','schedule');" style="cursor:pointer;<?php if($get[0]=="schedule" or $get[0]==""){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            Schedule Reports
                        </li>
                        <li onclick="loadSection('Reports','','','custom');" style="cursor:pointer;<?php if($get[0]=="custom"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            Custom Reports
                        </li>
                        <li onclick="loadSection('Reports','','','inventory');" style="cursor:pointer;<?php if($get[0]=="inventory"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            Inventory Reports
                        </li>					
                    </ul>
                </div>	
            </div>
                <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding-left:20px;">              
                    <div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
                        <div class="card-body">
                            <h5>
                                <?php if($get[0]=="schedule" or $get[0]==""){   $name="schedule"; ?>
                                <?php }elseif($get[0]=="custom"){ $name="custom"; ?>
                                <?php }else{ $name="inventory"; } ?>                                                   
                                <?php echo ucwords($name); ?> Reports <span style="color:orange">(Development in progress)</span>
                                <div style="float:right;display:inline" class="btn-group">
                                    <button style="background:#0c5460;color:#d1ecf1" data-bs-toggle="modal" data-bs-target="#editReport" type="button" class="btn btn-sm"><i class="fas fa-plus"></i> &nbsp;Add <?php echo ucwords($name); ?> Report</button>
                                    <button style="background:#0c5460;color:#d1ecf1"type="button" class="btn dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-sort-down"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a  data-bs-toggle="modal" data-bs-target="#reportRetentionModal"  class="dropdown-item" href="javascript:void(0)">Configure Report Retention Period</a>
                                    </div>
                                </div>
                            </h5>
                        </div>
                    </div>
                            <?php if($get[0]=="schedule" or $get[0]==""){  ?>        
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="card table-card" style="margin-top:0px;padding:10px"> 
                                            <div style="margin-left:0px;margin-top:0px" class="row ">							
                                                <div style="padding:20px;border-radius:6px" class=" col-sm-12">     
                                                    <table class="table table-hover table-borderless table-striped" id="datatable">
                                                        <tr>
                                                            
                                                            <th>Scheduler Name</th>
                                                            <th >Last Run Time</th>
                                                            <th>Next Run Time</th>
                                                            <th>Email To</th>
                                                            <th>Frequency</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                        <?php
                                                        $count=0;
                                                       if($count>5){
                                                        ?>
                                                        <tr id="alert<?php echo $alert['ID']; ?>">
                                                            <td><?php echo $alert['name']; ?></td>
                                                            <td>If <b><?php echo $details['json']['Details']['Condition']."</b> ".$details['json']['Details']['Comparison']." ".$details['json']['Details']['Value']; ?></td>
                                                            <td><?php echo $company; ?></td>
                                                            <td>
                                                                <button type="button" onclick="deleteAlert('<?php echo $alert['ID']; ?>')" title="Delete Alert" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>									
                                                            </td>
                                                        </tr>
                                                        <?php } 
                                                        if($count == 0){ ?>
                                                            <tr>
                                                                <td colspan=5><center><h6>Once you create a report, it will show up here.</h6></center></td>
                                                            </tr>
                                                        <?php }?>
                                                     </table>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            <?php } ?>                   
                            <?php if($get[0]=="custom"){ ?>
                               <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="card table-card" style="margin-top:0px;padding:10px"> 
                                            <div style="margin-left:0px;margin-top:0px" class="row ">							
                                                <div style="padding:20px;border-radius:6px" class=" col-sm-12">     
                                                    <table class="table table-hover table-borderless table-striped" id="datatable">
                                                        <tr>
                                                            <th>Report Name</th>
                                                            <th >Owner</th>
                                                            <th>Last Modified</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                        <?php
                                                        $count=0;
                                                        if($count>5){
                                                        ?>
                                                        <tr id="alert<?php echo $alert['ID']; ?>">
                                                            <td><?php echo $alert['name']; ?></td>
                                                            <td>If <b><?php echo $details['json']['Details']['Condition']."</b> ".$details['json']['Details']['Comparison']." ".$details['json']['Details']['Value']; ?></td>
                                                            <td><?php echo $company; ?></td>
                                                            <td>
                                                                <button type="button" onclick="deleteAlert('<?php echo $alert['ID']; ?>')" title="Delete Alert" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>									
                                                            </td>
                                                        </tr>
                                                        <?php } 
                                                        if($count == 0){ ?>
                                                            <tr>
                                                                <td colspan=4><center><h6>Once you create a report, it will show up here.</h6></center></td>
                                                            </tr>
                                                        <?php }?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if($get[0]=="inventory"){  ?>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="card table-card" style="margin-top:0px;padding:10px"> 
                                            <div style="margin-left:0px;margin-top:0px" class="row ">							
                                                <div style="padding:20px;border-radius:6px" class=" col-sm-12">     
                                                    <table class="table table-hover table-borderless table-striped" id="datatable">
                                                        <tr>                                                
                                                            <th>Report Name</th>
                                                            <th >Owner</th>
                                                            <th>Last Modified</th>
                                                            <th>Actions</th>                                                           
                                                        </tr>
                                                        <?php
                                                        $count=0;
                                                    if($count>5){
                                                        ?>
                                                        <tr id="alert<?php echo $alert['ID']; ?>">
                                                            <td><?php echo $alert['name']; ?></td>
                                                            <td>If <b><?php echo $details['json']['Details']['Condition']."</b> ".$details['json']['Details']['Comparison']." ".$details['json']['Details']['Value']; ?></td>
                                                            <td><?php echo $company; ?></td>
                                                            <td>
                                                                <button type="button" onclick="deleteAlert('<?php echo $alert['ID']; ?>')" title="Delete Alert" style="margin-top:-2px;padding:12px;padding-top:8px;padding-bottom:8px;border:none;" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>									
                                                            </td>
                                                        </tr>
                                                        <?php } 
                                                        if($count == 0){ ?>
                                                            <tr>
                                                                <td colspan=4><center><h6>Once you create a report, it will show up here.</h6></center></td>
                                                            </tr>
                                                        <?php }?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            <?php } ?>                      
                        </div>
                    </div>
                </div>          
            </div>
        </div>   
    </div>
</form>
<?php if($results==0){ ?> 
    <div id="pageAlert" class="modal fade" role="dialog">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="pageAlert_title"><?php if($db and $mqttConnect!="timeout"){ ?>Congratulations!<?php }else{ echo "New Admin User"; } ?></h6>
                </div>
                <form method="post">			 
                    <div class="modal-body">           
                        <p>
                            <?php if($db and $mqttConnect!="timeout"){ ?> 
                                You got us all set up. You are almost ready to use our Remote Monitoring and Management solution.<b> Lastly, we need to setup some login information.</b>
                            <?php }else{ ?>
                                You are almost ready to use our Remote Monitoring and Management solution.<b> We need to setup a new admin user that way you can get signed in.</b>
                            <?php } ?>
                            </p>
                        <div class="form-group float-label-control">
                            <label>Username:</label>
                            <input required type="text" name="username" value="" class="form-control" placeholder="">
                        </div>
                        <div class="form-group float-label-control">
                            <label>Password:</label>
                            <input required type="password" name="password" value="" class="form-control" placeholder="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" value="AddNewUser" name="type">
                        <button type="submit" class="btn btn-sm btn-warning">Continue&nbsp;&nbsp;<i class="fas fa-arrow-right"></i></button>
                    </div>
                </form>	
            </div>
        </div>
    </div>
<?php }
 if($db and $mqttConnect!="timeout"){ ?>
    <script>
        $("#pageAlert").modal("show");
    </script>
<?php } ?>
<script>   
   function showInit(){
        $('#init').slideToggle();
        if ($('#initButton').html().includes('Show more')){ 
            $('#initButton').html('Show less &nbsp;<i class="fas fa-chevron-up"></i>');
            $('#initPage').hide();
            $('#saveBar').fadeOut();
        }else{                 
            $('#initButton').html('Show more &nbsp;<i class="fas fa-chevron-down"></i>');
            $('#initPage').show();
            $('#saveBar').fadeIn();
        }; 
    }
</script>
<script>
    var rangeSlider = function(){
    var slider = $('.range'),
        range = $('.range-slider__range'),
        value = $('.range-slider__value');
    slider.each(function(){
        value.each(function(){
        var value = $(this).prev().attr('value');		
        if(this.value==0){
            $(this).html("Disabled");
        }else{
            $(this).html(value+" minutes");
        }
        });
        range.on('input', function(){
            if(this.value==0){
                $(this).next(value).html("Disabled");
            }else{
                $(this).next(value).html(this.value+" minutes");
            }
        });
    });
};
rangeSlider();
</script>
