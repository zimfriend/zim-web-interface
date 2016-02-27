#!/usr/bin/perl -w
use strict;
use warnings;

use Getopt::Long qw(:config no_auto_abbrev);
use Time::HiRes qw(usleep);
#use JSON;
use Cwd qw(abs_path);
use File::Basename;
#use Scalar::Util qw(looks_like_number);

# our constant variable here
use constant TRUE  => 1;
use constant FALSE => 0;

# general return code
use constant RC_OK => 0;
# program return code
use constant EXIT_NO_FILE        => -1;
use constant EXIT_ERROR_PRM      => -2;
use constant EXIT_ERROR_INTERNAL => -9;

use constant CEILING_HEAT => 50;

use constant ALLOW_COMMAND_LIST => [
	'T0',		# switch to right extruder
	'T1',		# switch to left extruder
	'G90',		# absolute coordinate
	'G91',		# relative coordinate
	'M82',		# absolute extrusion length
	'M83',		# relative extrusion length
	'M203',		# set maximum speed
	'G0',		# same as g1
	'G1',		# movement
	'G2',		# movement cw
	'G3',		# movement ccw
	'G4',		# wait
	'G28',		# home
	'M84',		# disable stepper
	'M104',		# set extruder temperature
	'M109',		# wait extruder temperature
	'M140',		# set bed temperature
#	'M190',		# wait bed temperature
	'M106',		# fan on
	'M107',		# fan off
	'M400',		# finish movements
	'M1200',	# head led on
	'M1201',	# head led off
	'M1202',	# strip led on
	'M1203',	# strip led off
	'M1621',	# set current speed
	'M1624',	# set current acceleration
	'M1650',	# compensation of retraction for left extruder
	'M1651',	# compensation of retraction for right extruder
	'M1652',	# retraction for left extruder
	'M1653',	# retraction for right extruder
];

# our global variable here
my $is_windows;
my $myself;
my $mypath;

my $extruder_current = 'T0';

sub alter_file {
	my ($filename, $temp_l, $temp_r, $temp_ls, $temp_rs) = @_;
	my @lines;
	my $m104_before_m109 = TRUE;
	
	open my $fh, '<', $filename;
	if (tell($fh) != -1) {
		@lines = <$fh>;
		close $fh;
	} else {
		return EXIT_NO_FILE; #todo here
	}
	
	foreach my $line (@lines) {
		my $pos_extruder = -1;
		my $pos_comment = -1;
		my $pos_m109 = -1;
		my $pos_m104 = -1;
		my $pos_temp = -1;
		
		# do not count comment and empty line
		$line =~ s/\R//g;
		$pos_comment = index($line, ';');
		if ($line eq "" || $pos_comment == 0) {
			next;
		}
		
		# control current extruder
		foreach my $extruder_test ("T0", "T1") {
			$pos_extruder = index($line, $extruder_test);
			
			if ($pos_extruder != -1 && $pos_extruder == 0) {
#				# do not count key word in comment
#				if ($pos_comment != -1 && $pos_comment < $pos_extruder) {
#					print $line . "\n";
#					next;
#				}
				$extruder_current = $extruder_test;
			}
		}
		
		# count of m109
		$pos_m109 = index($line, "M109");
		if ($pos_m109 != -1) {
			# do not count key word in comment
			unless ($pos_comment != -1 && $pos_comment < $pos_m109) {
				my $extruder_set = "";
				
				# count m109 and tx in only one line
				if (index($line, "T0") != -1) {
					$extruder_set = "T0";
				}
				elsif (index($line, "T1") != -1) {
					$extruder_set = "T1";
				}
				else {
					$extruder_set = $extruder_current;
				}
				
				# start output
				if ($extruder_set eq "T0") {
					print "M109 S" . $temp_r . " T0\n";
				}
				else { # T1
					print "M109 S" . $temp_l . " T1\n";
				}
				
				$m104_before_m109 = FALSE;
				next;
			}
		}
		
		# count of m104
		$pos_m104 = index($line, "M104");
		if ($pos_m104 != -1) {
			# do not count the m104 command before first m109
			if ($m104_before_m109 == TRUE) {
				print $line . "\n";
				next;
			}
			
			# do not count key word in comment
			unless ($pos_comment != -1 && $pos_comment < $pos_m104) {
				my $extruder_set = "";
				my $string_temp = "";
				my $offset = 0;
				
				# count m104 and tx in only one line
				if (index($line, "T0") != -1) {
					$extruder_set = "T0";
				}
				elsif (index($line, "T1") != -1) {
					$extruder_set = "T1";
				}
				else {
					$extruder_set = $extruder_current;
				}
				
				# get temperature
				$pos_temp = index ($line, 'S', $offset);
				while ($pos_temp < $pos_m104) {
					$offset = $pos_temp + 1;
					$pos_temp = index ($line, 'S', $offset);
				}
				if ($pos_comment != -1) {
					$string_temp = substr($line, $pos_temp + 1, $pos_comment - $pos_temp - 1);
				}
				else {
					$string_temp = substr($line, $pos_temp + 1);
				}
				
				# change only when we are in heating
#				print "temp: " . $string_temp . "\r\n";
#				print "look: " . looks_like_number $string_temp . "\r\n\r\n";
				if ((int $string_temp) > CEILING_HEAT) {
					if ($extruder_set eq "T0") {
						print "M104 S" . $temp_rs . " T0\n";
					}
					else { # T1
						print "M104 S" . $temp_ls . " T1\n";
					}
					next;
				}
			}
		}
		
		print $line . "\n";
	}
	
	return RC_OK;
}

