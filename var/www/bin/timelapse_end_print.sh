#!/bin/sh

PATH=$PATH:/bin

IMAGESPAT=img%04d.jpg
IMAGEPATH=/var/www/tmp/timelapse/
TIMELAPSE=/var/www/tmp/timelapse.mp4
TEMPVIDEO=/var/www/tmp/tempvideo.mp4
PREFINPAT=/var/www/tmp/timelapse/fin%03d.jpg
WATERMARK=/var/www/images/timelapse_watermark.png
PHOTOPATH=/var/www/tmp/image.jpg
POWEREDVD=/var/www/images/powered.mp4
CAMERAINF=/tmp/Camera.json
FRAMEPERS=10 # 10fps
VIDEOTIME=30
NEEDPHOTO=`expr $FRAMEPERS \* $VIDEOTIME` # 30s * 10fps

# exit function with file clean
exit_withClean() {
	rm -fv $TEMPVIDEO $TIMELAPSE
	/etc/init.d/ffmpeg clean_tl
	
	touch $TIMELAPSE
	chown www-data $TIMELAPSE
	chmod 777 $TIMELAPSE
	exit
}

# stop ffmpeg (do not release camera json for photo later)
/etc/init.d/ffmpeg stop

# touch timelapse file
touch $TIMELAPSE
chown www-data $TIMELAPSE
chmod 777 $TIMELAPSE

# prepare images
tlvdfps=$FRAMEPERS
nbphoto=`find $IMAGEPATH -maxdepth 1 -name img*.jpg | wc -l`
if [ $nbphoto -gt $NEEDPHOTO ]
then
	rang=2
	tmp_nb=$rang
	while [ $tmp_nb -le $nbphoto ]
	do
		tmp_file=`printf $IMAGEPATH$IMAGESPAT $tmp_nb`
		if [ ! -f $tmp_file ]
		then
			echo "prepare image error, file not found: $tmp_file"
			exit_withClean
		fi
		
		# start to compare progress percentage
		# it equals [ $tmp_nb / $nbphoto < $rang / $NEEDPHOTO ]
		if [ `expr $tmp_nb \* $NEEDPHOTO` -lt `expr $rang \* $nbphoto` ]
		then
			rm -fv $tmp_file # remove useless file
		else
			target_file=`printf $IMAGEPATH$IMAGESPAT $rang`
			mv -v $tmp_file $target_file # rename file
			
			rang=`expr $rang + 1`
		fi
		
		tmp_nb=`expr $tmp_nb + 1`
	done
else
	tlvdfps=`expr $nbphoto / $VIDEOTIME`
	echo "fps changed : $tlvdfps"
fi

# get estimated duration and fade out point
fadeptr=`find $IMAGEPATH -maxdepth 1 -name img*.jpg | wc -l`
prefinp=`find $IMAGEPATH -maxdepth 1 -name fin*.jpg | wc -l`
fadeptr=`expr $fadeptr + $prefinp`
echo -n "fadeptr = "
echo $fadeptr

# clean temporary file and exit if we have no image files
if [ $fadeptr -eq 0 ]
then
	exit_withClean
fi

# take photo as last frame
retry=0
while [ $retry -lt 4 ]
do
	fuser /dev/video0
	if [ $? != 0 ]
	then
		# take photo if device is available
		ffmpeg -f video4linux2 -i /dev/video0 -y -vframes 1 -pix_fmt yuv420p $PHOTOPATH
		break
	fi
	sleep 3
	retry=`expr $retry + 1`
done

# assign proper last image file
last_image=$PHOTOPATH # with photo
if [ $retry -ge 4 ]
then
	# without photo
	last_image=`find $IMAGEPATH -maxdepth 1 -name img*.jpg | sort | tail -n 1`
fi

# timelapse generation
if [ -e $TIMELAPSE ]
then
	# default fps is 10
	ffmpeg -r $tlvdfps -loop 1 -i $last_image -t 1 -y -vcodec libx264 -crf 23 -pix_fmt yuv420p $TEMPVIDEO
	nice -n 19 ffmpeg -r $tlvdfps -f image2 -i $IMAGEPATH$IMAGESPAT -i $TEMPVIDEO -i $WATERMARK -i $POWEREDVD -f image2 -i $PREFINPAT -y -filter_complex "[0:v][4:v][1:v]concat=n=3:v=1[bg];[bg][2:v]overlay=main_w-overlay_w:0[pt];[pt]fade=t=out:$fadeptr:$tlvdfps[tl];[tl][3:v]concat=n=2:v=1" -vcodec libx264 -crf 29 -pix_fmt yuv420p $TIMELAPSE
	rm -fv $TEMPVIDEO
	
	chown www-data $TIMELAPSE
	chmod 777 $TIMELAPSE
fi

# clean temporary file and release camera
/etc/init.d/ffmpeg clean_tl
rm -fv $CAMERAINF
rm -fv /var/www/tmp/*.m3u8
rm -fv /var/www/tmp/*.ts
