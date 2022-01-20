<?php 
$siteSettingsJson = '
{
    "MySQL": {
        "host": "[mysqlHost]:[mysqlPort]",
        "username": "[mysqlUsername]",
        "password": "[mysqlPassword]",
        "database": "[mysqlDatabase]"
    },
    "Encryption": {
        "secret": "[secret]",
        "salt": "[salt]"
    },
    "agentEncryption": {
        "secret": "[agentSecret]"
    },
    "MQTT": {
        "host": "[mqttHost]",
        "username": "[mqttUsername]",
        "password": "[mqttPassword]",
        "port": "[mqttPort]"
    },
    "theme": {
        "Color 1": "#f0f0f0",
        "Color 2": "#d1ecf1",
        "Color 3": "#0ac282",
        "Color 4": "#eb3422",
        "Color 5": "#01a9ac",
        "MSP": "false"
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
    "Service_Desk": "Enabled"
}';