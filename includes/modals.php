<?php if($_SESSION['userid']!=""){ ?>
		<!-------------------------------MODALS------------------------------------>
		<!--------------- Notification Modal ------------->
			<div id="alertModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-md">
					<div class="modal-content" >
						<div class="modal-header">
							<h6 class="modal-title"><b>Notifications</b></h6>
						</div>
						<div class="modal-body">
							<ul>					
							<li>No New Notifications</li>
							</ul>
						</div>
						<div class="modal-footer">
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-warning btn-sm" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!--------------- User Modal ------------->
			<div id="userModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Add/Edit User
								</b>
							</h6>
						</div>
						<form id="userform" method="POST">
							<input type="hidden" name="type" value="AddEditUser"/>
							<input type="hidden" name="ID" id="editUserModal_ID"/>
							<div class="modal-body">
								<p>This will configure a new user and will allow them access to this platform.</p>
								<div class="form-group">
									<input placeholder="Name" type="text" name="name" class="form-control" id="editUserModal_name"/>
								</div>
								<div class="form-group">
									<input placeholder="Email"  type="email" name="email" class="form-control" id="editUserModal_email"/>
								</div>
								<div class="form-group">
									<input placeholder="Username"  required type="text" name="username" class="form-control" id="editUserModal_username"/>
								</div>
								<div class="form-group">
									<input placeholder="Phone" type="text" name="phone" class="form-control" id="editUserModal_phone"/>
								</div>
								<?php if($_SESSION['accountType']=="Admin"){  ?>
									<div class="form-group">
										<label for="editUserModal_type">Access Type</label>
										<select required name="accountType" class="form-control">
											<option id="editUserModal_type" value="">Select Option</option>
											<option value="Standard">Standard</option>
											<option value="Admin">Admin</option>
										</select>
									</div>
								<?php } ?>
								<div class="input-group">
									<input placeholder="Password" style="display:inline" type="password" id="editUserModal_password" name="password" class="form-control"/>
									<span class="input-group-btn">
										<a style="border-radius:0px;padding:6px;pointer:cursor;color:#fff;" class="btn btn-md btn-success" onclick="generate();" >Generate</a>
									</span>
								</div>
								<br>
								<div class="form-group">
									<input placeholder="Confirm Password" type="password" id="editUserModal_password2" name="password2" class="form-control"/>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
								<button type="submit" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning">
									<i class="fas fa-check"></i> Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
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
								<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
								<button type="submit" style="color:#fff" class="btn btn-sm btn-danger">
									<i class="fas fa-trash"></i> Delete
								</button>
							</div>
						</form>
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
						<form id="note" method="POST">
							<div class="modal-body">
								<p>This Will Create A New Note That Only You And Other Administrators Can See.</p>
								<div class="form-group">
									<label for="noteTitle">Title</label>
									<input type="text" class="form-control" placeholder="" name="noteTitle">
								</div>
								<div class="form-group">
									<label for="note">Note</label>
									<textarea rows="6" required maxlength="300" name="note" class="form-control"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Cancel</button>
								<button type="submit"  style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning">
									<i class="fas fa-save"></i> Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!--------------- View note Modal ------------->
			<div id="viewNoteModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
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
							<button type="button" class="btn btn-sm btn-primary" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!---------- Company Modal ------------>
			<div id="companyModal" class="modal fade" role="dialog">
				<div class="modal-dialog">
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
								<p>This Will Add <?php echo $msp; ?> Information. To Better Assist And Organize Content.</p>
								<div class="form-group">
									<input placeholder="Name" type="text" name="name" class="form-control" id="editCompanyModal_name"/>
								</div>
								<div class="form-group">
									<input placeholder="Address" type="text" name="address" class="form-control" id="editCompanyModal_address"/>
								</div>
								<div class="form-group">
									<input placeholder="Phone" type="phone" name="phone" class="form-control" id="editCompanyModal_phone"/>
								</div>
								<div class="form-group">
									<input placeholder="Email" type="email" name="email" class="form-control" id="editCompanyModal_email"/>
								</div>
								<div class="form-group">
									<textarea placeholder="Additional Info" style="resize:vertical" name="comments" class="form-control" placeholder="Optional" id="editCompanyModal_comments"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
								<button type="submit" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning">
									<i class="fas fa-check"></i> Save
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!----------- Terminal ---------------->
			<div id="terminalModal" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6>
								<b>
									Terminal
								</b>
							</h6>
						</div>
						<div class="modal-body" style="background-color:#000;color:#fff;font-family: 'Courier New', Courier, monospacepadding:20px;">
							<div style="max-height:400px;margin-bottom:10px;min-height:100px;overflow:auto;">
								<div id="terminalResponse" style="color:#fff;font-family:font-family:monospace;">
									Microsoft Windows [Version 10.0.<?php echo rand(100000,9999999);?>]<br/>
									(c) <?php echo date("Y");?> Microsoft Corporation. All rights reserved.
									<br/><br/>
								</div>
							</div>
							<div style="min-height:50px;">
								<?php echo strtoupper($data['hostname']);?>> <input type="text" id="terminaltxt" style="outline: none;border:none;background:#000;width:300px;color:#fff;font-family:font-family:monospace;"/>
							</div>
						</div>
					</div>
				</div>
			</div>
				<!------------- Alerts (not sure if used)------------------->
			<div id="confirm" class="modal fade" role="dialog">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h6 id="computerAlertsHostname">
								<b>
									Confirm Action
								</b>
							</h6>
						</div>
						<div class="modal-body">
							<p>Are You Sure You Would Like To Complete This Action></p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm" style="background:<?php echo $siteSettings['theme']['Color 4']; ?>;color:#fff;" data-dismiss="modal">Close</button>
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning"  data-dismiss="modal">Confirm</button>
						</div>
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
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!------------- Historical ------------------->
			<div id="historicalData_modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">
								Historical Data
							</h5>
						</div>
						<div class="modal-body">
							<div id="historicalData" style="overflow:auto;max-height:400px;"></div>
						</div>
						<div class="modal-footer">
							<button type="button" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning"  data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<!------------- Historical Date Selection  ------------------->
			<div id="historicalDateSelection_modal" class="modal fade" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">
								View Historical Data
							</h5>
						</div>
						<div class="modal-body" style="overflow:auto;max-height:400px;">
							<table class="table table-striped">
								<tr>
									<td>Latest</td>
									<td>
										<form method="post">
											<input type="hidden" value="latest" name="historyDate">
											<button type="submit" class="btn btn-sm btn-secondary" >
												Select
											</button>
										</form>
									</td>
								</tr>
								<?php
									$showLast = $siteSettings['Max_History_Days']; //Show last 31 days
									$count = 0;
									while($count <= $showLast){ $count++;
									$niceDate = date("l, F jS", strtotime("-".$count." day"));
									$formatedDate = date("n/j/Y", strtotime("-".$count." day"));
									$formatedDate2 = date("Y-n-j", strtotime("-".$count." day"));
								?>
								<tr>
									<td><?php echo $niceDate; ?></td>
									<td>
										<form method="post">
											<input type="hidden" value="<?php echo $formatedDate2; ?>" name="historyDate"> 
											<button type="submit" class="btn btn-sm btn-warning">Select</button>
										</form>
										</td>
								</tr>
								<?php }?>
							</table>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-sm btn-secondary"  data-dismiss="modal">Close</button>
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
								<div class="input-group-prepend">
									<span class="input-group-text" id="inputGroupFileAddon01">Agent Version</span>
								</div>
								<input style="padding:20px" type="text" name="agentVersion" required minlength=7 class="form-control" placeholder="ex. 1.0.0.4" value="<?php echo $siteSettings['general']['agent_latest_version']; ?>"/>&nbsp;
								<div class="input-group-prepend">
									<span class="input-group-text" id="inputGroupFileAddon01">Upload .exe</span>
								</div>
								<div class="custom-file" >
									<input required="" type="hidden" value="true" name="agentFile">
									<input  required="" accept=".exe" type="file" name="agentUpload" class="custom-file-input" id="agentUpload"/>
									<label style="padding:10px;padding-bottom:30px" class="custom-file-label" for="agentUpload">Choose file</label>
								</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-sm"  data-dismiss="modal">Close</button>
								<button type="submit" style="background:<?php echo $siteSettings['theme']['Color 2']; ?>;border:none" class="btn btn-sm btn-warning" >Upload</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="modal fade in" id="editTrigger" tabinsdex="-1" role="dialog" aria-hidden="true" >
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" >Create New Task</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">×</span>
							</button>
						</div>
						<form method="post">
							<div class="modal-body">
								<label>Task Name</label>
								<input maxlength="30" required type="text" class="form-control" name="name">
								<br><hr>
								<h4>Conditions</h4>
								<table class="table">
									<tbody>
										<tr>
											<th scope="row" style="vertical-align:middle;">IF</th>
											<td>
												<input type="hidden" value="newTask" name="type">
												<select required class="form-control" style="width:23%;display:inline-block;" name="taskCond1">
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

												<select class="form-control" required style="width:20%;display:inline-block;" name="taskCond1Comparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>

												<input type="text" required placeholder="Value" class="form-control" style="width:57%;display:inline-block;" name="taskCond1Value">     
											</td>
										</tr>
										<tr>
											<td colspan=2 style="float:left"><h6>(optional)</h6></td>
										</tr>
										<tr>
											<th scope="row" style="vertical-align:middle;">AND</th>
											<td>
												<select class="form-control" style="width:23%;display:inline-block;"name="taskCond2">	
													<option value="Total Ram/Memory">Total Ram/Memory</option>
													<option value="Total Alert Count">Total Alert Count</option>
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

												<select class="form-control" style="width:20%;display:inline-block;" name="taskCond2Comparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>

												<input type="text" placeholder="Value" class="form-control" style="width:57%;display:inline-block;" name="taskCond2Value">     
											</td>
										</tr>
										<tr>
											<th scope="row" style="vertical-align:middle;">OR</th>
											<td>
												<select class="form-control" style="width:23%;display:inline-block;" name="taskCond3">
													<option value="Available Disk Space">Available Disk Space</option>
													<option value="Total Ram/Memory">Total Ram/Memory</option>
													<option value="Total Alert Count">Total Alert Count</option>
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

												<select class="form-control" style="width:20%;display:inline-block;" name="taskCond3Comparison">
													<option value="=">Equals</option>
													<option value="!=">Not Equal</option>
													<option value=">">Greater than</option>
													<option value="<">Less than</option>
													<option value=">=">Greater than or equals</option>
													<option value="<=">Less than equals</option>
													<option value="contain">Contains</option>
													<option value="notcontain">Does not Contain</option>
												</select>

												<input type="text" placeholder="Value" class="form-control" style="width:57%;display:inline-block;" name="taskCond3Value">     
											</td>
										</tr>
									</tbody>
								</table>
								<hr>
								<h4>Action</h4>
								<table class="table" id="actionsTable">
									<thead>
										<tr>
											<th scope="col">Type</th>
											<th scope="col">Title/Pushover Email</th>
											<th scope="col">Command/Message</th>
										</tr>
									</thead>
									<tbody>		
										<tr>
											<td>
												<select required class="form-control" name="taskAct1">
													<option value="Log">Action Log</option>
													<option value="Command">Send Command</option>
													<option value="Pushover">Pushover</option>
												</select>
											</td>
											<td>
												<input required type="text" class="form-control" name="taskAct1Title">  
											</td>
											<td>
												<input type="text" required placeholder="Message" class="form-control" name="taskAct1Message">     
											</td>
											</tr>
									</tbody>
								</table>
								<p><b>Note:</b> To add log items to the message use: [condition state] [date] [time]</p>	
							</div>
							<div class="modal-footer">	
								<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
								<button type="submit" class="btn btn-warning btn-sm" >Create Task</button>
							</div>
						</form>
					</div>
				</div>
			</div>
									
		<!---------------------------------End MODALS------------------------------------->	
		<?php } ?>