sub analyze_file {
#	my $filename;
	my @lines;
	my ($filename) = @_;
	my ($nb_extruder, $temp_l, $temp_r, $temp_b);
	
	open my $fh, '<', $filename;
	if (tell($fh) != -1) {
		@lines = <$fh>;
		close $fh;
	} else {
		return EXIT_NO_FILE; #todo here
	}
	
	$nb_extruder = 1;
	
	foreach my $line (@lines) {
		my $pos_extruder = -1;
		my $pos_comment = -1;
		my $pos_m109 = -1;
		my $pos_m190 = -1;
		my $pos_temp = -1;
		
		# do not count comment and empty line
		$line =~ s/\R//g;
		$pos_comment = index($line, ';');
		if ($line eq "" || $pos_comment == 0) {
			next;
		}
		
		# control current extruder
		foreach my $extruder_test ("T0", "T1") {
			$pos_extruder = index($line, $extruder_test);
			
			if ($pos_extruder != -1 && $pos_extruder == 0) {
				# do not count key word in comment
				if ($pos_comment != -1 && $pos_comment < $pos_extruder) {
					next;
				}
				
				if ($extruder_test eq "T1") {
					$nb_extruder = ($nb_extruder < 2) ? 2 : $nb_extruder;
				}
				$extruder_current = $extruder_test;
			}
		}
		
		# count of m109
		$pos_m109 = index($line, "M109");
		if ($pos_m109 != -1) {
			# do not count key word in comment
			unless ($pos_comment != -1 && $pos_comment < $pos_m109) {
				my $extruder_set = "";
				my $string_temp = "";
				my $offset = 0;
				
				# count m109 and tx in only one line
				if (index($line, "T0") != -1) {
					$extruder_set = "T0";
				}
				elsif (index($line, "T1") != -1) {
					$extruder_set = "T1";
				}
				else {
					$extruder_set = $extruder_current;
				}
				
				# get temperature
				$pos_temp = index ($line, 'S', $offset);
				while ($pos_temp < $pos_m109) {
					$offset = $pos_temp + 1;
					$pos_temp = index ($line, 'S', $offset);
				}
				if ($pos_comment != -1) {
					$string_temp = substr($line, $pos_temp + 1, $pos_comment - $pos_temp - 1);
				}
				else {
					$string_temp = substr($line, $pos_temp + 1);
				}
				
				# change only when we are in heating
				no warnings 'numeric';
				my $temp_temp = int $string_temp;
				use warnings 'numeric';
				if ($temp_temp > CEILING_HEAT) {
					if ($extruder_set eq "T0") {
						$temp_r = defined($temp_r) ? $temp_r : $temp_temp;
					}
					else { # T1
						$temp_l = defined($temp_l) ? $temp_l : $temp_temp;
					}
					next;
				}
			}
		}
		
		# count of m190
		$pos_m190 = index($line, "M190");
		if ($pos_m190 != -1) {
			# do not count key word in comment
			unless ($pos_comment != -1 && $pos_comment < $pos_m190) {
				my $string_temp = "";
				my $offset = 0;
				
				# get temperature
				$pos_temp = index ($line, 'S', $offset);
				while ($pos_temp < $pos_m190) {
					$offset = $pos_temp + 1;
					$pos_temp = index ($line, 'S', $offset);
				}
				if ($pos_comment != -1) {
					$string_temp = substr($line, $pos_temp + 1, $pos_comment - $pos_temp - 1);
				}
				else {
					$string_temp = substr($line, $pos_temp + 1);
				}
				
				# change only when we are in heating
				no warnings 'numeric';
				my $temp_temp = int $string_temp;
				use warnings 'numeric';
				if ($temp_temp > CEILING_HEAT) {
					$temp_b = defined($temp_b) ? $temp_b : $temp_temp;
					next;
				}
			}
		}
		
		# do not treat the rest if we get all info
		if (defined($temp_l) && defined($temp_r) && defined($temp_b)) {
			last;
		}
	}
	
	{
		use JSON;
		
		my %array_output = ();
		my %array_check = (T0 => $temp_r, T1 => $temp_l, B => $temp_b);
		$array_output{'N'} = $nb_extruder;
		
		for my $key ( keys %array_check) {
			my $value = $array_check{$key};
			if (defined($value) && int $value != 0) {
				$array_output{$key} = $value;
			}
		}
		
		print to_json(\%array_output);
	}
	
	return RC_OK;
}

