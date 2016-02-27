#!/bin/sh

RETVAL=0
TIMEOUT_HEAT_UNLOAD=600
UNLOAD_SPEED_DEFAULT=150
UNLOAD_SPEED_PVA=50
STATUS_FILE_UNLOAD_HEAT=/tmp/printer_unload_heat

PATH=$PATH:/bin

force_reco() {
	zfw_setenv force_reco 1
	
	return $?
}

start_tomboning() {
	/etc/init.d/zeepro-agent start
	
	retrun $?
}

stop_tomboning() {
	/etc/init.d/zeepro-agent stop
	
	return $?
}

status_tomboning() {
	zeepro-agent-conf
	
	return $?
}

restart_arcontrol() {
	/etc/init.d/arcontrol stop
	/etc/init.d/arcontrol start
	arcontrol_cli M1400
	
	return $?
}

start_slic3r() {
	/etc/init.d/zeepro-slic3r start
	
	return 0
}

unload_filament() {
	case "$1" in
		l)
			gcode_temper="M1301";
			gcode_unload="M1607";
			gcode_extruder="T1";
			gcode_charge="M1651";
			;;
			
		r)
			gcode_temper="M1300";
			gcode_unload="M1606";
			gcode_extruder="T0";
			gcode_charge="M1650";
			;;
			
		*)
			echo "unknown extruder";
			exit 2
	esac
	
	case "$2" in
		pva)
			gcode_unload="$gcode_unload P";
			gcode_charge="$gcode_charge P";
			prime_speed=50
			unload_offset=48
			;;
			
		default)
			prime_speed=150
			unload_offset=16
			;;
		*)
			echo "unknown material";
			exit 4
			;;
	esac
	
	# time management
	timeout_check=`date +%s`;
	timeout_check=`expr $timeout_check + $TIMEOUT_HEAT_UNLOAD`;
	
	# temporary file management
	echo `date +%s` > $STATUS_FILE_UNLOAD_HEAT
#	chown www-data $STATUS_FILE_UNLOAD_HEAT
#	chgrp www-data $STATUS_FILE_UNLOAD_HEAT
	
	arcontrol_cli "M104 S$3 $gcode_extruder"
	arcontrol_cli M1905;
	temper_current=`arcontrol_cli -q $gcode_temper`;
	temper_current=`awk 'BEGIN {printf "%d\n", '$temper_current' }'`;
	while [ $temper_current -lt $3 ]
	do
		if [ ! -e $STATUS_FILE_UNLOAD_HEAT ]
		then
			echo "Unloading cancelled";
			arcontrol_cli "M104 S0 $gcode_extruder";
			exit 0
		fi
		
		sleep 3;
		
		# check timeout here
		time_current=`date +%s`;
		if [ $time_current -gt $timeout_check ]
		then
			echo "Reach timeout of heating";
			arcontrol_cli "M104 S0 $gcode_extruder";
			exit 3;
		fi
		
		temper_current=`arcontrol_cli -q $gcode_temper`;
		temper_current=`awk 'BEGIN {printf "%d\n", '$temper_current' }'`;
	done
	rm $STATUS_FILE_UNLOAD_HEAT
	
	arcontrol_cli G90 M83 $gcode_extruder "$gcode_charge" "G1 E40 F$prime_speed";
	sleep $unload_offset; # wait charging and extruding
	arcontrol_cli "$gcode_unload";
	arcontrol_cli "M104 S0 $gcode_extruder";
}

clean_sliced() {
	rm -f /tmp/_sliced_info.json /tmp/_sliced_model.gcode*
}


# main program

case "$1" in
	force_reco)
		force_reco
		;;
		
	start_tomboning)
		start_tomboning
		;;
		
	stop_tomboning)
		stop_tomboning
		;;
		
	status_tomboning)
		status_tomboning
		;;
		
	restart_arcontrol)
		restart_arcontrol
		;;
		
	start_slic3r)
		start_slic3r
		;;
		
	unload)
		unload_filament $2 default $3
		;;
		
	unload_pva)
		unload_filament $2 pva $3
		;;
		
	remote_slice)
		/etc/init.d/remote_slice slice "$2" "$3"
		;;
		
	remote_slice_stop)
		/etc/init.d/remote_slice stop
		;;
		
	stats)
		/etc/init.d/zeepro-statsd send $2
		;;
		
	reboot)
		/sbin/reboot -i
		;;
		
	upgrade_url)
		mount /fab -o remount,rw,noatime
		echo "$2" > /fab/profile.txt
		rc_url=$?
		mount /fab -o remount,ro,noatime
		exit $rc_url
		;;
		
	clean_slicerfiles)
		rm -f /tmp/_slicer_preview.amf /tmp/_slicer_preview.stl
		clean_sliced
		;;
		
	clean_sliced)
		clean_sliced
		;;
		
	*)
		echo "Usage: $0 {force_reco|start_tomboning|stop_romboning|status_tomboning|restart_arcontrol|start_slic3r|unload|unload_pva|stats|remote_slice|remote_slice_stop|*}"
		exit 1
esac

exit $?
