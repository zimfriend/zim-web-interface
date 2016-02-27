TARGETS = hostname.sh mountkernfs.sh zeepro-statsd remote_agent zeepro-button udev keyboard-setup console-screen.sh console-setup mountall.sh mountall-bootclean.sh mountnfs.sh mountnfs-bootclean.sh hwclock.sh mountdevsubfs.sh checkroot.sh urandom procps bootmisc.sh checkroot-bootclean.sh zeepro-network checkfs.sh mtab.sh zeepro-fixmac zeepro-slic3r udev-mtab netbios kmod x11-common
INTERACTIVE = udev keyboard-setup console-screen.sh console-setup checkroot.sh checkfs.sh
udev: mountkernfs.sh
keyboard-setup: mountkernfs.sh udev
console-screen.sh: mountall.sh mountall-bootclean.sh mountnfs.sh mountnfs-bootclean.sh
console-setup: mountall.sh mountall-bootclean.sh mountnfs.sh mountnfs-bootclean.sh console-screen.sh
mountall.sh: checkfs.sh checkroot-bootclean.sh
mountall-bootclean.sh: mountall.sh
mountnfs.sh: mountall.sh mountall-bootclean.sh
mountnfs-bootclean.sh: mountall.sh mountall-bootclean.sh mountnfs.sh
hwclock.sh: mountdevsubfs.sh
mountdevsubfs.sh: mountkernfs.sh udev
checkroot.sh: hwclock.sh keyboard-setup mountdevsubfs.sh hostname.sh
urandom: mountall.sh mountall-bootclean.sh hwclock.sh
procps: mountkernfs.sh mountall.sh mountall-bootclean.sh udev
bootmisc.sh: mountall.sh mountall-bootclean.sh mountnfs.sh mountnfs-bootclean.sh udev checkroot-bootclean.sh
checkroot-bootclean.sh: checkroot.sh
zeepro-network: mountall.sh mountall-bootclean.sh
checkfs.sh: checkroot.sh mtab.sh
mtab.sh: checkroot.sh
zeepro-fixmac: mountall.sh mountall-bootclean.sh
udev-mtab: udev mountall.sh mountall-bootclean.sh
kmod: checkroot.sh
x11-common: mountall.sh mountall-bootclean.sh mountnfs.sh mountnfs-bootclean.sh
