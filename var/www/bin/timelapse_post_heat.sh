#!/bin/sh

PATH=$PATH:/bin

CAMERAINF=/tmp/Camera.json
TMPTLPATH=/var/www/tmp/timelapse
TMPSDFOLD=/sdcard/tmp
TMPSDPATH=$TMPSDFOLD/timelapse

# stop ffmpeg and temporary clean m3u8 file
/etc/init.d/ffmpeg stop
rm -fv /var/www/tmp/*.m3u8

# check camera availability before switch camera mode
retry=0
while [ $retry -lt 4 ]
do
	fuser /dev/video0
	if [ $? != 0 ]
	then
		break
	fi
	sleep_check=0
	while [ $sleep_check -lt 12 ]
	do
		rm -fv /var/www/tmp/*.m3u8
		sleep 0.25
		sleep_check=`expr $sleep_check + 1`
	done
#	sleep 3
	retry=`expr $retry + 1`
done

if [ $retry -ge 4 ]
then
	rm -fv $CAMERAINF
	exit 1
fi

# make timelapse folder
if [ -d $TMPTLPATH ]
then
	echo "timelapse folder existed"
else
	mkdir -pv $TMPSDPATH
	chmod -v 777 $TMPSDPATH
	chmod -v 777 $TMPSDFOLD
	touch $TMPSDPATH
	
	if [ $? != 0 ]
	then
		mkdir -pv $TMPTLPATH
	else
		ln -vs $TMPSDPATH $TMPTLPATH
	fi
	
	if [ $? != 0 ]
	then
		echo "make timelapse folder error"
		exit 2
	fi
fi

# final command to be added by mobile site automatically to re-open camera with timelapse images
