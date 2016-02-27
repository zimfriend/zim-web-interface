#!/usr/bin/perl -w
use strict;
use warnings;

use Getopt::Long qw(:config no_auto_abbrev);
use Time::HiRes qw(usleep);
use JSON;
use Cwd qw(abs_path);
use File::Basename;

# our constant variable here
use constant TRUE  => 1;
use constant FALSE => 0;

use constant FILENAME_PRINT   => 'print.dat';
use constant FILENAME_CONFIG  => 'config.json';
use constant FILENAME_TEMPERX => 'temper.dat';
use constant FILENAME_TEMPER1 => '1temper.dat';
use constant FILENAME_TEMPER2 => '2temper.dat';

#use constant FILENAME_T_ETAT1 => '1temper_e.tmp';
#use constant FILENAME_T_ETAT2 => '2temper_e.tmp';
use constant FILENAME_T_ETATX => 'temper_e.tmp';

#use constant FILENAME_T_STOP1 => '1temper_s.tmp';
#use constant FILENAME_T_STOP2 => '2temper_s.tmp';
use constant FILENAME_T_STOPX => 'temper_s.tmp';

use constant FILENAME_CARTRIDGE_L_LOADED => 'left_loaded.json';
use constant FILENAME_CARTRIDGE_R_LOADED => 'right_loaded.json';
use constant FILENAME_CARTRIDGE_L_UNLOAD => 'left.json';
use constant FILENAME_CARTRIDGE_R_UNLOAD => 'right.json';
use constant FILENAME_CARTRIDGE_ETAT     => 'cartridge_e.tmp';

use constant FILENAME_PRINT_STOP   => 'print_s.tmp';
use constant FILENAME_PRINT_PAUSE  => 'print_p.tmp';
use constant FILENAME_PRINT_RESUME => 'print_r.tmp';

use constant JSON_NB_EXTRUD  => 'Extruders';
use constant JSON_CUR_EXTRUD => 'Current extruder';

#use constant JSON_LEFT_LABEL  => 'Left label';
#use constant JSON_RIGHT_LABEL => 'Right label';
use constant JSON_TEMPER_EXT1 => 'Temperature Extruder1';
use constant JSON_TEMPER_EXT2 => 'Temperature Extruder2';
use constant JSON_TEMPER_EXTX => 'Temperature Extruder';
use constant JSON_LABEL       => 'Label';
use constant JSON_LED_STRIP   => 'Strip Led';
use constant JSON_LED_HEAD    => 'Head Led';

use constant STATUS_ON          => 1;
use constant STATUS_OFF         => 0;
use constant MAX_TIME_CMD_PRINT => 60 * 1000 * 1000;
use constant TEMPER_CHANGE_SPD  => 5;
use constant TIME_PRECISION     => 500 * 1000;
use constant CURRENT_EXTRUD1    => 0;
use constant CURRENT_EXTRUD2    => 1;
use constant DEFAULT_TEMPER     => 20;
use constant DEFAULT_LEFT_LAB   => '5C0F0100FF80FFF0F000F0F00001F0D3';
use constant DEFAULT_RIGHT_LAB  => '5C0F0001FFFFFF0F0F00000F00020FA0';
use constant MAX_TIME_CMD_FILA  => 30 * 1000 * 1000;
use constant TIME_CMD_PREPRINT  => 36 * 1000 * 1000;

use constant CMD_CHECK => 'M1600';

#use constant CMD_GET_TEMPER    => 'M1300';
use constant CMD_GET_TEMPER1   => 'M1300';
use constant CMD_GET_TEMPER2   => 'M1301';
use constant CMD_SET_TEMPER    => 'M104';
use constant CMD_SET_EXTRUD1   => 'T0';
use constant CMD_SET_EXTRUD2   => 'T1';
use constant CMD_GET_EXTRUD    => 'M1601';
use constant CMD_GET_RIGHT_LAB => 'M1602';
use constant CMD_GET_LEFT_LAB  => 'M1603';
use constant CMD_SET_RIGHT_LAB => 'M1610';
use constant CMD_SET_LEFT_LAB  => 'M1611';

