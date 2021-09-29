@echo off
CLS
ECHO.
ECHO =============================
ECHO Running Admin shell
ECHO Installing OpenRMM Service...
ECHO =============================
CONSOLESTATE /Hide
@echo off
SET PROG=%~dp0\
SET SERVICE_EXE=%"%bat\OpenRMM.exe%"%
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
cd /d %~dp0
if '%1'=='ELEV' (del "%vbsGetPrivileges%" 1>nul 2>nul  &  shift /1)

::::::::::::::::::::::::::::
::START
::::::::::::::::::::::::::::

pushd %~dp0
cscript bin\OpenRMM_Update.vbs

SET ThisScriptsDirectory=%~dp0
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
GOTO end
:end
echo Starting Service
net start OpenRMM
ECHO OpenRMM Service Installed And Started.

Set shell = CreateObject("WScript.Shell")
shell.LogEvent 4, "OpenRMM Updated"
if "%errorlevel%"=="0" cls &Echo Success.
if "%errorlevel%"=="1" cls &Echo Fail.
exit /B