<?php
$computerID = $_GET['ID'];
$query = "SELECT * FROM users";
$results = mysqli_num_rows(mysqli_query($db, $query));

$gets = clean(base64_decode($_GET['other']));
$get = explode("|", $gets);
if($_SESSION['accountType']!="Admin"){
    $get="profile";
    /*
    if($db and $mqttConnect!="timeout" and $results!="0"){ 
        $_SESSION['excludedPages'] = explode(",",$excludedPages);
        ?>
        <script> 
            loadSection('Login');
            setCookie("section", btoa("Login"), 365);	
        </script>
    <?php 
    exit;
    }
    */
}

if(!$db or $mqttConnect=="timeout" or $results==0){ 
    $show=true;
}else{
    $show=false;
}
?>
<?php if($_SESSION['accountType']=="Admin" or !file_exists("config.php")){ ?>
<div class="card shadow white" style="color:#fff;background:#343a40;padding:10px;">
    <h5 style="color:#fff;?>">OpenRMM Initialization <?php if(!$show){ echo "<span style='color:#01A9AC'>(Configuration Completed)</span>"; }?>
        <button title="Refresh" onclick="loadSection('Init');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
            <i class="fas fa-sync"></i>
        </button>
        <?php if(!$show){ ?>
            <center>
                <button class="btn btn-light btn-sm" id="initButton"onclick="showInit();" style="margin-top:5px;min-width:10%;text-align:center">Show more &nbsp;<i class="fas fa-chevron-down"></i></button>
            </center>
        <?php } ?>
    </h5>
</div>

<div id="init" style="<?php if(!$show){ echo "display:none"; } ?>">
    <div style="padding:20px;margin-bottom:-1px;" class="shadow card">
        <div class="row" style="padding:15px;">	
            <div class="col-md-9">
                <h5 style="color:#0c5460;font-size:16px">
                Welcome to OpenRMM. You first need to configure a few settings before you get started. 
                </h5>
                <span style="font-size:12px;color:#666;color:maroon"> 
                    You will need A MySQL server and a MQTT broker to continue.
                </span>
            </div>
            <div class="col-md-3" style="text-align:right;">
        
            </div>
        </div>
    </div>
    <div style="width:100%;backgrdound:#fff;padding:15px;">
        <form method="post">
            <div class="row">
                <?php $host = explode(":",$siteSettings['MySQL']['host']); ?>    
                <div class="col-sm-3 mx-auto">	
                    <div style="height:100%" class="shadow panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                MySQL Database
                            </h4>
                        </div>
                        <div class="panel-body">			
                            <div class="form-group float-label-control">
                                <label>Host:</label>
                                <input required type="text" name="mysqlHost" value="<?php echo $host[0]; ?>" class="form-control" placeholder="">
                            </div>
                            <div class="form-group float-label-control">
                                <label>Port:</label>
                                <input required type="text" name="mysqlPort" value="<?php echo $host[1]; ?>" class="form-control" placeholder="">
                            </div>
                            <div class="form-group float-label-control">
                                <label>Database Name:</label>
                                <input required type="text" name="mysqlDatabase" value="<?php echo $siteSettings['MySQL']['database']; ?>" class="form-control" placeholder="">
                            </div>
                            <div class="form-group float-label-control">
                                <label>Username:</label>
                                <input required type="text" name="mysqlUsername" value="<?php echo $siteSettings['MySQL']['username']; ?>" class="form-control" placeholder="">
                            </div>
                            <div class="form-group float-label-control">
                                <label>Password:</label>
                                    <input required type="password" name="mysqlPassword" value="" class="form-control" placeholder="">
                            </div>                 
                        </div>		
                    </div>
                </div>       
                <div class="col-sm-3 mx-auto">	
                    <div style="height:100%" class="shadow panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                            MQTT Broker
                            </h4>
                        </div>
                        <div class="panel-body">
                            <div class="form-group float-label-control">
                                <label>Host:</label>
                                <input required type="text" name="mqttHost" value="<?php echo $siteSettings['MQTT']['host']; ?>" class="form-control" placeholder="">
                            </div>
                            <div class="form-group float-label-control">
                                <label>Port:</label>
                                <input required type="text" name="mqttPort" value="<?php echo $siteSettings['MQTT']['port']; ?>" class="form-control" placeholder="">
                            </div>
                            <div class="form-group float-label-control">
                                <label>Username:</label>
                                <input required type="text" name="mqttUsername" value="<?php echo $siteSettings['MQTT']['username']; ?>" class="form-control" placeholder="">
                            </div>
                            <div class="form-group float-label-control">
                                <label>Password:</label>
                                <input required type="password" name="mqttPassword" value="" class="form-control" placeholder="">
                            </div>                 
                        </div>		
                    </div>
                </div>  
                <?php if(!$show){  ?>
                <div class="col-sm-3 mx-auto">
                    <div style="height:100%" class="shadow panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                Encryption
                            </h4>
                        </div>
                        <div  class="panel-body">
                            <div class="form-check" style="border-radius:6px;margin-bottom:10px;padding:10px;padding-left:10px;color:#333;">
                            <b>Agent Encryption</b><br><br>
                            <div style="margin-left:5px" class="form-group float-label-control">                       
                                <label>Secret Key:</label> <a id="showbtn" href="#" onclick="$('#agentSecret').attr('type', 'text');$('#showbtn').hide();">Show</a>
                                <input readonly required type="password" id="agentSecret"  value="<?php echo $siteSettings['agentEncryption']['secret']; ?>" class="form-control" placeholder="">
                            </div> 
                            
                            <button type="button" onclick="resetKeys();" style="margin-left:5px" class="btn btn-sm btn-danger" >Regenerate All Keys</button>                        
                            </div>
                        </div>
                    </div>
                </div>	
                <div class="col-sm-3 mx-auto">
                <? }else{ ?>	  
                    <div class="col-sm-6 mx-auto">
                <?php } ?>
                    <div style="height:100%" class="shadow panel panel-default">
                        <div class="panel-heading">
                                <h4 class="panel-title">
                                    Setup Progress
                                </h4>
                            </div>
                            <div  class="panel-body">
                                <div class="form-check" style="border-radius:6px;margin-bottom:10px;padding:10px;padding-left:10px;color:#333;">
                                    <ul class="list-group">
                                        <li class="secbtn list-group-item"><b>Database: </b><?php if(!$db){ echo "<span style='color:red'>Cannot connect to database</span>"; }else{ echo "<span style='color:green'>Connected</span>"; } ?></li>
                                        <li class="secbtn list-group-item"><b>MQTT Broker: </b><?php if($mqttConnect=="timeout"){ echo "<span style='color:red'>Cannot connect to broker</span>"; }else{ echo "<span style='color:green'>Connected</span>"; } ?></li>
                                        <li class="secbtn list-group-item"><b>User Count: </b> <?php if($results==0){ echo "<span style='color:red'>0</span>"; }else{ echo "<span style='color:green'>1 or more</span>"; } ?></li>
                                    </ul>
                                    
                                    <div style="margin-top:30px;">
                                            <input type="hidden" value="init" name="type">
                                            <?php if($results==0 and $db){ ?>  
                                                <button type="button"  data-bs-toggle="modal" data-bs-target="#pageAlert" class="btn btn-secondary btn-sm"><i class="fas fa-plus"></i>&nbsp;&nbsp;Create Admin User</button>
                                            <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>	
                </div>
                <div style="bottom:0;position:fixed;float:right;width:100%;background:#fff;border-top:1px solid #d3d3d3d3;padding:10px;margin-left:-15px;z-index:1;overflow:hidden">
                    <center>
                        <button onclick="loadSection('Dashboard');" style="width:100px" class="btn btn-light btn-sm">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> &nbsp;Save Changes</button>
                    </center>
                </div>
            </div>
        </form>	
     </div>    	
</div>
<?php } ?>
<?php if(!$show){ ?>
<form method="POST">
    <div id="initPage" style="width:100%;padding:15px;">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3" style="padding-left:20px;">
                <div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
                    <div style="height:45px" class="panel-heading">
                        <h6 class="panel-title">
                            OpenRMM Configuration 
                        </h6>
                    </div>
                    <ul class="list-group">
                    <?php if($_SESSION['accountType']=="Admin"){ ?>
                        <li onclick="loadSection('Init','','','general');" style="cursor:pointer;<?php if($get[0]=="general" or $get[0]==""){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            General Settings
                        </li>
                        <li onclick="loadSection('Init','','','agent');" style="cursor:pointer;<?php if($get[0]=="agent"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            Default Agent Configuration
                        </li>
                    <?php } ?>
                        <li onclick="loadSection('Init','','','profile');" style="cursor:pointer;<?php if($get[0]=="profile"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            User Settings
                        </li>	
                        <li onclick="loadSection('Init','','','software');" style="cursor:pointer;<?php if($get[0]=="software"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            Software Inventory
                        </li>
                        <li onclick="loadSection('Init','','','script');" style="cursor:pointer;<?php if($get[0]=="script"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            Script Inventory
                        </li>					
                    </ul>
                </div>	
                <?php if($get[0]=="agent"){ ?>
                    <div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
                        <div style="height:auto" class="panel-heading">
                            <h5 style="font-size:14px;" class="panel-title">
                                Default configurations per <?php echo strtolower($msp); ?> 
                            <hr>
                                <p style="padding:3px;font-size:13px"> 
                                    Select a <?php echo strtolower($msp); ?> to edit its default agent configuration. 
                                    <span style="color:red">
                                        Changes here will not affect existing assets.
                                    </span>
                                </p>
                            </h5>
                        </div>
                        <ul class="list-group">
                            <li onclick="loadSection('Init','','','agent|');" style="cursor:pointer;<?php if($get[1]==""){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                                Base Defaults
                            </li>
                            <?php
                                $query = "SELECT ID, name,hex FROM companies WHERE active='1' ORDER BY ID ASC";
                                $results = mysqli_query($db, $query);
                                while($result = mysqli_fetch_assoc($results)){ 
                                    if($result['ID']==$company['ID']){continue;}		
                            ?>
                                <li onclick="loadSection('Init','','','agent|<?php echo $result['ID']; ?>');" style="cursor:pointer;<?php if($get[1]==$result['ID']){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                                    <?php echo crypto('decrypt',$result['name'],$result['hex']);?>
                                </li>
                            <?php }?>			
                        </ul>
                    </div>
                <?php } ?>
            </div>
                <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding-left:20px;">
                    <div class="card user-card2" style="width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
                        <div class="card-body">
                            <?php if($get[0]=="general" or $get[0]==""){  ?>
                                <h5>General Settings</h5>
                            <?php }elseif($get[0]=="agent"){ ?>
                                <h5>Default Agent Configuration 
                                <?php if($get[1]!=""){
                                    $query = "SELECT ID,name,hex FROM companies WHERE ID='".$get[1]."' LIMIT 1";
                                    $data = mysqli_fetch_assoc(mysqli_query($db, $query));
                                    echo " | ".crypto('decrypt',$data['name'],$data['hex']);
                                }?>
                                </h5>
                            <?php }elseif($get[0]=="software"){ ?>
                                <h5>Software Inventory</h5>
                            <?php }elseif($get[0]=="script"){ ?>
                                <h5>Script Inventory</h5>
                            <?php }else{ ?>                    
                                <h5>User Settings</h5>
                            <?php } ?>
                            </div>
                        </div>
                            <?php if($get[0]=="profile"){  ?>                     
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="card table-card" style="margin-top:0px;padding:20px"> 
                                                <div style="margin-left:50px;margin-top:0px" class="row ">							
                                                    <div style="padding:20px;border-radius:6px" class=" col-sm-12">     
                                                        <center><h6>No Settings Yet</h6></center>
                                                     </div>
                                                 </div>
                                             </div> 
                                        </div>
                                    </div>
                            <?php } ?>
                            <?php if($get[0]=="software"){  ?>                            
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="card table-card" style="margin-top:0px;padding:20px"> 
                                            <div style="margin-left:50px;margin-top:0px" class="row ">							
                                                <div style="padding:20px;border-radius:6px" class=" col-sm-12">     
                                                    <center><h6>No Settings Yet</h6></center>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if($get[0]=="script"){  ?>                            
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="card table-card" style="margin-top:0px;padding:20px"> 
                                            <div style="margin-left:50px;margin-top:0px" class="row ">							
                                                <div style="padding:20px;border-radius:6px" class=" col-sm-12">     
                                                    <center><h6>No Settings Yet</h6></center>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                </div>
                            <?php } ?>
                           
                            <?php if($get[0]=="agent"){  
                                   $count=0;
                                   if($get[1]!=""){
                                       $query = "SELECT ID, default_agent_settings FROM companies WHERE ID='".$get[1]."' LIMIT 1";
                                   }else{
                                       $query = "SELECT ID, default_agent_settings FROM general WHERE ID='1' LIMIT 1";
                                   }
                                   $results2 = mysqli_query($db, $query);
                                   $data = mysqli_fetch_assoc($results2);

                                   $agent_settings = json_decode($data['default_agent_settings'],true);    
                            ?>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"> 
                                        <div class="card table-card" style="margin-top:0px;padding:10px">  
                                            <div style="margin-top:-40px" class="card-header"><br>
                                                <h5>Update Settings</h5>
                                                <p></p>
                                                <hr>
                                            </div>
                                            <div style="margin-left:50px;margin-top:-40px" class="row ">							
                                                <div style="padding:20px;border-radius:6px" class=" col-sm-12">
                                                    <div class="row">
                                                        <div class="col-sm-2">
                                                            <?php 
                                                            if($agent_settings["Updates"]['auto_update']=="1"){
                                                                $auto = "checked";
                                                            }else{
                                                                $auto="";
                                                            }
                                                            ?>
                                                            <label>Automatic Updates</label>
                                                            <center>
                                                                <div style="margin-top:10px;" class="custom-control custom-switch">
                                                                    <input <?php echo $auto; ?> type="checkbox" class="custom-control-input" name="defaultAutoUpdate" value="1" id="customSwitches">
                                                                    <label class="custom-control-label" for="customSwitches"></label>
                                                                </div>
                                                            </center>
                                                        </div>
                                                        <div class=" col-sm-5">
                                                            <label class="form-label" for="customRange2">Update URL</label>
                                                            <input placeholder="https://" name="defaultUpdateURL" class="form-control" type="url" value="<?php echo $agent_settings["Updates"]['update_url']; ?>">
                                                        </div>
                                                        <div class=" col-sm-5">
                                                            <label class="form-label" for="customRange2">Update Check Interval</label>
                                                            <div style="margin-top:10px;" class="range">
                                                                <input stylse="width:160px" class="range-slider__range" type="range" name="defaultUpdateInterval" value="<?php echo (int)$agent_settings["Updates"]['check_interval']; ?>" min="0" max="1000">
                                                                <span style="background:#6c757d;color:#fff;" class="range-slider__value">0</span>
                                                            </div>
                                                        </div>									
                                                    </div>
                                                </div>	
                                            </div>	
                                        </div>
                                    </div>
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="card table-card" style="margin-top:0px;"> 
                                                <div style="margin-top:-40px" class="card-header"><br>
                                                    <h5>Update Intervals</h5>
                                                    <p>How often would you like the agent to send data?</p>
                                                    <hr>
                                                </div>      
                                                <input type="hidden" name="type" value="defaultAgentConfig"/>
                                                <input type="hidden" name="ID" value="<?php echo $get[1]; ?>"/>
                                                <div style="padding:40px;padding-top:0px" class="row">
                                                    <?php
                                                 
                                                    //print_r($agent_settings);exit;
                                                    foreach ($agent_settings['Interval'] as $setting => $val) {
                                                        $setting_new = str_replace("_"," ", $setting);
                                                        $count++;	
                                                    ?>
                                                        <div class=" col-sm-4">
                                                            <label class="form-label" for="customRange2"><?php echo ucwords($setting_new); ?></label>
                                                            <div class="range">
                                                                <input class="range-slider__range" type="range" name="agent_<?php echo $setting; ?>" value="<?php echo $val; ?>" min="0" max="360">
                                                                <span style="background:#6c757d;color:#fff" class="range-slider__value">0</span>
                                                            </div>
                                                        </div>
                                                    <?php } 
                                                    if($count==0){
                                                    ?>
                                                        <div class="col-sm-12" style="margin-top:10px">
                                                            <center>
                                                                <h6>An error has occurred while trying to load the settings for the selected <?php echo strtolower($msp); ?>.</h6>
                                                            </center>
                                                        </div>
                                                    <?php }	?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                
                            <?php } ?>
                            <?php if($get[0]=="general" or $get[0]=="" and $_SESSION['accountType']=="Admin"){  ?>
                                <div class="card table-card" style="margin-top:0px;padding:20px"> 
                                     
                                    <input type="hidden" name="type" value="initGeneral">
                                    <div class="row">       
                                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                            <div style="display:inline" class="form-group">
                                                <label for="email">Are you an MSP? <span style="color:red">*</span></label>
                                                <?php
                                                if($siteSettings['theme']['MSP']=="true"){ 
                                                    $msp2 = "Yes"; 
                                                }else{
                                                    $msp2="No";
                                                }   
                                                ?>
                                                <select required type="text"  name="msp" class="form-select" id="pwd">
                                                    <option><?php echo $msp2; ?></option>
                                                    <option value="true">Yes</option>
                                                    <option value="false">No</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                            <div style="display:inline" class="form-group">
                                                <label for="pwd">Service Desk <span style="color:red">*</span></label>
                                                <select required type="text"  name="serviceDesk" class="form-select" id="pwd2">
                                                    <option value="<?php echo $siteSettings['Service_Desk']; ?>"><?php echo $siteSettings['Service_Desk']; ?></option>
                                                    <option value="Enabled">Enabled</option>
                                                    <option value="Disabled">Disabled</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>                      
                        </div>
                    </div>
                </div>
                <div id="saveBar" style="bottom:0;position:fixed;float:right;width:100%;background:#fff;border-top:1px solid #d3d3d3d3;padding:10px;margin-left:-15px;z-index:1;overflow:hidden">
                    <center>
                        <button onclick="loadSection('Dashboard');" style="width:100px" class="btn btn-light btn-sm">Cancel</button>
                        <button type="submit" style="width:120px" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> &nbsp;Save Changes</button>
                    </center>
                </div>          
            </div>
        </div>   
    </div>
</form>
<?php } ?>
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