use constant CMD_LOAD_RIGHT_FILA     => 'M1604';
use constant CMD_LOAD_LEFT_FILA      => 'M1605';
use constant CMD_UNIN_RIGHT_FILA     => 'M1606';
use constant CMD_UNIN_LEFT_FILA      => 'M1607';
use constant CMD_GET_ETAT_RIGHT_FILA => 'M1608';
use constant CMD_GET_ETAT_LEFT_FILA  => 'M1609';
use constant CMD_GET_ETAT_LED_STRIP  => 'M1614';
use constant CMD_GET_ETAT_LED_HEAD   => 'M1615';
use constant CMD_SET_LED_STRIP_ON    => 'M1202';
use constant CMD_SET_LED_STRIP_OFF   => 'M1203';
use constant CMD_SET_LED_HEAD_ON     => 'M1200';
use constant CMD_SET_LED_HEAD_OFF    => 'M1201';
use constant CMD_GET_VERSION         => 'M1400';
use constant CMD_SET_RFID_ON         => 'M1616';
use constant CMD_SET_RFID_OFF        => 'M1617';
use constant CMD_GET_ALL_TEMPER      => 'M1402';
use constant CMD_RAISE_PLATFORM      => 'M1905';
use constant CMD_GET_OFFSET_X        => 'M1661';
use constant CMD_GET_OFFSET_Y        => 'M1662';
use constant CMD_SET_OFFSET          => 'M1660';
use constant CMD_GET_POSITION        => 'M114';
use constant CMD_GET_CONSUMPTION     => 'M1907';
use constant CMD_CHECK_LEFT_SIDE     => 'M1625';

use constant CMD_STOP_PRINT    => 'M1000';
use constant CMD_RESET_PRINTER => 'M1100';

use constant CMD_START_SD_WRITE => 'M28';
use constant CMD_STOP_SD_WRITE  => 'M29';
use constant CMD_SELECT_SD_FILE => 'M23';
use constant CMD_START_SD_FILE  => 'M24';
use constant CMD_DELETE_SD_FILE => 'M30';

use constant CMD_UNIN_FILA_PLUS => 'G99';
use constant CMD_RELATIVE_POS   => 'G91';
use constant CMD_ABSOLUTE_POS   => 'G90';
use constant CMD_ALLOW_COLD_E   => 'M302';
use constant CMD_RELATIVE_EXTUD => 'M83';
use constant CMD_GET_ENDSTOPS   => 'M119';

use constant CMD_MOVE => 'G1';
use constant CMD_HOME => 'G28';

use constant CMD_GET_SPEED        => 'M1620';
use constant CMD_GET_ACCELERATION => 'M1623';
use constant CMD_SET_SPEED        => 'M1621';
use constant CMD_SET_ACCELERATION => 'M1624';
use constant CMD_GET_COLD_E       => 'M1622';

# general return code
use constant RC_OK => 0;

# thread start_print return code
use constant RC_IN_PRINT => 1;

# thread start_status return code
use constant RC_IDLE => -1;

# program return code
use constant EXIT_IN_PRINT       => -1;
use constant EXIT_IDLE           => -1;
use constant EXIT_ERROR_PRM      => -2;
use constant EXIT_ERROR_INTERNAL => -3;

# our global variable here
my $is_windows;
my $print_on;
my $myself;
my $mypath;
my ( $temperature1, $temperature2 );

$myself = abs_path($0);
$mypath = dirname($myself) . '/';

# our sub thread function here
sub start_print {
	if ( -e ( $mypath . FILENAME_PRINT ) ) {
		$print_on = STATUS_ON;
	}
	else {
		$print_on = STATUS_OFF;
	}

	# set our global status variable to on
	if ( $print_on == STATUS_ON ) {
		return RC_IN_PRINT;
	}
	else {
		$print_on = STATUS_ON;
	}

	# initialize status file
	{
		my $fp;
		open( $fp, '>', $mypath . FILENAME_PRINT );
		print $fp '1';
		close($fp);
	}

	# _start_print();
	if ( $is_windows == TRUE ) {
		system( 'start /B "" "perl" ' . $myself . ' -sp' );

		#		system( 'start "" "perl" ' . $myself . ' -sp' );
	}
	else {
		system( 'perl ' . $myself . ' -sp &' );
	}

	return RC_OK;
}

sub _start_print() {

	# start print simulation
	my ( $fp, $time_pass, $progress );

	#	my $cmd_time = int( rand(MAX_TIME_CMD_PRINT) );
	my $cmd_time = MAX_TIME_CMD_PRINT - TIME_CMD_PREPRINT;
	{

		# write status file
		open( $fp, '>', $mypath . FILENAME_PRINT );
		print $fp '1';
		close($fp);

		#		usleep(TIME_CMD_PREPRINT);
		for (
			$time_pass = 0 ;
			$time_pass < TIME_CMD_PREPRINT ;
			$time_pass = $time_pass + TIME_PRECISION
		  )
		{
			usleep(TIME_PRECISION);

			# check if we want to stop
			if ( -e ( $mypath . FILENAME_PRINT_STOP ) ) {

				# release two status lock after finishing printing
				unlink( $mypath . FILENAME_PRINT_STOP );
				unlink( $mypath . FILENAME_PRINT );

				return;
			}
		}
	}
	for (
		$time_pass = 0 ;
		$time_pass < $cmd_time ;
		$time_pass = $time_pass + TIME_PRECISION
	  )
	{
		usleep(TIME_PRECISION);
		$progress = int( $time_pass / $cmd_time * 100 );
		if ($progress == 0) {
			$progress = 1;
		}

		# check if we want to stop
		if ( -e ( $mypath . FILENAME_PRINT_STOP ) ) {
			unlink( $mypath . FILENAME_PRINT_STOP );
			last;
		}

		# check if we want to pause
		if ( -e ( $mypath . FILENAME_PRINT_PAUSE ) ) {

			# release pause status file
			unlink( $mypath . FILENAME_PRINT_PAUSE );

			# wait until resume status file, then release this status file
			do {
				usleep(TIME_PRECISION);
			} until( -e ( $mypath . FILENAME_PRINT_RESUME ) );
			unlink( $mypath . FILENAME_PRINT_RESUME );
		}

		# write status file
		open( $fp, '>', $mypath . FILENAME_PRINT );
		print $fp $progress;
		close($fp);
	}

	# release the status lock after finishing printing
	unlink( $mypath . FILENAME_PRINT );
	
	return;
}

