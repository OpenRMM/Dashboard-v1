<?php
$computerID = $_GET['ID'];
$query = "SELECT * FROM users";
$results = mysqli_num_rows(mysqli_query($db, $query));

$get = clean(base64_decode($_GET['other']));

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
<?php if($_SESSION['accountType']=="Admin"){ ?>
<h5 style="color:#333;?>">OpenRMM Initialization <?php if(!$show){ echo "<span style='color:green'>(completed)</span>"; }?>
	<button title="Refresh" onclick="loadSection('Init');" class="btn btn-sm" style="float:right;margin:5px;color:#0c5460;background:<?php echo $siteSettings['theme']['Color 2'];?>;">
		<i class="fas fa-sync"></i>
    </button>
    <?php if(!$show){ ?>
        <center>
            <button class="btn btn-light btn-sm" id="initButton"onclick="showInit();" style="margin-top:5px;min-width:10%;text-align:center">Show more &nbsp;<i class="fas fa-chevron-down"></i></button>
        </center>
    <?php } ?>
</h5>
<hr>
<div id="init" style="width:100%;backgrdound:#fff;padding:15px;<?php if(!$show){ echo "display:none"; } ?>">
	<p style="font-size:16px">
	   Welcome to OpenRMM. You first need to configure a few settings before you get started. 
        <br>
        <small class="text-muted"> 
            <span style="color:maroon">
                You will need A MySQL server and a MQTT broker to continue.
            </span>
        </small>
	</p>
	<hr />
    <form method="POST">
	<div class="row">
        <?php $host = explode(":",$siteSettings['MySQL']['host']); ?>
		<div class="col-sm-4 mx-auto">	
             <div class="panel panel-default">
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
        <div class="col-sm-4 mx-auto">	
             <div class="panel panel-default">
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
        <div class="col-sm-4 mx-auto">
			<div class="panel panel-default">
			   <div class="panel-heading">
					<h4 class="panel-title">
						Setup Progress
					</h4>
				</div>
				<div  class="panel-body">
                    <div class="form-check" style="border-radius:6px;margin-bottom:10px;padding:10px;padding-left:50px;color:#333;">
                        <b>Database: </b><?php if(!$db){ echo "<span style='color:red'>Cannot connect to database</span>"; }else{ echo "<span style='color:green'>Connected</span>"; } ?><br>
                        <b>MQTT Broker: </b><?php if($mqttConnect=="timeout"){ echo "<span style='color:red'>Cannot connect to broker</span>"; }else{ echo "<span style='color:green'>Connected</span>"; } ?><br>
                        <b>User Count: </b> <?php if($results==0){ echo "<span style='color:red'>0</span>"; }else{ echo "<span style='color:green'>1 or more</span>"; } ?>
                        <hr>
                        <div style="margin-top:30px;">
                                <input type="hidden" value="init" name="type">
                                <?php if($results==0 and $db){ ?>  
                                    <button type="button"  data-toggle="modal" data-target="#pageAlert" class="btn btn-secondary btn-sm">Create Admin User&nbsp;&nbsp;<i class="fas fa-plus"></i></button>
                                <?php } ?>
                                <button type="submit"  class="btn btn-success btn-sm">Save Details&nbsp;&nbsp;<i class="fas fa-save"></i></button>
                        </div>
                    </div>
				</div>
			</div>
        </div>
	</form>	
		</div>
	</div>
</div>
<?php } ?>
<?php if(!$show){ ?>
<div style="width:100%;padding:15px;">
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
                        <li onclick="loadSection('Init','','','general');" style="cursor:pointer;<?php if($get=="general" or $get==""){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            General Settings
                        </li>
                    <?php } ?>
                        <li onclick="loadSection('Init','','','profile');" style="cursor:pointer;<?php if($get=="profile"){echo "background:#343a40;color:#fff";} ?>" class="list-group-item secbtn">
                            User Settings
                        </li>					
                    </ul>
                </div>	
            </div>
            
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9" style="padding-left:20px;">
                <div class="card user-card2" style="min-height:200px;width:100%;box-shadow:rgba(69, 90, 100, 0.08) 0px 1px 20px 0px;">
                    <div class="card-body">
                         <?php if($get=="general" or $get==""){  ?>
                            <h5>General Settings</h5>
                        <?php }else{ ?>
                            <h5>User Settings</h5>
                        <?php } ?>
                        <hr>
                        <?php if($get=="profile"){  ?>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">       
                                    <center><h6>No Settings Yet</h6></center>
                                    </div>
                                </div>
                                <button type="submit" style="float:right;margin-top:300px" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> &nbsp;Save Changes</button>  
                            </form> 
                        <?php } ?>
                        <?php if($get=="general" or $get=="" and $_SESSION['accountType']=="Admin"){  ?>
                            <form method="POST">
                                <input type="hidden" name="type" value="initGeneral">
                                <div class="row">       
                                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                        <div style="display:inline" class="form-group">
                                            <label for="email">Are you an MSP? <span style="color:red">*</span></label>
                                            <?php
                                            if($siteSettings['theme']['MSP']=="true"){ 
                                                $msp = "Yes"; 
                                            }else{
                                                $msp="No";
                                            }   
                                            ?>
                                            <select required type="text"  name="msp" class="form-control" id="pwd">
                                                <option><?php echo $msp; ?></option>
                                                <option value="true">Yes</option>
                                                <option value="false">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                        <div style="display:inline" class="form-group">
                                            <label for="pwd">Service Desk <span style="color:red">*</span></label>
                                            <select required type="text"  name="serviceDesk" class="form-control" id="pwd">
                                                <option value="<?php echo $siteSettings['Service_Desk']; ?>"><?php echo $siteSettings['Service_Desk']; ?></option>
                                                <option value="Enabled">Enabled</option>
                                                <option value="Disabled">Disabled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                               
                                <button type="submit" style="float:right;margin-top:20px" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> &nbsp;Save Changes</button>  
                            </form> 
                        <?php } ?>
                     </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<footer style="z-index:999;padding:5px;height:30px;position: fixed;left: 0;bottom: 0;width: 100%;color:#fff;text-align: center;background:<?php echo $siteSettings['theme']['Color 1'];?>" class="page-footer font-small black">
    <div class="footer-copyright text-center ">© <?php echo date('Y');?> Copyright
        <a style="color:#fff" href="https://github.com/OpenRMM"> OpenRMM</a>
    </div>
</footer>
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
        }else{                 
            $('#initButton').html('Show more &nbsp;<i class="fas fa-chevron-down"></i>');
        }; 
    }
</script>
