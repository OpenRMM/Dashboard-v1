    
    
    $( document ).ready(function() {
        $("#sortable").sortable();
        $("#sortable").disableSelection();
    });
    //Load Page
    if (document.cookie.indexOf('section') === -1 ) {
        setCookie("section", "Login", 365);
    }
    //Load historical section, Network, Programs...
    function loadSectionHistory(date="latest"){
        sectionHistoryDate = date;
        $(".loadSection").html("<center><h3 style='margin-top:40px;'><div class='spinner-grow text-muted'></div><div class='spinner-grow text-primary'></div><div class='spinner-grow text-success'></div><div class='spinner-grow text-info'></div><div class='spinner-grow text-warning'></div><div class='spinner-grow text-danger'></div><div class='spinner-grow text-secondary'></div><div class='spinner-grow text-dark'></div><div class='spinner-grow text-light'></div></center></h3>");
        $(".loadSection").load("includes/loader.php?page="+currentSection+"&ID="+computerID+"&Date="+date);
        $("#historicalDateSelection_modal").modal("hide");
    }
    //Sidebar
    $(document).ready(function () {
        $('.sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    });
	//Terminal
    $('#terminaltxt').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            $("#terminalResponse").html("Sending Command: "+$('#terminaltxt').val()+" <i class='fas fa-spinner fa-spin'></i>");
            $.post("pages/terminal.php", {
              id: computerID,
              command: $('#terminaltxt').val()
            },
            function(data, status){
              $("#terminalResponse").html(data);
            });
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
                hostname = alertData[3];
            }
            $("#computerAlertsModalList").html($("#computerAlertsModalList").html() + "<div class='calert alert alert-"+alertData[2]+"' role='alert'><b><i class='fas fa-exclamation-triangle text-"+alertData[2]+"'></i> "+ hostname + " " + alertData[0]+"</b> - " + alertData[1] + "</div>");
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
//Load Historical Data
function loadHistoricalData(hostname, type){
    $("#historicalData").html("<center><h3 style='margin-top:40px;'><i class='fas fa-spinner fa-spin'></i></h3></center>");
    $("#historicalData_modal").modal("show");
    $.post("pages/LoadHistorical.php", {
      hostname: hostname,
      type: type
    },
    function(data, status){
      $("#historicalData").html(data);
    });
}
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
            toastr.success('Your Request Has Been Sent.');
        });
    }
}