sub stop_print {
	if ( -e ( $mypath . FILENAME_PRINT ) ) {
		$print_on = STATUS_ON;
	}
	else {
		$print_on = STATUS_OFF;
	}

	if ( $print_on == STATUS_ON ) {
		my $fp;
		open( $fp, '>', $mypath . FILENAME_PRINT_STOP )
		  or exit(EXIT_ERROR_INTERNAL);
		close($fp);
		# release the status as soon as possible
		unlink( $mypath . FILENAME_PRINT );
	}

	return;
}

sub pause_print {
	if ( -e ( $mypath . FILENAME_PRINT ) ) {
		$print_on = STATUS_ON;
	}
	else {
		$print_on = STATUS_OFF;
	}

	if ( $print_on == STATUS_ON ) {
		my $fp;
		open( $fp, '>', $mypath . FILENAME_PRINT_PAUSE )
		  or exit(EXIT_ERROR_INTERNAL);
		close($fp);
	}

	return;
}

sub resume_print {
	if ( -e ( $mypath . FILENAME_PRINT ) ) {
		$print_on = STATUS_ON;
	}
	else {
		$print_on = STATUS_OFF;
	}

	if ( $print_on == STATUS_ON ) {
		my $fp;
		open( $fp, '>', $mypath . FILENAME_PRINT_RESUME )
		  or exit(EXIT_ERROR_INTERNAL);
		close($fp);
	}

	return;
}

sub check_status {
	if ( -e ( $mypath . FILENAME_PRINT ) ) {
		$print_on = STATUS_ON;
	}
	else {
		$print_on = STATUS_OFF;
	}

	# check if we are in printing or not
	if ( $print_on == STATUS_OFF ) {
		print '0';
		return RC_IDLE;
	}
	else {
		my ( $fp, $progress );
		open( $fp, '<', $mypath . FILENAME_PRINT ) or exit(EXIT_ERROR_INTERNAL);
		$progress = <$fp>;
		close($fp);
		print $progress;

		#exit($progress);
	}

	return RC_OK;
}

sub get_temperature {
	my $data;
	my $command = shift;
	my $file_extruder;
	if ( $command eq CMD_GET_TEMPER1 ) {
		$file_extruder = $mypath . '1' . FILENAME_TEMPERX;
	}
	else {
		$file_extruder = $mypath . '2' . FILENAME_TEMPERX;
	}

	{
		my ( $fp, $temper );
		open( $fp, '<', $file_extruder )
		  or exit(EXIT_ERROR_INTERNAL);
		$temper = <$fp>;
		close($fp);
		print $temper;
	}

	return;
}

sub get_all_temperature {
	print "TEMP 1:";
	get_temperature(CMD_GET_TEMPER1);
	print " - TEMP 2:";
	get_temperature(CMD_GET_TEMPER2);
	
	return;
}

#sub get_temperature {
#	my $data;
#	my $id_extruder = _get_extruder() + 1;
#	{
#		my ( $fp, $temper );
#		open( $fp, '<', $mypath . $id_extruder . FILENAME_TEMPERX )
#		  or exit(EXIT_ERROR_INTERNAL);
#		$temper = <$fp>;
#		close($fp);
#		print $temper;
#	}
#
#	return;
#}

sub set_temperature {
	my $temper_to_set = shift @ARGV;

	# _set_temperature();
	if ( $is_windows == TRUE ) {
		system( 'start /B "" "perl" ' . $myself . ' -st ' . substr($temper_to_set, 1) );

		#		system( 'start "" "perl" ' . $myself . ' -st ' . $temper_to_set );
	}
	else {
		system( 'perl ' . $myself . ' -st ' . substr($temper_to_set, 1) . ' &' );
	}

	exit;
}