sub change_extruder {
	my ($filename) = @_;
	my @lines;
	
	open my $fh, '<', $filename;
	if (tell($fh) != -1) {
		@lines = <$fh>;
		close $fh;
	} else {
		return EXIT_NO_FILE; #todo here
	}
	
	# inverse the default extruder at first
	# to avoid no change of extruder in body
	print "T1\n";
	
	foreach my $line (@lines) {
		my $pos_extruder = -1;
		my $pos_comment = -1;
		
		# do not count comment and empty line
		$line =~ s/\R//g;
		$pos_comment = index($line, ';');
		if ($line eq "" || $pos_comment == 0) {
			next;
		}
		
		# control current extruder
		foreach my $extruder_test ("T0", "T1") {
			$pos_extruder = index($line, $extruder_test);
			
			if ($pos_extruder != -1) {
				if ($pos_extruder == 0) {
					$extruder_current = $extruder_test;
					if ($extruder_test eq "T0") { # T0
						$line = "T1";
					}
					else { # T1
						$line = "T0";
					}
					last;
				}
				else { # special gcode, such like M104, M109, etc.
					my $extruder_set = ($extruder_test eq "T0") ? "T1" : "T0";
					
					while ($pos_extruder > -1) {
						substr($line, $pos_extruder, length($extruder_test), $extruder_set);
						$pos_extruder = index($line, $extruder_test, $pos_extruder + length($extruder_set));
					}
					last; # normally, there is only one extruder in one line (except in comment)
				}
			}
		}
		
		print $line . "\n";
	}
	
	return RC_OK;
}

