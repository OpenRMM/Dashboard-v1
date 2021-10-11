<?php
$computerID = $_GET['ID'];
$query = "SELECT * FROM users";
$results = mysqli_num_rows(mysqli_query($db, $query));

if($_SESSION['accountType']!="Admin"){
    if($db and $mqttConnect!="timeout" and $results!="0"){ 
        $_SESSION['excludedPages'] = explode(",",$excludedPages);
        ?>
        <script> 
            loadSection('Login');
            setCookie("section", "Login", 365);	
        </script>
    <?php 
    exit;
    }
}
?>
<h4 style="color:#333;?>">OpenRMM Initialization
	<a href="#" title="Refresh" onclick="loadSection('Init');" class="btn btn-sm" style="float:right;margin:5px;color:#fff;background:#333;">
		<i class="fas fa-sync"></i>
	</a>
</h4>
<hr>
<div style="width:100%;backgrdound:#fff;padding:15px;">
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
		<div class="col-sm-3 mx-auto">	
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
        <div class="col-sm-3 mx-auto">	
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
        <div class="col-sm-3 mx-auto">
			<div class="panel panel-default">
			   <div class="panel-heading">
					<h4 class="panel-title">
						Color Scheme
					</h4>
				</div>
				<div class="panel-body">
                        <div class="list-group" style="padding-bottom:10%">
                            <input type="radio" name="theme" value="theme1" checked id="Radio1" required />
                            <label class="list-group-item" for="Radio1">
                                <span style="text-align:left">Default</span>
                                <center>
                                    <p style="background:#f0f0f0;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#fe6f33;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#0ac282;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#eb3422;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#01a9ac;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                </center>
                            </label>
                            <input type="radio" name="theme" value="theme2" id="Radio2" required />
                            <label class="list-group-item" for="Radio2"> 
                                <span style="text-align:left">Bland</span> 
                                <center>
                                    <p style="background:#fff;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#333;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#a4b0bd;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#696969;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#595f69;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                </center>
                            </label>

                            <input type="radio" name="theme" value="theme3" required id="Radio3" />
                            <label class="list-group-item" for="Radio3">
                                <span style="text-align:left">Greenly</span>
                                <center>
                                    <p style="background:#f3f3f3;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#0ac282;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#a4b0bd;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#333;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                    <p style="background:#595f69;border-radius:5px;display:inline;border:1px solid #d3d3d3">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
                                </center>
                            </label>
                        </div>
				</div>
			</div>
        </div>       
        <div class="col-sm-3 mx-auto">
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