sub _set_temperature {
	my $temper_to_set = shift @ARGV;
	my $temper_current;
	my $id_extruder = _get_extruder() + 1;

	# check the status file to synchronize
	if ( -e ( $mypath . $id_extruder . FILENAME_T_ETATX ) ) {
		{
			my $fp;
			open( $fp, ">", $mypath . $id_extruder . FILENAME_T_STOPX )
			  or exit(EXIT_ERROR_INTERNAL);
			close($fp);
		}
		while ( -e ( $mypath . $id_extruder . FILENAME_T_STOPX ) ) {
			usleep(TIME_PRECISION);
		}
	}

	# create the status file
	{
		my $fp;
		open( $fp, ">", $mypath . $id_extruder . FILENAME_T_ETATX )
		  or exit(EXIT_ERROR_INTERNAL);
		close($fp);
	}

	# read temperature file
	{
		my $fp;
		local $/;    #Enable 'slurp' mode
		open( $fp, "<", $mypath . $id_extruder . FILENAME_TEMPERX )
		  or exit(EXIT_ERROR_INTERNAL);
		$temper_current = <$fp>;
		close($fp);
	}

	# start temperature simulation
	if ( $temper_current < $temper_to_set ) {
		while ( $temper_current < $temper_to_set ) {
			usleep(TIME_PRECISION);
			$temper_current = $temper_current + TEMPER_CHANGE_SPD / 2;

			# stop here if a new demand has been lanced
			if ( -e ( $mypath . $id_extruder . FILENAME_T_STOPX ) ) {
				unlink( $mypath . $id_extruder . FILENAME_T_STOPX );
				last;
			}

			# write temperature file
			my $fp;
			open( $fp, '>', $mypath . $id_extruder . FILENAME_TEMPERX );
			print $fp $temper_current;
			close($fp);
		}
	}
	elsif ( $temper_current > $temper_to_set ) {
		while ( $temper_current > $temper_to_set ) {
			usleep(TIME_PRECISION);
			$temper_current = $temper_current - TEMPER_CHANGE_SPD / 2;

			# stop here if a new demand has been lanced
			if ( -e ( $mypath . $id_extruder . FILENAME_T_STOPX ) ) {
				unlink( $mypath . $id_extruder . FILENAME_T_STOPX );
				last;
			}

			# write temperature file
			my $fp;
			open( $fp, '>', $mypath . $id_extruder . FILENAME_TEMPERX );
			print $fp $temper_current;
			close($fp);
		}
	}

	# delete status file
	unlink( $mypath . $id_extruder . FILENAME_T_ETATX );

	exit;
}

sub _get_extruder {
	my $data;
	{
		my ( $fp, $raw );
		local $/;    #Enable 'slurp' mode
		open( $fp, "<", $mypath . FILENAME_CONFIG )
		  or exit(EXIT_ERROR_INTERNAL);
		$raw = <$fp>;
		close($fp);
		$data = decode_json($raw);
	}

	return int( $data->{&JSON_CUR_EXTRUD} );
}

sub get_extruder {
	my $display = _get_extruder();

	print $display;

	return;
}

sub set_extruder {
	my $extrud_sel = shift;
	my $extrud_val;
	if ( $extrud_sel eq CMD_SET_EXTRUD2 ) {
		$extrud_val = CURRENT_EXTRUD2;
	}
	else {
		$extrud_val = CURRENT_EXTRUD1;
	}

	# read config file
	my $data;
	{
		my ( $fp, $raw );
		local $/;    #Enable 'slurp' mode
		open( $fp, "<", $mypath . FILENAME_CONFIG );
		$raw = <$fp>;
		close($fp);
		$data = decode_json($raw);
	}

	# change config if necessary
	my $test = int( $data->{&JSON_CUR_EXTRUD} );
	if ( int( $data->{&JSON_CUR_EXTRUD} ) != $extrud_val ) {
		my $fp;
		$data->{&JSON_CUR_EXTRUD} = $extrud_val;
		open( $fp, ">", $mypath . FILENAME_CONFIG );
		print $fp encode_json($data);
		close($fp);
	}

	return;
}

sub get_config {
	my $command = shift;

	# read config file
	my $data;
	{
		my ( $fp, $raw );
		local $/;    #Enable 'slurp' mode
		open( $fp, "<", $mypath . FILENAME_CONFIG );
		$raw = <$fp>;
		close($fp);
		$data = decode_json($raw);
	}

	if ( $command eq CMD_GET_EXTRUD ) {
		print $data->{&JSON_CUR_EXTRUD};
	}
	else {
		die(EXIT_ERROR_INTERNAL);    #never reach here
	}

	return;
}