sub check_file {
	my @lines;
	my $line_num = 0;
	my $check_status = 'ok';
	my ($filename) = @_;
	my %array_check = ();
	
	open my $fh, '<', $filename;
	if (tell($fh) != -1) {
		@lines = <$fh>;
		close $fh;
	} else {
		return EXIT_NO_FILE; #todo here
	}
	
	foreach my $line (@lines) {
		my @parameter;
		my $pos_comment = -1;
		
		# treat line
		++$line_num;
		chomp($line);
		$pos_comment = index($line, ';');
		if ($line eq "" || $pos_comment == 0) {
			next;
		}
		
		if ($pos_comment != -1) {
			$line = substr($line, 0, $pos_comment);
		}
		$line =~ s/^\s+|\s+$//g;
		
		@parameter = split(/ /, $line);
		if (scalar @parameter > 0) {
			unless ($parameter[0] ~~ ALLOW_COMMAND_LIST) {
				$check_status = 'ko';
				$array_check{$line_num} = $parameter[0];
				next;
			}
		}
		else { # internal error (can not split line)
			$array_check{$line_num} = undef;
		}
		
#		print $line . "\n";
	}
	
	{
		use JSON;
		
		my %array_output = ();
	
		$array_output{status} = $check_status;
		$array_output{result} = \%array_check;
		print to_json(\%array_output);
	}
	
	return 0;
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

my %opt = ();
{
	my %options = (
		'help|h'       => \$opt{help},
		'file|f=s'     => \$opt{openfile},
		'temp_l|l=s'   => \$opt{temp_l},	# left temperature for first layer (or all layer)
		'temp_r|r=s'   => \$opt{temp_r},	# right temperature for first layer (or all layer)
		'temp_ls|ll=s' => \$opt{temp_ls},	# left temperature for other layer (if exists)
		'temp_rs|rr=s' => \$opt{temp_rs},	# right temperature for other layer (if exists)
		'analyze|a'    => \$opt{analyze},
		'change|c'     => \$opt{change_e},
		'verify|v'     => \$opt{verify},
	);
	GetOptions(%options);
}

if ( $opt{help} ) {
	usage(RC_OK);    #print help
}
elsif ( $opt{temp_l} || $opt{temp_r} ) {
	my ($temp_ls, $temp_rs);
	unless ( $opt{openfile} ) {
		usage(EXIT_ERROR_PRM);
	}
	if ( defined($opt{temp_ls}) && int $opt{temp_ls} != 0 ) {
		$temp_ls = int $opt{temp_ls};
	} else {
		$temp_ls = int $opt{temp_l};
	}
	if ( defined($opt{temp_rs}) && int $opt{temp_rs} != 0 ) {
		$temp_rs = int $opt{temp_rs};
	} else {
		$temp_rs = int $opt{temp_r};
	}
	
	my $rc = alter_file($opt{openfile}, int $opt{temp_l}, int $opt{temp_r}, $temp_ls, $temp_rs);
	
	exit($rc);
}
elsif ( $opt{analyze} ) {
	unless ( $opt{openfile} ) {
		my $rc = usage(EXIT_ERROR_PRM);
		
		exit($rc);
	}
	else {
		my $rc = analyze_file($opt{openfile});
	
		exit($rc);
	}
}
elsif ( $opt{verify} ) {
	unless ( $opt{openfile} ) {
		my $rc = usage(EXIT_ERROR_PRM);
		
		exit($rc);
	}
	else {
		my $rc = check_file($opt{openfile});
	
		exit($rc);
	}
}
elsif ( $opt{change_e} ) {
	unless ( $opt{openfile} ) {
		my $rc = usage(EXIT_ERROR_PRM);
		
		exit($rc);
	}
	else {
		my $rc = change_extruder($opt{openfile});
	
		exit($rc);
	}
}
else {
	my $command;

	#check command
	if ( ( scalar @ARGV ) == 0 ) {
		usage(EXIT_ERROR_PRM);
	}

	$command = shift @ARGV;

	if ( $command eq '' ) {

		#cmd: do nothing, never reach here
	}
	else {    #default, wrong cmd, send help
		usage(EXIT_ERROR_PRM);
	}
}

exit(RC_OK);

sub usage {
	my ($exit_code) = @_;

	print 'usage' . $exit_code;
	exit($exit_code);
}

