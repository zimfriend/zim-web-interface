#!/bin/sh

PATH=$PATH:/bin

CAMERAINF=/tmp/Camera.json
PREFINPAT=/var/www/tmp/timelapse/fin%03d.jpg

# stop ffmpeg and temporary clean m3u8 file
/etc/init.d/ffmpeg stop

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

# clean old hls ts files and m3u8 file after release of resource
rm -fv /var/www/tmp/*.m3u8
rm -fv /var/www/tmp/*.ts

# final command to be added by mobile site automatically to re-open camera with timelapse images
nice -n 19 ffmpeg -v quiet -r 15 -s 640x480 -f video4linux2 -i /dev/video0 -vf "crop=640:360:0:60" -minrate 512k -maxrate 512k -bufsize 2512k -map 0 -force_key_frames "expr:gte(t,n_forced*2)" -c:v libx264 -r 15 -threads 2 -crf 35 -profile:v baseline -b:v 512k -pix_fmt yuv420p -flags -global_header -f hls -hls_time 5 -hls_wrap 20 -hls_list_size 10 /var/www/tmp/zim.m3u8 -f image2 -vf fps=fps=2.5 -qscale:v 2 $PREFINPAT &