sub get_cartridge_label {
	my $command = shift;
	my ( $filepath, $filepath_fb );
	my $data;

	# get the right file path
	if ( $command eq CMD_GET_LEFT_LAB ) {
		$filepath    = $mypath . FILENAME_CARTRIDGE_L_LOADED;
		$filepath_fb = $mypath . FILENAME_CARTRIDGE_L_UNLOAD;
	}
	elsif ( $command eq CMD_GET_RIGHT_LAB ) {
		$filepath    = $mypath . FILENAME_CARTRIDGE_R_LOADED;
		$filepath_fb = $mypath . FILENAME_CARTRIDGE_R_UNLOAD;
	}
	else {
		die(EXIT_ERROR_INTERNAL);    #never reach here
	}

	# return if no file
	unless ( -e $filepath ) {
		if ( -e $filepath_fb ) {
			$filepath = $filepath_fb;
		}
		else {
			return;
		}
	}

	{
		my ( $fp, $raw );
		local $/;    #Enable 'slurp' mode
		open( $fp, "<", $filepath );
		$raw = <$fp>;
		close($fp);
		$data = decode_json($raw);
	}
	print $data->{&JSON_LABEL};

	return;
}

sub load_filament {
	my $command = shift;
	my ( $has_file, $no_file );

	if ( -e ( $mypath . FILENAME_CARTRIDGE_ETAT ) ) {
		return;
	}

	# get right parameter
	if ( $command eq CMD_LOAD_LEFT_FILA ) {
		$has_file = $mypath . FILENAME_CARTRIDGE_L_UNLOAD;
		$no_file  = $mypath . FILENAME_CARTRIDGE_L_LOADED;
	}
	elsif ( $command eq CMD_LOAD_RIGHT_FILA ) {
		$has_file = $mypath . FILENAME_CARTRIDGE_R_UNLOAD;
		$no_file  = $mypath . FILENAME_CARTRIDGE_R_LOADED;
	}
	else {
		die(EXIT_ERROR_INTERNAL);
	}

	if ( -e $has_file && !( -e $no_file ) ) {
		print 'ok';
	}
	else {
		print 'already loaded';
		return;
	}

	# _start_filament();
	if ( $is_windows == TRUE ) {
		system(
			'start /B "" "perl" ' . $myself . ' -sfila ' . "$has_file $no_file" );

	  #		system( 'start "" "perl" ' . $myself . ' -sf ' . "$has_file $no_file");
	}
	else {
		system( 'perl ' . $myself . ' -sfila ' . "$has_file $no_file" . ' &' );
	}

	return;
}

sub unload_filament {
	my $command = shift;
	my ( $has_file, $no_file );

	if ( -e ( $mypath . FILENAME_CARTRIDGE_ETAT ) ) {
		return;
	}

	# get right parameter
	if ( $command eq CMD_UNIN_LEFT_FILA ) {
		$has_file = $mypath . FILENAME_CARTRIDGE_L_LOADED;
		$no_file  = $mypath . FILENAME_CARTRIDGE_L_UNLOAD;
	}
	elsif ( $command eq CMD_UNIN_RIGHT_FILA ) {
		$has_file = $mypath . FILENAME_CARTRIDGE_R_LOADED;
		$no_file  = $mypath . FILENAME_CARTRIDGE_R_UNLOAD;
	}
	else {
		die(EXIT_ERROR_INTERNAL);
	}

	if ( -e $has_file && !( -e $no_file ) ) {
		print 'ok';
	}
	else {
		print 'already unloaded';
		return;
	}

	# _start_filament();
	if ( $is_windows == TRUE ) {
		system(
			'start /B "" "perl" ' . $myself . ' -sfila ' . "$has_file $no_file" );

	  #		system( 'start "" "perl" ' . $myself . ' -sf ' . "$has_file $no_file");
	}
	else {
		system( 'perl ' . $myself . ' -sfila ' . "$has_file $no_file" . ' &' );
	}

	return;
}

sub _start_filament {
	my $has_file = shift;
	my $no_file  = shift;

	unless ( -e $has_file && !( -e $no_file ) ) {
		die(EXIT_ERROR_INTERNAL);
	}

	# create the status file
	{
		my $fp;
		open( $fp, ">", $mypath . FILENAME_CARTRIDGE_ETAT )
		  or exit(EXIT_ERROR_INTERNAL);
		close($fp);
	}

	usleep(MAX_TIME_CMD_FILA);
	rename( $has_file, $no_file );

	# release the status lock after finishing
	unlink( $mypath . FILENAME_CARTRIDGE_ETAT );

	return;
}

sub check_filament_state {
	my $command = shift;
	my $filepath;

	#	if ( -e ( $mypath . FILENAME_CARTRIDGE_ETAT ) ) {
	#		print 'in loading / unloading';    # not in spec file, but useful
	#		return;
	#	}
	if ( $command eq CMD_GET_ETAT_LEFT_FILA ) {
		$filepath = $mypath . FILENAME_CARTRIDGE_L_LOADED;
	}
	elsif ( $command eq CMD_GET_ETAT_RIGHT_FILA ) {
		$filepath = $mypath . FILENAME_CARTRIDGE_R_LOADED;
	}
	else {
		die(EXIT_ERROR_INTERNAL);
	}

	if ( -e $filepath ) {
		print 'filament';
	}
	else {
		print 'no filament';
	}

	return;
}

