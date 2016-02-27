# zim-web-interface
Dump of zeepro zim 3D printer linux internal program

This repository contains the sources of the linux internal card 
of the Zeepro ZIM 3DPrinter, a user oriented 3D printer using 
a remote web responsive interface.

The purpose is to continue to maintain it at a collaborative level, 
either on improving it a bit, or replacing it by an astroprint fork.
The sources where easy to get after trying password related 
to the visit of brave kickstarter bakers to the Factory in Shenzhen, 
in the district of Longgang, 2014.

A first imrovement is to solve the dead end process of 
resetting the Zim or reconfiguring the wifi : 

 - remove the need to "activate" the Zim on the zeeproshare 

` 
    rm /sdcard/conf/NeedActive.tmp 
` 
 

 - manually edit the wifi connection SSID and password 

` 
    vi /sdcard/conf/Connection.json 
` 