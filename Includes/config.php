<?php 
$siteSettingsJson = '
{
    "MySQL": {
        "host": "127.0.0.1:3307",
        "username": "root",
        "password": "Nikeswoosh.17",
        "database": "rmm"
    },
    "theme": {
        "Color 1": "#2d364b",
        "Color 2": "#01452c",
        "Color 3": "#a4b0bd",
        "Color 4": "#03925e",
        "Color 5": "#595f69"
    },
    "Alert Settings": {
        "Memory": {
            "Total": {
                "Danger": 3,
                "Warning": 4
            },
            "Free": {
                "Danger": 500,
                "Warning": 1024
            }
        },
        "Disk": {
            "Danger": 85,
            "Warning": 70
        },
		"Processor": {
			"Danger": 85,
            "Warning": 70
		}
    },
	"Max_History_Days": 7,
    "Online Threshold": "1 week",
    "Search Filters": {
        "Alerts": {
            "Nicename": "Alerts",
            "WMI_Name": "Alerts",
            "WMI_Key": "subject",
            "options": [
                "Memory",
                "Disk",
				"Processor",
				"Agent Version",
				"Windows Activation"
            ]
        },
        "Arch": {
            "Nicename": "OS Architecture",
            "WMI_Name": "WMI_OS",
            "WMI_Key": "OSArchitecture",
            "options": [
                "32-bit",
                "64-bit"
            ]
        },
        "WinVer": {
            "Nicename": "Windows Version",
            "WMI_Name": "WMI_OS",
            "WMI_Key": "Caption",
            "options": [
                "Windows 7",
                "Windows 8",
                "Windows 8.1",
                "Windows 10",
                "Windows Server",
                "*"
            ]
        },
        "Processor": {
            "Nicename": "Processor",
            "WMI_Name": "WMI_Processor",
            "WMI_Key": "Name",
            "options": [
                "Atom",
                "Celeron",
                "Pentium",
                "i3",
                "i5",
                "i7",
                "AMD",
                "Intel",
                "*"
            ]
        },
        "LoggedIn": {
            "Nicename": "Logged In User",
            "WMI_Name": "WMI_ComputerSystem",
            "WMI_Key": "UserName",
            "options": [
                "Administrator",
                "*"
            ]
        },
        "PCManufacturer": {
            "Nicename": "PC Manufacturer",
            "WMI_Name": "WMI_ComputerSystem",
            "WMI_Key": "Manufacturer",
            "options": [
                "Dell",
                "HP",
                "Asus",
                "Lenovo",
                "Acer",
                "*"
            ]
        },
        "PCModel": {
            "Nicename": "PC Model",
            "WMI_Name": "WMI_ComputerSystem",
            "WMI_Key": "Model",
            "options": [
                "OptiPlex 9020",
                "*"
            ]
        },
        "UserAcct": {
            "Nicename": "User Account",
            "WMI_Name": "WMI_UserAccount",
            "WMI_Key": "Name",
            "options": [
                "*"
            ]
        },
        "Printer": {
            "Nicename": "Printer",
            "WMI_Name": "WMI_Printers",
            "WMI_Key": "Caption",
            "options": [
                "*"
            ]
        },
        "Program": {
            "Nicename": "Installed Program",
            "WMI_Name": "WMI_Product",
            "WMI_Key": "Caption",
            "options": [
                "*"
            ]
        },
        "Services": {
            "Nicename": "Services",
            "WMI_Name": "WMI_Services",
            "WMI_Key": "Name",
            "options": [
                "Server",
                "*"
            ]
        },
		"Antivirus": {
            "Nicename": "Antivirus Solution",
            "WMI_Name": "Antivirus",
            "WMI_Key": "Value",
            "options": [
				"No Antivirus",
                "Windows Defender",
                "Webroot",
				"McAfee",
				"*"
            ]
        },
		"Firewall": {
            "Nicename": "Firewall Status",
            "WMI_Name": "Firewall",
            "WMI_Key": "Status",
            "options": [
                "Enabled",
                "Disabled"
            ]
        },
		"Activation": {
            "Nicename": "Windows Activation",
            "WMI_Name": "WindowsActivation",
            "WMI_Key": "Value",
            "options": [
                "Activated",
                "Not Activated"
            ]
        },
		"AgentVersion": {
            "Nicename": "Agent Version",
            "WMI_Name": "AgentVersion",
            "WMI_Key": "Value",
            "options": [
                "*"
            ]
        }	
    }
}';
 