#!/bin/sh

PATH=$PATH:/bin

TIMELAPSE=/var/www/tmp/timelapse.mp4
TEMPVIDEO=/var/www/tmp/tempvideo.mp4
CAMERAINF=/tmp/Camera.json

# stop ffmpeg and clean temporary file
/etc/init.d/ffmpeg stop
rm -fv $TEMPVIDEO $TIMELAPSE
/etc/init.d/ffmpeg clean_tl

# check camera availability before changing camera status
retry=0
while [ $retry -lt 4 ]
do
	fuser /dev/video0
	if [ $? != 0 ]
	then
		break
	fi
	sleep 3
	retry=`expr $retry + 1`
done
rm -fv $CAMERAINF
