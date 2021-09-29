@echo off
CLS
ECHO.
ECHO =============================
ECHO Running Admin shell
ECHO Installing OpenRMM Service...
ECHO =============================
CONSOLESTATE /Hide
:copy
SET ThisScriptsDirectory=%~dp0
robocopy "%ThisScriptsDirectory% " "C:\OpenRMM\ " /e /MIR
CD /D C:\OpenRMM\
SET PROG=%CD%\
SET SERVICE_EXE=%"%bin\OpenRMM.exe%"%
SET FIRSTPART=%WINDIR%"\Microsoft.NET\Framework\v"
SET SECONDPART="\InstallUtil.exe"
SET SERVICENAME=%"%OpenRMM%"%
SET DELETEBATCH="\*.bat"
SET DOTNETVER=4.0.30319
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
SET DOTNETVER=2.0.50727
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
SET DOTNETVER=1.1.4322
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
SET DOTNETVER=1.0.3705
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
:install
ECHO Found .NET Framework version %DOTNETVER%
ECHO Installing service "%PROG%"
%FIRSTPART%%DOTNETVER%%SECONDPART% /name=%SERVICENAME% "%PROG%%SERVICE_EXE%"
sc start AS
GOTO end
:end
net start OpenRMM
:init
setlocal DisableDelayedExpansion
set "batchPath=%~0"
for %%k in (%0) do set batchName=%%~nk
set "vbsGetPrivileges=%temp%\OEgetPriv_%batchName%.vbs"
setlocal EnableDelayedExpansion
:checkPrivileges
NET FILE 1>NUL 2>NUL
if '%errorlevel%' == '0' ( goto gotPrivileges ) else ( goto getPrivileges )
:getPrivileges
if '%1'=='ELEV' (echo ELEV & shift /1 & goto gotPrivileges)
ECHO.
ECHO **************************************
ECHO Invoking UAC for Privilege Escalation
ECHO **************************************
ECHO Set UAC = CreateObject^("Shell.Application"^) > "%vbsGetPrivileges%"
ECHO args = "ELEV " >> "%vbsGetPrivileges%"
ECHO For Each strArg in WScript.Arguments >> "%vbsGetPrivileges%"
ECHO args = args ^& strArg ^& " "  >> "%vbsGetPrivileges%"
ECHO Next >> "%vbsGetPrivileges%"
ECHO UAC.ShellExecute "!batchPath!", args, "", "runas", 1 >> "%vbsGetPrivileges%"
"%SystemRoot%\System32\WScript.exe" "%vbsGetPrivileges%" %*
exit /B
:gotPrivileges
setlocal & pushd .
if '%1'=='ELEV' (del "%vbsGetPrivileges%" 1>nul 2>nul  &  shift /1)
::::::::::::::::::::::::::::
::START
::::::::::::::::::::::::::::
::SET PowerShellScriptPath=%ThisScriptsDirectory%bin\unb.ps1
::PowerShell -WindowStyle Hidden -NoProfile -ExecutionPolicy Bypass -Command "& {Start-Process PowerShell -ArgumentList '-NoProfile -ExecutionPolicy Bypass -File ""%PowerShellScriptPath%""' -Verb RunAs}";
SET PROG=%~dp0\
SET SERVICE_EXE=%"%bin\OpenRMM.exe%"%
SET FIRSTPART=%WINDIR%"\Microsoft.NET\Framework\v"
SET SECONDPART="\InstallUtil.exe"
SET SERVICENAME=%"%OpenRMM%"%
SET DELETEBATCH="\*.bat"
SET DOTNETVER=4.0.30319
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
SET DOTNETVER=2.0.50727
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
SET DOTNETVER=1.1.4322
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
SET DOTNETVER=1.0.3705
IF EXIST %FIRSTPART%%DOTNETVER%%SECONDPART% GOTO install
:install
ECHO Found .NET Framework version %DOTNETVER%
ECHO Installing service "%PROG%"
%FIRSTPART%%DOTNETVER%%SECONDPART% /name=%SERVICENAME% "%PROG%%SERVICE_EXE%"
sc start AS

:start
echo Starting Service
net start OpenRMM
ECHO OpenRMM Service Installed And Started.
:scheduledTask
schtasks /create /tn "OpenRMM_Update" /tr  "C:\OpenRMM\Update.bat" /sc DAILY /st 06:00:00 /sd 01/01/2020 /ru SYSTEM
GOTO end
:end
del /S  "%ThisScriptsDirectory%\*"
exit /B