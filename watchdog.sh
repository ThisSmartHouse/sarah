#!/bin/bash

TEST="wget -O /dev/null -T 5 -t 3 http://127.0.0.1:8123"

if ! $TEST &>/dev/null; then
	echo "`date -u` : HASS Not responding -- Restarting" >> /home/homeassistant/.homeassistant/watchdog.log 
	systemctl restart home-assistant@homeassistant.service
fi

