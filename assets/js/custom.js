    //Sidebar
$(document).ready(function () {
    $('.sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
});

//Terminal
var old = "";
var items2 = [];
var n = 0;
var i = 0;
$('#terminaltxt').keydown(function(e){
   
    var keycode = (e.keyCode ? e.keyCode : e.which);   	
    if(keycode == '13'){
        var command = $('#terminaltxt').val();
        $('#cmdtxt').hide();
        
        $("#terminalResponse").html(old + "C:\\Windows\\System32> Sending Command: "+$('#terminaltxt').val()+" <i class='fas fa-spinner fa-spin'></i>");
        $.post("includes/terminal.php", {
            id: computerID,
            command: $('#terminaltxt').val()
        },
        function(data, status){
            old += "<br>C:\\Windows\\System32> " + command + "<br><br>" + data;
            $("#terminalResponse").html(old);
            $('#terminaltxt').val("");
            $('#terminaltxt').focus(); 
            n = items2.length + 1;  
            items2.push(command);
            //items2.reverse();
            $(".modal-body").animate({ scrollTop: $(".modal-body")[0].scrollHeight}, 1000);
        });   
    }
    if(e.keyCode==38||e.keyCode==40){
        i = (e.keyCode==38? ++i : --i) <0? n-1 : i%n;
        //if      (e.keyCode==38) items2.push(items2.shift());
        //else if (e.keyCode==40) items2.unshift(items2.pop());
        $('#terminaltxt').val(items2[i]);
    }
});


//Alerts Modal
function computerAlertsModal(title, delimited='none', showHostname = false){
    $("#computerAlertsHostname").html("<b>Alerts for "+title+"</b>");
    if(delimited=="none"){
        $("#computerAlertsModalList").html("<div class='alert alert-success' style='font-size:12px' role='alert'><b><i class='fas fa-thumbs-up'></i> No Issues</b></div>");
        return;
    }
    $("#computerAlertsModalList").html("")
    var alerts = delimited.split(",");
    var hostname = "";
    for(alert in alerts){
        var alertData = alerts[alert].split("|");
        if(alertData[0].trim()==""){
            continue;
        }
        if(showHostname == true){
            hostname = alertData[3] + " - ";
        }
        $("#computerAlertsModalList").html($("#computerAlertsModalList").html() + "<div class='calert alert alert-"+alertData[2]+"' role='alert'><b><i class='fas fa-exclamation-triangle text-"+alertData[2]+"'></i> "+ hostname + " " + alertData[0]+"</b> " + alertData[1] + "</div>");
    }
}

//Random password
function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

//Set random passwords to inputs
function generate() {
    var pass = randomPassword(8);
    $('#editUserModal_password').prop('type', 'text').val(pass);
    $('#editUserModal_password2').prop('type', 'text').val(pass);
}

//Page Alerts, replaces alert()
function pageAlert(title, message, type="default"){
    if(title.trim() == ""){
        title = "Message From Webpage";
    }
    if(message.trim() != "") {
        type = type.toLowerCase();
        toastr.options.progressBar = true;
        toastr[type](message,title);
    }
}

//Send commands
function sendCommand(command, prompt, expire_after=5){
    if(confirm("Are you sure you would like to "+prompt+"?")){
        $.post("index.php", {
        type: "SendCommand",
        ID: computerID,
        command: command,
        expire_after: expire_after
        },
        function(data, status){
            toastr.options.progressBar = true;
            toastr.info('Your Command Has Been Sent.');
        });
    }
}
//remove commands
function removeCommand(ID,command){
    if(confirm("Are you sure you would like to remove this command?")){
        $.post("index.php", {
        type: "removeCommand",
        command: command
        },
        function(data, status){
            toastr.options.progressBar = true;
            $('#btn' + ID).hide();
            toastr.info('Your Command Has Been Removed.');
        });
    }
}
function deleteNote(delNote){  
    $.post("index.php", {
    delNote: delNote
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('Your Notes Have Been Deleted.');
        $(".noteList").hide();
        $(".no_noteList").show();
    });
}
function resetAssetPassword(){ 
    var ID = $("#AssetID").val();
    var pass = $("#AssetPassword").val();
    var user = $("#AssetUser").val();
    var command = "net user " + user + " " + pass;
    $.post("index.php", {
    command: command,
    ID: ID,
    type: 'SendCommand',
    expire_after: 5
    },
    function(data, status){
        toastr.options.progressBar = true;
        $("#AssetPassword").val("");
        toastr.info('The asset password has been updated. Changes may take some time to reflect.');
    });
}
function deleteCompany(ID,status2){  
    $.post("index.php", {
        ID: ID,
        type: "DeleteCompany",
        companyactive: status2
    },
    function(data, status){
        if(status2=="0"){
            toastr.options.progressBar = true;
            toastr.info('The selected has been deactivated.');
            $("#actCompany" + ID).show();
            $("#delCompany" + ID).hide();
        }
        if(status2=="1"){
            toastr.options.progressBar = true;
            toastr.info('The selected has been activated.');
            $("#actCompany" + ID).hide();
            $("#delCompany" + ID).show(); 
        }

    });
}
function deleteUser(ID,status2){  
    $.post("index.php", {
        ID: ID,
        type: "DeleteUser",
        useractive: status2
    },
    function(data, status){
        if(status2=="0"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been deactivated.');
            $("#actUser" + ID).show();
            $("#delUser" + ID).hide();
        }
        if(status2=="1"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been activated.');
            $("#actUser" + ID).hide();
            $("#delUser" + ID).show();    
        }

    });
}
function deleteAlert(ID){  
    $.post("index.php", {
        ID: ID,
        type: "delAlert"
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The selected alert has been deleted.');
        $("#alert" + ID).fadeOut();
    });
}
function deleteTask(ID){  
    $.post("index.php", {
        ID: ID,
        type: "delTask"
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The selected task has been deleted.');
        $("#task" + ID).fadeOut();
    });
}
function deleteUserProfile(ID,useractive){  
    $.post("index.php", {
        ID: ID,
        type: "DeleteUser",
        useractive: useractive
    },
    function(data, status){
        if(useractive=="0"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been disabled.');
            $("#userDel" + ID).hide();
            $("#userAct" + ID).show();  
        }
        if(useractive=="1"){
            toastr.options.progressBar = true;
            toastr.info('The selected user has been enabled.');
            $("#userAct" + ID).hide();
            $("#userDel" + ID).show();  
        }
    });
}
function newNote(){  
    var note = $("#note").val();
    var noteTitle = $("#noteTitle").val(); 
    $.post("index.php", {
        note: note,
        noteTitle: noteTitle
    },
    function(data, status){
        $(".no_noteList").hide();
        $("#note").val('');
        $("#noteTitle").val(''); 
        var newTextBoxDiv = $(document.createElement('div')).attr("id", 'TextBoxDiv' + counter);
		newTextBoxDiv.after().html('<a title="View Note" class="noteList" onclick="$(\'#notetitle\').text(\''+ noteTitle + '\');$(\'#notedesc\').text(\'' + note + '\');" data-toggle="modal" data-target="#viewNoteModal"><li style="font-size:14px;cursor:pointer;color:#333;background:#fff;" class="secbtn list-group-item"><i style="float:left;font-size:26px;padding-right:7px;color:#999" class="far fa-sticky-note"></i>' + noteTitle + '</li> </a>');
		newTextBoxDiv.prependTo("#TextBoxesGroup");  
        
    });
}
function deleteActivity(){
    var ID = $("#delActivity").val();  
    $.post("index.php", {
        delActivity: ID
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The Activity has been cleared for this user');
        $("#activity").slideUp("slow");
    });
}



function updateAgent(ID2){ 
    $.post("index.php", {
       type: "updateAgent",
       ID: ID2
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The update request has been sent. Please allow up to 5 minutes for the update to complete.');
    });
}

function updateCompanyAgent(ID2){ 
    $.post("index.php", {
       type: "CompanyUpdateAll",
       ID: ID2
    },
    function(data, status){
        toastr.options.progressBar = true;
        toastr.info('The update request has been sent to each agent. Please allow up to 15 minutes for the update to complete.');
    });
}

function disableTFA(){ 
    $.post("index.php", {
       type: "DisableTFA",
       ID: $("#editUserModal_ID").val()
    },
    function(data, status){
        $("#disableTFA2").hide();
        $("#enableTFA").show();
        toastr.options.progressBar = true;
        toastr.error('Two Factor Authentication has been disabled');
    });
}

function sendChat(){ 
    var ID2 = $("#asset_message_id").val();
    var message2 = $("#asset_message").val();
    var user_id2 = $("#user_id").val();
    if(ID2!='0' && message2!=''){
        $.post("index.php", {
        type: "asset_message",
        ID: ID2,
        user_id: user_id2,
        message: message2
        },
        function(data, status){
            $(".chatList").append(' <li title="Reload chat to see more info" class="clearfix"><div style="text-align:center;min-width:100px;font-size:14px;padding:5px" class="message other-message float-right bg-primary text-white">'+message2+'</div></li>');
            $("#asset_message").val('');
        });
    }
}

function agentStatus(ID,action){ 
    var type2;
    var message;
    $.post("index.php", {
       type: "agentStatus",
       ID: ID,
       action: action
    },
    function(data, status){
        if(action=="stop"){
            type2="error";
            message="";
        }else{
            type2="warning";
            message="Please allow up to 5 minutes for the agent to come back online."
        }
        toastr.options.progressBar = true;
        toastr[type2]('The ' + action + ' request has been sent. ' + message);
    });
}

function deleteAssets(){ 
    var array = []
    var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')

    for (var i = 0; i < checkboxes.length; i++) {
    array.push(checkboxes[i].value)
    }
    $.post("index.php", {
        computers: array,
        type: "deleteAssets"
    },
    function(data, status){
        array.forEach(function (item) {
            $("#row"+item).hide();
        });
        toastr.options.progressBar = true;
        toastr.error('The Selected Assets Have Been Deleted');
        
    });
}
function deleteServer(ID,action){   
    $.post("index.php", {
        ID: ID,
        action: action,
        type: "deleteServer"
    },
    function(data, status){            
        toastr.options.progressBar = true;
        if(action=="0"){
            $("#delServer"+ID).hide();
            $("#actServer"+ID).show();
            toastr.error('The Selected Server Has Been Disabled');
        }else{
            $("#actServer"+ID).hide();
            $("#delServer"+ID).show();
            toastr.success('The Selected Server Has Been Enabled');
        }       
    });
}
function serverStatus(ID,action){  
    if (confirm('Are you sure you want to ' + action +' this server?')) { 
        $.post("index.php", {
            ID: ID,
            action: action,
            type: "serverStatus"
        },
        function(data, status){            
            toastr.options.progressBar = true;
            if(action=="restart"){
                toastr.warning('The Selected Server Has Been Sent Restart Request');
            }else if(action=="shutdown"){
                toastr.error('The Selected Server Has Been Sent Shutdown Request');
            }else if(action=="stop service"){
                toastr.error('The Selected Server Service Has Been Sent Stop Request');
            }else if(action=="restart service"){
                toastr.warning('The Selected Server Service Has Been Sent Restart Request');
            }else if(action=="update service"){
                toastr.info('The Selected Server Service Has Been Sent Update Request');
            }else{

            }     
        });
    }
}
function assignAssets(){ 
    var array = []
    var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')
    var companyID = $('input[name="companies"]:checked').attr('company')
    var company = $('input[name="companies"]:checked').val();
    for (var i = 0; i < checkboxes.length; i++) {
    array.push(checkboxes[i].value)
    }
    $.post("index.php", {
        computers: array,
        companies: company,
        companyID: companyID,
        type: "CompanyComputers"
    },
    function(data, status){
        array.forEach(function (item) {
            $("#col"+item).text(company);
        });
        toastr.options.progressBar = true;
        toastr.info('The Selected Assets Have Been Assigned To ' + company);
        
    });
}

function updateTicket(type,data2,ticket,id=0){  
	if(type=="category"){
		$('#category').html(data2);
	}
	if(type=="priority"){
		$('#priority').html(data2);
	}
	if(type=="status"){
		$('#status').html(data2);
        $('#status' + ticket).html(data2);
	}
	if(type=="assignee"){
		$('#assignee').html(data2);
		data2 = id;
	}
	
	$.post("/", {
	type: "updateTicket",
	ID: ticket,
	tkttype: type,
	tktdata: data2
	},
	function(data, status){
		toastr.options.progressBar = true;
		toastr.success("Your Changes Have Been Saved");
	});  
}
function olderData(ID, name, key){
    $("#olderData_content").load("includes/olderData.php?ID="+btoa(ID)+"&name="+btoa(name)+"&key="+btoa(key));
    $(".olderdata").css({"z-index": "2"});
    key = key.replace(".", "");

    if(key=="null"){
        $("#olderDataModalDialog").removeClass("modal-md");
        $("#olderDataModalDialog").addClass("modal-lg");
    }else{
        $("#olderDataModalDialog").addClass("modal-md");
        $("#olderDataModalDialog").removeClass("modal-lg");
         
    }
    if(name=="Firewall"){
        $("#" + name).css({"z-index": "99999"});
    }else{
        $("#" + name + "_" + key).css({"z-index": "99999"});
    }
}
