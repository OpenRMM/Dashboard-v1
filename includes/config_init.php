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
    "MQTT": {
        "host": "[mqttHost]",
        "username": "[mqttUsername]",
        "password": "[mqttPassword]",
        "port": "[mqttPort]"
    },
    "theme": {
        "Color 1": "[color1]",
        "Color 2": "[color2]",
        "Color 3": "[color3]",
        "Color 4": "[color4]",
        "Color 5": "[color5]"
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
	"Max_History_Days": 7
}';
 