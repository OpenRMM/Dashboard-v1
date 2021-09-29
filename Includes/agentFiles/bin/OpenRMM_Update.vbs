dim http_obj
dim stream_obj
dim shell_obj
 
set http_obj = CreateObject("Microsoft.XMLHTTP")
set stream_obj = CreateObject("ADODB.Stream")
set shell_obj = CreateObject("WScript.Shell")


'Kill Proccess
dim wmi, list, process, path, shell
path = "empty"
set wmi = GetObject("winmgmts:{impersonationLevel=impersonate}!\\.\root\cimv2") 
set list = wmi.ExecQuery("Select * from Win32_Process") 

for each process in list
	if (lcase(process.Name) = "openrmm.exe") then
        path = process.ExecutablePath
        process.terminate()
		exit for
	end if
next

'Download
URL = "http://rmm.smgunlimited.com/rmm/Includes/update/OpenRMM.exe"
http_obj.open "GET", URL, False
http_obj.send
 
stream_obj.type = 1
stream_obj.open
stream_obj.write http_obj.responseBody
stream_obj.savetofile path, 2 'overwrite