sub check_led_state {
	my $command = shift;
	my $data;
	{
		my ( $fp, $raw );
		local $/;    #Enable 'slurp' mode
		open( $fp, "<", $mypath . FILENAME_CONFIG )
		  or exit(EXIT_ERROR_INTERNAL);
		$raw = <$fp>;
		close($fp);
		$data = decode_json($raw);
	}

	if ( $command eq CMD_GET_ETAT_LED_STRIP ) {
		print $data->{&JSON_LED_STRIP};
	}
	elsif ( $command eq CMD_GET_ETAT_LED_HEAD ) {
		print $data->{&JSON_LED_HEAD};
	}

	return;
}

sub set_led {
	my $command = shift;
	my ($led_sel, $led_val);
	
	if ( $command eq CMD_SET_LED_STRIP_ON ) {
		$led_sel = JSON_LED_STRIP;
		$led_val = 1;
	}
	elsif ( $command eq CMD_SET_LED_STRIP_OFF ) {
		$led_sel = JSON_LED_STRIP;
		$led_val = 0;
	}
	elsif ( $command eq CMD_SET_LED_HEAD_ON ) {
		$led_sel = JSON_LED_HEAD;
		$led_val = 1;
	}
	elsif ( $command eq CMD_SET_LED_HEAD_OFF ) {
		$led_sel = JSON_LED_HEAD;
		$led_val = 0;
	}
	else {
		return; # error - do not treat
	}

	# read config file
	my $data;
	{
		my ( $fp, $raw );
		local $/;    #Enable 'slurp' mode
		open( $fp, "<", $mypath . FILENAME_CONFIG );
		$raw = <$fp>;
		close($fp);
		$data = decode_json($raw);
	}

	# change config if necessary
	my $test = int( $data->{$led_sel} );
	if ( int( $data->{$led_sel} ) != $led_val ) {
		my $fp;
		$data->{$led_sel} = $led_val;
		open( $fp, ">", $mypath . FILENAME_CONFIG );
		print $fp encode_json($data);
		close($fp);
	}

	return;
}

sub show_endstops {
	print <<ENDSTOP
Reporting endstop status
x_min: TRIGGERED
x_max: open
y_min: TRIGGERED
y_max: open
z_min: TRIGGERED
z_max: open
E0: TRIGGERED
E1: TRIGGERED
ENDSTOP
;
	return;
}

sub reset_printer {
	_set_default_temper_file();
	_set_default_config_file();

	return;
}

sub _set_default_temper_file {
	my $fp;

	open( $fp, '>', $mypath . FILENAME_TEMPER1 );
	print $fp DEFAULT_TEMPER;
	close($fp);
	open( $fp, '>', $mypath . FILENAME_TEMPER2 );
	print $fp DEFAULT_TEMPER;
	close($fp);

	return;
}

sub _set_default_config_file {
	my $fp;
	my %default = (
		&JSON_NB_EXTRUD  => 2,
		&JSON_CUR_EXTRUD => CURRENT_EXTRUD1,
		&JSON_LED_STRIP  => 0,
		&JSON_LED_HEAD   => 0,

		#		&JSON_LEFT_LABEL  => DEFAULT_LEFT_LAB,
		#		&JSON_RIGHT_LABEL => DEFAULT_RIGHT_LAB,
	);

	my $data = encode_json( \%default );
	open( $fp, '>', $mypath . FILENAME_CONFIG );
	print $fp $data;
	close($fp);

	return;
}

sub _set_default_cartridge {
	my $abb_cartridge = shift;
	my ( $file_cartridge, $fp, $data );
	my %default;

	if ( $abb_cartridge eq 'l' ) {
		$file_cartridge = FILENAME_CARTRIDGE_L_LOADED;
		%default = ( &JSON_LABEL => DEFAULT_LEFT_LAB );
	}
	else {    # ( $abb_cartridge eq 'r' )
		$file_cartridge = FILENAME_CARTRIDGE_R_LOADED;
		%default = ( &JSON_LABEL => DEFAULT_RIGHT_LAB );
	}

	$data = encode_json( \%default );
	open( $fp, '>', $file_cartridge );
	print $fp $data;
	close($fp);

	return;
}

sub _init_files {
	unless ( -e ( $mypath . FILENAME_TEMPER1 )
		&& -e ( $mypath . FILENAME_TEMPER2 ) )
	{
		_set_default_temper_file();
	}
	unless ( -e ( $mypath . FILENAME_CONFIG ) ) {
		_set_default_config_file();
	}
	unless ( -e ( $mypath . FILENAME_CARTRIDGE_L_LOADED )
		|| -e ( $mypath . FILENAME_CARTRIDGE_L_UNLOAD ) )
	{
		_set_default_cartridge('l');
	}
	unless ( -e ( $mypath . FILENAME_CARTRIDGE_R_LOADED )
		|| -e ( $mypath . FILENAME_CARTRIDGE_R_UNLOAD ) )
	{
		_set_default_cartridge('r');
	}

	return;
}

