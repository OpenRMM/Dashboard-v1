<?php if($_SESSION['userid']!=""){ ?>
		<!-------------------------------MODALS------------------------------------>
			<!--------------- User Modal ------------->
			<div id="userModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Add/Edit Technician
								</b>
							</h6>
						</div>
						<form id="userform" method="POST">
							<input type="hidden" name="type" value="AddEditUser"/>
							<input type="hidden" name="ID" id="editUserModal_ID"/>
							<div class="modal-body">
								<p>This will configure a new technician and will allow them access to this platform.</p>
								<div class="row">
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Name</label>
										<input placeholder="Name" minlength="4" required pattern="[[A-Z]+[\\s]?]+" type="text" name="name" class="form-control" id="editUserModal_name"/>
									</div>
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Email</label>
										<input placeholder="Email"  type="email" name="email" class="form-control" id="editUserModal_email"/>
									</div>
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Username</label>
										<input placeholder="Username"  required type="text" minlength="4" name="username" class="form-control" id="editUserModal_username"/>
									</div>
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Phone</label>
										<input placeholder="Phone (ex. 1234567890)" type="tel"  pattern="[0-9]{10}" name="phone" class="form-control" id="editUserModal_phone"/>
									</div>
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Technician Color</label>
										<input placeholder="User Color" type="color" name="color" class="form-control" id="editUserModal_color"/>
									</div>								
								</div>
								<br><hr>
								<div class="row">
									<div class="col-md-6">
										<label for="editUserModal_type">Password</label>
										<div class="input-group">
											<input title="Must contain at least one  number and one uppercase and lowercase letter, and at least 8 or more characters" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" style="display:inline" type="password" id="editUserModal_password" name="password" class="form-control"/>
											<span class="input-group-btn">
												<a style="border-radius:0px;padding:6px;pointer:cursor;color:#fff;" class="btn btn-md btn-success" onclick="generate();" >Generate</a>
											</span>
										</div>
									</div>									
									<div class="col-md-6 form-group">
										<label for="editUserModal_type">Confirm Password</label>
										<input placeholder="Confirm Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" type="password" id="editUserModal_password2" name="password2" class="form-control"/>
									</div>
								</div>
								<hr>
								<div class="row">
									<?php if($_SESSION['accountType']=="Admin"){  ?>
									
										<div class="col-md-4 form-group">
											<label for="editUserModal_type">Access Level</label>
											<select onChange="update()" required name="accountType" id="accessSelect" class="form-select">
												<option  id="editUserModal_type" value="">Select Option</option>
												<option value="Standard">Standard</option>
												<option value="Admin">Admin</option>
											</select>
										</div>
									<?php } ?>
								<?php if($_SESSION['accountType']=="Admin"){ ?>
								<div id="allowed_pages" class="col-md-12 m-auto form-group">
									<hr>
									<h6 style="color:#35384e">Allowed Pages</h6>
										<div style="margin-top:20px;margin-left:10px" class="row">
											<div class="col-md-3 form-group">
												<label class="checkbox-inline">
													<input id="AssetChat" type="checkbox" class="settingsCheckbox" name="settings[]" style="margin-right:5px;" value="AssetChat">Asset Chat
												</label>
											</div>
											<?php 
											foreach ($allPages as $value) { 
												$value2 = trim(str_replace("_","",$value));
												$value3 = $value;
												$value = trim(str_replace("_"," ",$value));	
												$value = str_replace("Asset","",$value);
												$value = str_replace("Service Desk","",$value);
												if($value=="s"){$value="Assets";}
												$value = str_replace("Home","Service Desk",$value);
												$value = str_replace("Edit","Edit Assets",$value);
												$value = str_replace("Versions","Downloads",$value);

												if(trim($value)=="Init"){continue;}
												if(trim($value)=="Logout"){continue;}
												if(trim($value)=="Portal"){continue;}
												if(trim($value)=="Login"){continue;}
												if(trim($value)=="Servers"){continue;}
												if(trim($value)=="Dashboard"){continue;}
												if(trim($value)=="Customers"){continue;}
												if(trim($value)=="Technicians"){continue;}
											?>
												<div class="col-md-3 form-group">
													<label class="checkbox-inline">
														<input id="<?php echo $value2; ?>" type="checkbox" class="settingsCheckbox" name="settings[]" style="margin-right:5px;" value="<?php echo $value3; ?>"><?php echo $value; ?>
													</label>
												</div>
											<?php } ?>
										</div>
									</div>
								<?php } ?>
								</div>		
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-sm" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-sm btn-primary">
									<i class="fas fa-check"></i> Create
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<script>
				function update() {
					var select = document.getElementById('accessSelect');
					var option = select.options[select.selectedIndex];

					if(option.text=="Standard"){
						$("#allowed_pages").slideDown();
					}else{
						$("#allowed_pages").slideUp();
					}
				}
			</script>
			<!--------------- Version Modal ------------->
			<div id="versionModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Delete Version
								</b>
							</h6>
						</div>
						<form id="user" method="POST">
							<input type="hidden" name="version" value="" id="delVersion_ID"/>
							<div class="modal-body">
								<p>This will delete this agent version. Are you sure?</p>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" style="color:#fff" class="btn btn-sm btn-danger">
									<i class="fas fa-trash"></i> Delete
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
				<!--------------- Asset reset password Modal ------------->
				<div id="Asset_Reset_Password_Modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Reset Asset Password
								</b>
							</h6>
						</div>
						<input type="hidden" name="AssetID" value="" id="AssetID"/>
						<input type="hidden" name="AssetUser" value="" id="AssetUser"/>
						<div class="modal-body">
							<p>This will reset the assets password to the one provided.</p>
							<label>New password:</label>
							<input required autocomplete="off" type="password" value="" id="AssetPassword" name="password" class="form-control">								
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Cancel</button>
							<button type="button" onclick="resetAssetPassword();" data-bs-dismiss="modal" style="color:#fff" class="btn btn-sm btn-primary">
								<i class="fas fa-save"></i> Update
							</button>
						</div>					
					</div>
				</div>
			</div>
			<!--------------- Note Modal ------------->
			<div id="noteModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Create A New Note
								</b>
							</h6>
						</div>
					
							<div class="modal-body">
								<p>This Will Create A New Note That Only You And Other Administrators Can See.</p>
								<div class="form-group">
									<label for="noteTitle">Title</label>
									<input id="noteTitle" type="text" class="form-control" placeholder="" name="noteTitle">
								</div>
								<div class="form-group">
									<label for="note">Note</label>
									<textarea id="note" rows="6" required maxlength="300" name="note" class="form-control"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Cancel</button>
								<button data-bs-dismiss="modal" type="button" onclick="newNote()" class="btn btn-sm btn-primary">
									<i class="fas fa-save"></i> Save
								</button>
							</div>
					
					</div>
				</div>
			</div>
			<!--------------- View note Modal ------------->
			<div id="viewNoteModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<h4 id="notetitle">
								<b>
									View Note
								</b>
							</h4>
						</div>
						<div class="modal-body">					
								<h6 style="margin-top:20px"><span id="notedesc"></span></h6>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
		
			<!---------- Company Modal ------------>
			<div id="companyModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Add/Edit <?php echo $msp; ?>
								</b>
							</h6>
						</div>
						<form method="POST">
							<input type="hidden" name="type" value="AddEditCompany"/>
							<input type="hidden" name="ID" value="" id="editCompanyModal_ID"/>
							<div class="modal-body">
								<p>Add <?php echo strtolower($msp); ?> information to organize content and create <?php echo strtolower($msp); ?> based events.</p>
								<div class="row">
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Name</label>
										<input placeholder="Name" type="text" name="name" class="form-control" id="editCompanyModal_name"/>
									</div>
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Address</label>
										<input placeholder="Address" type="text" name="address" class="form-control" id="editCompanyModal_address"/>
									</div>
									<div class="col-md-4 form-group">
										<label for="editUserModal_type">Phone (ex. 1234567890)</label>
										<input placeholder="Phone" pattern="[0-9]{10}" type="tel" name="phone" class="form-control" id="editCompanyModal_phone"/>
									</div>
									<?php if($msp=="Customer"){ ?>
										<div class="col-md-4 form-group">
											<label for="editUserModal_type">Owner</label>
											<input placeholder="Owner"  type="text" name="owner" class="form-control" id="editCompanyModal_owner"/>
										</div>
									<?php } ?>
									<div class="col-md-6 form-group">
										<label for="editUserModal_type">Email</label>
										<input placeholder="Email" type="email" name="email" class="form-control" id="editCompanyModal_email"/>
									</div>
									<div class="col-md-12 form-group">
										<label for="editUserModal_type">Additional Information</label>
										<textarea placeholder="Additional Info" style="resize:vertical" name="comments" class="form-control" placeholder="Optional" id="editCompanyModal_comments"></textarea>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-sm" data-bs-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-sm btn-primary">
									<i class="fas fa-check"></i> Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<!------------- Alerts ------------------->
			<div id="computerAlerts" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6 id="computerAlertsHostname">
								<b>
									Alerts
								</b>
							</h6>
						</div>
						<div class="modal-body">
							<div id="computerAlertsModalList"></div>
						</div>
						<div class="modal-footer">
							<button type="button"  class="btn btn-sm btn-primary" data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!------------- User commands ------------------->
			<div id="userCommandsModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Create Command
								</b>
							</h6>
						</div>
						<form method="post">
							<div class="modal-body">
								<div class="col-md-12 form-group">
									<label>Title</label>
									<input placeholder="ex. Flush DNS" style="resize:vertical" name="title" class="form-control" type="text" placeholder="Title">
								</div>
								<div class="col-md-12 form-group">
									<label>Button Color</label>
									<input placeholder="Button Color" type="color" name="btnColor" class="form-control"/>
								</div>	
								<div class="col-md-12 form-group">
									<label>Command</label>
									<textarea placeholder="ex. ipconfig /flushdns" name="customCommand" class="form-control" style="height:150px;resize-x:none;max-height:300px" placeholder="Optional" ></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-sm btn-primary">Create</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!------------- Older Data ------------------->
			<div id="olderDataModal" class="modal fade" role="dialog">
				<div id="olderDataModalDialog" class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">
								Change Log
							</h5>
						</div>
						<div class="modal-body">
							<div id="olderData_content" style="overflow-x:hidden;overflow-y:auto;max-height:400px;">
								<center>
									<h3 style='margin-top:40px;'>
										<div class='spinner-grow text-muted'></div>
										<div class='spinner-grow' style='color:#0c5460'></div>
									</h3>
								</center>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-primary"  data-bs-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!-------------asset messages ------------------->
			<div id="asset_message_modal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content" style="overflow:hidden" >
						<div class="row clearfix">
							<div  class="col-lg-12">
								<div class="card chat-app">
									<div id="chatDiv" >
										<center>
											<h3 style='margin-top:40px;'>
												<div class='spinner-grow text-muted'></div>
												<div class='spinner-grow' style='color:#0c5460'></div>
											</h3>
										</center>
									</div>
									<div class="chat-message clearfix">
										<div style="width:490px;float:right;margin-right:15px;margin-bottom:-20px" class="input-group">									
											<span class="input-group-text"><i class="fas fa-paper-plane"></i></span>
											<input type="hidden" name="ID" id="asset_message_id"  value="<?php echo $computerID; ?>"> 
											<input type="hidden" name="user_id" id="user_id"  value="<?php echo $_SESSION['userid']; ?>"> 
											<input type="text" oninput="updateLastTypedTime();" id="asset_message" required class="form-control" name="message" placeholder="Enter text here...">   
											<button onclick="sendChat();" class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i> &nbsp;Send</button>                      
										</div>
									</div>									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!------------- Upload .exe File ------------------->
			<div id="agentUpload" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6><b>Upload New Agent (.exe)</b></h6>
						</div>
						<form enctype="multipart/form-data" method="POST">
							<div class="modal-body">
								<p>This Will Create A Downloadable .Zip File. It Will Also Rewrite The Existing Update Directory.</p>
								<div class="input-group">									
									<span class="input-group-text" id="inputGroupFileAddon01">Agent Version *</span>
									<input type="text" name="agentVersion" required minlength=3 class="form-control" placeholder="ex. 1.0.0.4" value="<?php echo $siteSettings['general']['agent_latest_version']; ?>"/>&nbsp;
								</div>
								<hr><br>
								<h6>Would you like to upload a file or update via URL?</h6><br>
								<div class="input-group">
									<span class="input-group-text" id="inputGroupFileAddon01">Update URL</span>
									<input  type="url" name="updateURL"  minlength=6 class="form-control" placeholder="https://" value="<?php echo json_decode($siteSettings['general']['default_agent_settings'],true)['Updates']['update_url']; ?>"/>&nbsp;
								</div>
								<hr><h6>OR</h6><hr>
								<div class="input-group">										
									<input required="" type="hidden" value="true" name="agentFile">
									<input  accept=".py" type="file" name="agentUpload" class="form-control" id="agentUpload"/>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm"  data-bs-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-sm btn-primary" >Upload</button>
							</div>
						</form>
					</div>
				</div>
			</div>
            <!--new task -->
			<div class="modal fade in" id="editTrigger" tabinsdex="-1" role="dialog" aria-hidden="true" >
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" >Create New Task</h5>
						</div>
						<form method="post">
							<div class="modal-body">
								<h6>Task Name</h6>
								<input maxlength="30" required type="text" class="form-control" name="name">
								<br><hr>
								<h6>Conditions</h6>
								<table class="table">
									<tbody id="TextBoxesGroup">
										<tr>
											<th scope="row" style="vertical-align:middle;">IF</th>
											<td>
												<input type="hidden" value="newTask" name="type">                                               
												<select required class="form-select" style="width:23%;display:inline-block;" name="taskCond1">
													<option value="Total Alert Count">Total Alert Count</option>
													<option value="Total Ram/Memory">Total Ram/Memory</option>
													<option value="Available Disk Space">Available Disk Space</option>
													<option value="Total Disk Space">Total Disk Space</option>
													<option value="Domain">Domain</option>
													<option value="Public IP Address">Public IP Address</option>
													<option value="Antivirus">Antivirus</option>

													<option value="Agent Version">Agent Version</option>
													<option value="Total User Accounts">Total User Accounts</option>
													<option value="Command Received">Command Received</option>
													<option value="Agent Comes Online">Agent Comes Online</option>
													<option value="Agent Goes Offline">Agent Goes Offline</option>
													<option value="Windows Activation">Windows Activation</option>
													<option value="Local IP Address">Local IP Address</option>
													<option value="Last Update">Last Update</option>

												</select>

												<select class="form-select" required style="width:20%;display:inline-block;" name="taskCond1Comparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>

												<input type="text" required placeholder="Value" class="form-control" style="width:47%;display:inline-block;" name="taskCond1Value">     
                                                <button type="button" style="margin-left:10px" id="addButton" class="btn btn-sm btn-success"><i class="fa fa-plus"></i></button>
                                            </td>
										</tr>
                                           
                                       
									
									</tbody>
								</table>
								<hr>
								<h6>Action</h6>
								<table class="table" id="actionsTable">
									<thead>
										<tr>
											<th scope="col">Type</th>
											<th scope="col">Argument</th>
										</tr>
									</thead>
									<tbody>		
										<tr>
											<td>
												<select required class="form-select" name="taskAct1">
													<option value="Log">Add to Log</option>
													<option value="Command">Send Command</option>
													<option value="Send Notification">Send Notification</option>
                                                    <option value="Send Alert To User">Send Alert To User</option>
                                                    <option value="Shutdown Asset">Shutdown Asset</option>
                                                    <option value="Restart Asset">Restart Asset</option>
                                                    <option value="Stop Server">Stop Server</option>

												</select>
											</td>
											<td>
												<input type="text" required placeholder="Message" class="form-control" name="taskAct1Message">     
											</td>
											</tr>
									</tbody>
								</table>
								<p><b>Note:</b> To add log items to the message use: [condition states] [date] [time] [username]</p>	
							</div>
							<div class="modal-footer">	
								<button type="button" class="btn btn-default btn-sm" data-bs-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-warning btn-sm" >Create Task</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- new alert -->
            <div class="modal fade in" id="editAlert" tabinsdex="-1" role="dialog" aria-hidden="true" >
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" >Create New Alert</h5>
						</div>
						<form method="post">
							<div class="modal-body">
								<h6>Alert Name</h6><hr>
								<input maxlength="30" required type="text" class="form-control" name="name">
								<br>
                                <div id="alertCompany">
                                    <h6><?php echo $msp; ?></h6><hr>
                                    <select  name="alertCompany" class="form-select">
                                        <option value="0">All <?php echo $msp."s"; ?></option>
                                        <?php
                                            $query = "SELECT ID, name,hex FROM companies WHERE active='1' ORDER BY ID ASC";
                                            $results = mysqli_query($db, $query);
                                            while($result = mysqli_fetch_assoc($results)){ ?>
                                                <option value='<?php echo $result['ID'];?>'><?php echo crypto('decrypt',$result['name'],$result['hex']);?></option>
                                        <?php }?>
                                    
                                    </select>
                                    <br>
                                </div>
								<h6>Condition</h6>
								<table class="table">
									<tbody id="TextBoxesGroup">
										<tr>
											<th scope="row" style="vertical-align:middle;">IF</th>
											<td>
												<input type="hidden" value="newAlert" name="type">
                                                <input type="hidden" value="" id="alertID" name="ID">
												<select required class="form-select" style="width:23%;display:inline-block;" name="alertCondition">
													<option value="Total Alert Count">Total Alert Count</option>
													<option value="Total Ram/Memory">Total Ram/Memory</option>
													<option value="Available Disk Space">Available Disk Space</option>
													<option value="Total Disk Space">Total Disk Space</option>
													<option value="Domain">Domain</option>
													<option value="Public IP Address">Public IP Address</option>
													<option value="Antivirus">Antivirus</option>

													<option value="Agent Version">Agent Version</option>
													<option value="Total User Accounts">Total User Accounts</option>
													<option value="Command Received">Command Received</option>
													<option value="Agent Comes Online">Agent Comes Online</option>
													<option value="Agent Goes Offline">Agent Goes Offline</option>
													<option value="Windows Activation">Windows Activation</option>
													<option value="Local IP Address">Local IP Address</option>
													<option value="Last Update">Last Update</option>
												</select>
												<select class="form-select" required style="width:20%;display:inline-block;" name="alertComparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>
												<input type="text" required placeholder="Value" class="form-control" style="width:47%;display:inline-block;" name="alertValue">     
                                            </td>
										</tr>
									</tbody>
								</table>	
							</div>
							<div class="modal-footer">	
								<button type="button" class="btn btn-default btn-sm" data-bs-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-primary btn-sm" >Create Alert</button>
							</div>
						</form>
					</div>
				</div>
			</div>	
			<!-- one way message -->
			<div id="agentMessageModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">One-way message to asset: <?php echo $_SESSION['ComputerHostname']; ?></h5>
						</div>
						<form method="post" action="/">
							<div class="modal-body">
								<input type="hidden" name="type" value="assetOneWayMessage"/>
								<input type="hidden" name="ID" value="<?php echo $computerID; ?>">
								<div class="form-group">
									<label>Title</label>
									<input type="text" placeholder="What should the title be?" id="#inputTitle" class="form-control" name="alertTitle"/>
								</div>
								<div class="form-group">
									<label>Message</label>
									<textarea id="inputMessage" style="max-height:300px" placeholder="What is your message?" name="alertMessage" class="form-control"></textarea>
								</div>
								<label>Alert Type</label>
								<center>
									<div class="form-check form-check-inline">
										<label class="radio-inline">
											<input type="radio" id="#inputType1" class="form-check-input" name="alertType" value="alert" checked />Alert
										</label>
									</div>
									<div class="form-check form-check-inline">
										<label class="radio-inline">
											<input type="radio" id="#inputType2" class="form-check-input" name="alertType" value="confirm" />Confirm
										</label>
									</div>
									<div class="form-check form-check-inline">
										<label class="radio-inline">
											<input type="radio" id="#inputType3" class="form-check-input" name="alertType" value="password" />Password
										</label>
									</div>
									<div class="form-check form-check-inline">
										<label class="radio-inline">
											<input type="radio" id="#inputType4" class="form-check-input" name="alertType" value="prompt" />Prompt
										</label>
									</div>
								<center>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-default" data-bs-dismiss="modal">Close</button>
								<button type="button" onclick='sendMessage()' data-bs-dismiss="modal" class="btn btn-primary btn-sm">
									Send <i class="fas fa-paper-plane" ></i>
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>					
		<!---------------------------------End MODALS------------------------------------->	
		<?php } ?>