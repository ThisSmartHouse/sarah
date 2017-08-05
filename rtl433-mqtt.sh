#!/bin/bash
 
# A simple script that will receive events from a RTL433 SDR
 
# Author: Marco Verleun <marco@marcoach.nl>
# Version 2.0: Adapted for the new output format of rtl_433
 
# Remove hash on next line for debugging
#set -x
 
export LANG=C
PATH="/usr/local/bin:/usr/local/sbin:/usr/bin:/usr/sbin:/bin:/sbin"
 
MQTT_HOST="localhost"
 
#
# Start the listener and enter an endless loop
#
/usr/local/bin/rtl_433 -f 344975000 -f 433920000 -R 40 -R 70 -H 0.2 -F json -q |  while read line
do
# Log to file if file exists.
# Create file with touch /tmp/rtl_433.log if logging is needed
 [ -w /tmp/rtl_433.log ] && echo $line >> rtl_433.log
 
# Raw message to MQTT
 echo $line  | /usr/bin/mosquitto_pub -h $MQTT_HOST -i RTL_433 -l -t "/sensor/rtl433"
done