#=========================
# main function below
#=========================

if ( $^O eq 'MSWin32' ) {
	$is_windows = TRUE;
}
else {
	$is_windows = FALSE;
}
$myself = abs_path($0);
$mypath = dirname($myself) . '/';
_init_files();

my %opt = ();
{
	my %options = (
		'help|h' => \$opt{help},
		'f'      => \$opt{openfile},
		'd'      => \$opt{directfile},
		'0'      => \$opt{extruder0},
		'1'      => \$opt{extruder1},
		'sf|s'   => \$opt{stopfile},
		'pf|p'   => \$opt{pausefile},
		'rf|r'   => \$opt{resumefile},
		'st'     => \$opt{settemperature},
		'sp'     => \$opt{startprint},
		'sfila'  => \$opt{startfilament},
		'rmctl'  => \$opt{removecartridgeleft},
		'rmctr'  => \$opt{removecartridgeright},
		'isctl'  => \$opt{insertcartridgeleft},
		'isctr'  => \$opt{insertcartridgeright},
	);
	GetOptions(%options);
}

if ( $opt{help} ) {
	usage(RC_OK);    #print help
}
elsif ( $opt{settemperature} ) {

	#run program here
	_set_temperature();

	exit(RC_OK);
}
elsif ( $opt{startprint} ) {
	my $filename = shift @ARGV;

	#run program here
	_start_print();

	exit(RC_OK);
}
elsif ( $opt{openfile} ) {
	my $filename = shift @ARGV;

	#run program here
	start_print();

	exit(RC_OK);
}
elsif ( $opt{stopfile} ) {
	my $filename = shift @ARGV;

	#run program here
	stop_print();

	exit(RC_OK);
}
elsif ( $opt{pausefile} ) {
	my $filename = shift @ARGV;

	#run program here
	pause_print();

	exit(RC_OK);
}
elsif ( $opt{resumefile} ) {
	my $filename = shift @ARGV;

	#run program here
	resume_print();

	exit(RC_OK);
}
elsif ( $opt{startfilament} ) {
	my $has_file = shift @ARGV;
	my $no_file  = shift @ARGV;

	#run program here
	_start_filament( $has_file, $no_file );

	exit(RC_OK);
}
elsif ( $opt{removecartridgeleft} ) {
	my $file_ori = $mypath . FILENAME_CARTRIDGE_L_UNLOAD;
	my $file_fin = $mypath . '_' . FILENAME_CARTRIDGE_L_UNLOAD;

	#rename file here
	rename($file_ori, $file_fin);
	
	exit(RC_OK);
}
elsif ( $opt{removecartridgeright} ) {
	my $file_ori = $mypath . FILENAME_CARTRIDGE_R_UNLOAD;
	my $file_fin = $mypath . '_' . FILENAME_CARTRIDGE_R_UNLOAD;
	
	#rename file here
	rename($file_ori, $file_fin);
	
	exit(RC_OK);
}
elsif ( $opt{insertcartridgeleft} ) {
	my $file_ori = $mypath . '_' . FILENAME_CARTRIDGE_L_UNLOAD;
	my $file_fin = $mypath . FILENAME_CARTRIDGE_L_UNLOAD;
	
	#rename file here
	rename($file_ori, $file_fin);
	
	exit(RC_OK);
}
elsif ( $opt{insertcartridgeright} ) {
	my $file_ori = $mypath . '_' . FILENAME_CARTRIDGE_R_UNLOAD;
	my $file_fin = $mypath . FILENAME_CARTRIDGE_R_UNLOAD;
	
	#rename file here
	rename($file_ori, $file_fin);
	
	exit(RC_OK);
}
else {
	my $command;

	#check command
	if ( ( scalar @ARGV ) == 0 ) {
		usage(EXIT_ERROR_PRM);
	}

	$command = shift @ARGV;

	if ( $command eq CMD_CHECK ) {

		#cmd: check status
		check_status();
	}

	#	elsif ( $command eq CMD_GET_TEMPER ) {
	#
	#		#cmd: get temperature 1 / 2
	#		get_temperature();
	#	}
	elsif ( $command eq CMD_GET_TEMPER1 || $command eq CMD_GET_TEMPER2 ) {

		#cmd: get temperature 1 / 2
		get_temperature($command);
	}
	elsif ( $command eq CMD_SET_TEMPER ) {

		#cmd: set temperature 1 / 2
		set_temperature();
	}
	elsif ( $command eq CMD_SET_EXTRUD1 || $command eq CMD_SET_EXTRUD2 ) {

		#cmd: set extruder 1 / 2
		set_extruder($command);
	}
	elsif ( $command eq CMD_GET_EXTRUD ) {

		#cmd: get current extruder
		get_config($command);
	}
	elsif ( $command eq CMD_GET_LEFT_LAB || $command eq CMD_GET_RIGHT_LAB ) {

		#cmd: get cartridge L / R
		get_cartridge_label($command);
	}
	elsif ( $command eq CMD_LOAD_LEFT_FILA || $command eq CMD_LOAD_RIGHT_FILA )
	{

		#cmd: load filament L / R
		load_filament($command);
	}
	elsif ( $command eq CMD_UNIN_LEFT_FILA || $command eq CMD_UNIN_RIGHT_FILA )
	{

		#cmd: unload filament L / R
		unload_filament($command);
	}
	elsif ($command eq CMD_GET_ETAT_LEFT_FILA
			|| $command eq CMD_GET_ETAT_RIGHT_FILA )
	{

		#cmd: check filament state L / R
		check_filament_state($command);
	}
	elsif ( $command eq CMD_STOP_PRINT ) {

		#cmd: stop printing
		stop_print();
	}
	elsif ( $command eq CMD_RESET_PRINTER ) {

		#cmd: reset printer
		reset_printer();
	}
	elsif ( $command eq CMD_GET_ETAT_LED_STRIP
			|| $command eq CMD_GET_ETAT_LED_HEAD)
	{

		#cmd: get led state strips / head
		check_led_state($command);
	}
	elsif ( $command eq CMD_SET_LED_STRIP_ON
			|| $command eq CMD_SET_LED_STRIP_OFF
			|| $command eq CMD_SET_LED_HEAD_ON
			|| $command eq CMD_SET_LED_HEAD_OFF)
	{

		#cmd: set led state strips / head
		set_led($command);
	}
	elsif ( $command eq CMD_GET_ENDSTOPS ) {

		#cmd: move / extrude / special g99
		show_endstops();
	}
	elsif ( $command eq CMD_CHECK_LEFT_SIDE ) {

		#cmd: check left side
		print "1\n";
	}
	elsif ( $command eq CMD_START_SD_WRITE
			|| $command eq CMD_STOP_SD_WRITE
			|| $command eq CMD_SELECT_SD_FILE
			|| $command eq CMD_START_SD_FILE
			|| $command eq CMD_DELETE_SD_FILE )
	{

		#cmd: sd card
		exit(RC_OK);
	}
	elsif ( $command eq CMD_MOVE
			|| $command eq CMD_HOME
			|| $command eq CMD_RELATIVE_POS
			|| $command eq CMD_ABSOLUTE_POS
			|| $command eq CMD_RELATIVE_EXTUD
			|| $command eq CMD_UNIN_FILA_PLUS
			|| $command eq CMD_ALLOW_COLD_E
			|| $command eq CMD_SET_RFID_OFF
			|| $command eq CMD_SET_RFID_ON
			|| $command eq CMD_SET_RIGHT_LAB
			|| $command eq CMD_SET_LEFT_LAB
			|| $command eq CMD_RAISE_PLATFORM
			|| $command eq CMD_SET_OFFSET ) {

		#cmd: move / extrude / special g99 / etc.
		exit(RC_OK);
	}
	elsif ( $command eq CMD_GET_SPEED ) {
		print "2000\n";
	}
	elsif ( $command eq CMD_GET_ACCELERATION ) {
		print "1000\n";
	}
	elsif ( $command eq CMD_GET_COLD_E ) {
		print "0\n";
	}
	elsif ( $command eq CMD_GET_OFFSET_X
			|| $command eq CMD_GET_OFFSET_Y ) {
		print "1\n";
	}
	elsif ( $command eq CMD_GET_POSITION ) {
		print "X:1.23Y:3.21Z:0.00E:27.66 Count X: 1.89Y:3.98Z:0.99\n";
	}
	elsif ( $command eq CMD_GET_CONSUMPTION ) {
		print "E0:123.456\nE1:0.00\n";
	}
	elsif ( $command eq CMD_SET_SPEED
			|| $command eq CMD_SET_ACCELERATION ) {

		#cmd: set speed / acceleration
		exit(RC_OK);
	}
	elsif ( $command eq CMD_GET_VERSION ) {
		print "1.0.99\n";
	}
	elsif ( $command eq CMD_GET_ALL_TEMPER ) {
		get_all_temperature();
	}
	else {    #default, wrong cmd, send help
		usage(EXIT_ERROR_PRM);
	}
}

print "\n\n[<-] ok\n";

exit(RC_OK);

sub usage {
	my ($exit_code) = @_;

	#print 'usage' . $exit_code;
	exit($exit_code);
}

