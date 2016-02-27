<div data-role="page" data-url="/advanceduser">
	<div id="overlay"></div>
	<header data-role="header" class="page-header"> </header>
	<div class="logo">
		<div id="link_logo"></div>
	</div>

	<div data-role="content">
		<div id="container">
			<div data-role="header" data-theme="b">
				<h1>Warning</h1>
			</div>
			<div data-role="main" class="ui-content">
				The uploading of G-code files to the Zim printer is an advanced
				operation that requires thorough knowledge of 3D printing. Improper
				use can cause errors and even damage your printer.<br /> <br /> Zim
				uses a branch of Marlin, published at: <a
					href="https://github.com/Zeepro/marlin-zeepro" target="_blank">https://github.com/Zeepro/marlin-zeepro</a><br />
				<br /> The few specific G-codes can be found in the code; they are
				related to printer management (cartridges loading, filament
				management, etc.) and are not strictly required when printing.<br />
				<br /> Uploading and running a G-code file is not supported by the
				printer state management (ie, the feature redirecting to the viewing
				page when a normal printing is in progress). The user should ensure
				that no command is sent during an active print - the Zim may or may
				not be capable to cue multiple requests. Viewing the current print
				must be done via the Zim\'s manage page.<br /> <br /> Provided that
				G-code usage is not part of the official set of ZeeproShare's
				features, Zeepro cannot confirm 100% the correct functionality of
				such usage.<br /> <br /> Therefore, users understand:
				<ul>
					<li>They will use the G-code feature at their own risk.</li>
					<li>Zim's initial warranty will void if the G-code feature is used
						at least once.</li>
				</ul>
				Enter the serial number of your Zim, which is on the bottom of the printer. You can also find it by clicking on the "About my Zim" button in the main menu.
				<form method="post"
					accept-charset="utf-8">
					<input type="text" name="serial" id="serial">
					<div class="zim-error">{err_msg}</div>
					<br/>
					<input type="submit" value="Ok" />
				</form>
				<a href="/" data-role="button" class="ui-btn ui-shadow ui-corner-all">Home</a>
			</div>
		</div>
	</div>

	<script type="text/javascript">
var var_ajax;
var var_refreshCheckTemper = 0;
var var_ajax_lock = false;
var var_checked_rfid = false;
var var_verifyRFIDInterval = 0;
var var_allow = ('{allow}' == 'yes');

function confirmDialog(callback) {
    var popupDialogObj = $('#activate');
    popupDialogObj.trigger('create');
    popupDialogObj.popup({
        afterclose: function (event, ui) {
            popupDialogObj.find(".optionConfirm").first().off('click');
            var isConfirmed = popupDialogObj.attr('data-confirmed') === 'yes' ? true : false;
            $(event.target).remove();
            if (isConfirmed && callback) {
                callback();
            }
        }
    });
    popupDialogObj.popup('open');
    popupDialogObj.find(".optionConfirm").first().on('click', function () {
        popupDialogObj.attr('data-confirmed', 'yes');
    });
}

// $("form#fileupload_v, form#fileupload_n").submit(verifyRfid(event));


/* 
function checkTemper() {
//	refreshCheckTemper();
	$("#temper_collaspible").bind('expand', function () {
		if (var_refreshCheckTemper == 0) {
//			checkTemper();
			var_refreshCheckTemper = setInterval(refreshCheckTemper, 10000);
		}
	}).bind('collapse', function() {
		clearInterval(var_refreshCheckTemper);
		var_refreshCheckTemper = 0;
	});
}
 */
function checkTemper() {
//		refreshCheckTemper();
		$("#temper_collaspible").collapsible({
			expand: function(event, ui) {
				if (var_refreshCheckTemper == 0) {
					refreshCheckTemper();
					var_refreshCheckTemper = setInterval(refreshCheckTemper, 10000);
				}
			},
			collapse: function(event, ui) {
				if (var_refreshCheckTemper != 0) {
					clearInterval(var_refreshCheckTemper);
					var_refreshCheckTemper = 0;
				}
			},
		});
	}

function refreshCheckTemper() {
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}
	
	var_ajax = $.ajax({
		url: "/advanceduser/temper_ajax",
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		var response = JSON.parse(html);
		$("#temper_l_current").val(response.left);
		$("#temper_r_current").val(response.right);
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});
}

function home(var_axis) {
	var var_url;
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	var_axis = typeof var_axis !== 'undefined' ? var_axis : 'all';
	if (var_axis == 'all') {
		var_url = "/advanceduser/home";
	}
	else {
		var_url = "/advanceduser/home/" + var_axis;
	}
	var_ajax = $.ajax({
		url: var_url,
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html('OK');
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function move(var_axis, var_value) {
	var var_url;
	var var_speed;
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	var_axis = typeof var_axis !== 'undefined' ? var_axis : 'error';
	if (var_axis == 'error') {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	else {
		if (var_axis == 'Z') {
			var_speed = $("#z_speed").val();
		}
		else {
			var_speed = $("#xy_speed").val();
		}
		var_url = "/advanceduser/move";
	}
	var_ajax = $.ajax({
		url: var_url,
		type: "GET",
		cache: false,
		data: {
				'axis' : var_axis,
				'value' : var_value,
				'speed' : var_speed
		},
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html('OK');
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function level(var_point) {
	var var_url;
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	var_point = typeof var_point !== 'undefined' ? var_point : 'error';
	if (var_point == 'error') {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	else {
		var_url = "/advanceduser/level/" + var_point;
	}
	var_ajax = $.ajax({
		url: var_url,
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html('OK');
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function heat(var_extruder, var_value) {
	var var_url;
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	var_extruder = typeof var_extruder !== 'undefined' ? var_extruder : 'error';
	var_value = typeof var_value !== 'undefined' ? var_value : 'get';

	if (var_extruder == 'error') {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	if (var_value == 'get') {
		if (var_extruder == 'l') {
			var_value = $("#temper_l").val();
		}
		else {
			var_value = $("#temper_r").val();
		}
	}

	var_url = "/advanceduser/heat/" + var_extruder + '/' + var_value;
	var_ajax = $.ajax({
		url: var_url,
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html('OK');
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function extrude(var_extruder, var_direction) {
	var var_url, var_value, var_speed;
	var id_extrude, id_speed;
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	var_extruder = typeof var_extruder !== 'undefined' ? var_extruder : 'error';
	var_direction = typeof var_direction !== 'undefined' ? var_direction : 'error';

	if (var_extruder == 'error' || var_direction == 'error') {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	if (var_extruder == 'l') {
		id_extrude = "#extrude_l";
		id_speed = "#speed_l";
	} else if (var_extruder == 'r') {
		id_extrude = "#extrude_r";
		id_speed = "#speed_r";
	} else {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	if (var_direction == '+') {
		var_value = $(id_extrude).val();
	} else if (var_direction == '-') {
		var_value = -parseFloat($(id_extrude).val());
	} else {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	var_speed = $(id_speed).val();

	var_url = "/advanceduser/extrude/" + var_extruder + '/' + var_value + '/' + var_speed;
	var_ajax = $.ajax({
		url: var_url,
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html('OK');
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function refreshRfid() {
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}
	
	var_ajax = $.ajax({
		url: "/advanceduser/rfid_ajax",
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		var response = JSON.parse(html);
		$("#rfid_l_current").val(response.left);
		$("#rfid_r_current").val(response.right);
		var_checked_rfid = true;
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function verifyRfid() {
	if (var_checked_rfid == false) {
// 		var var_option = confirm("Haven't launched checking RFID detected, check it now before start printing?");
// 		if (var_option == true) {
			refreshRfid();
			return true;
// 		}
// 		else {
// 			alert("You have cancelled printing because of RFID");
// 			return false;
// 		}
	}
	else {
		return true;
	}
}

function runGcodeGet() {

	$.post("https://stat.service.zeepro.com/log.ashx", {printersn: "{serial}", version: "1", category: "send", action: "gcode"});

	var var_gcode = $('#oneline_v').val();
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	if (var_gcode.indexOf('M109') != -1
			|| var_gcode.indexOf('M1606') != -1
			|| var_gcode.indexOf('M1607') != -1) {
		alert("M109 / M1606 / M1607 include!");
		return false;
	}
	var_ajax = $.ajax({
		url: "/rest/gcode",
		type: "GET",
		data: {
			v: var_gcode,
		},
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html(html);
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function runGcodePOST(id_code, var_mode) {
	var var_gcode;
	var var_verify;

	id_code = typeof id_code !== 'undefined' ? id_code : 'error';
	var_mode = typeof var_mode !== 'undefined' ? var_mode : 'normal';
	if (id_code == 'error') {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	
	var_verify = verifyRfid();
	if (var_verify == false) {
		return;
	}
	
	var_gcode = $(id_code).val();
	
	var_verifyRFIDInterval = setInterval(function(var_gcode, var_mode) {
		if (var_ajax_lock == false) {
			spinnerStart();
			postGcodeAjax(var_gcode, var_mode);
			clearInterval(var_verifyRFIDInterval);
		}
	}, 100);
	
	function postGcodeAjax(var_gcode, var_mode) {
		if (var_ajax_lock == true) {
			return;
		}
		else {
			var_ajax_lock = true;
		}
		
		var_ajax = $.ajax({
			url: "/rest/gcode",
			type: "POST",
			data: {
				v:		var_gcode,
				mode:	var_mode,
			},
			cache: false,
			beforeSend: function() {
				$("#overlay").addClass("gray-overlay");
				$(".ui-loader").css("display", "block");
			},
			complete: function() {
				$("#overlay").removeClass("gray-overlay");
				$(".ui-loader").css("display", "none");
			},
		})
		.done(function(html) {
			$("#gcode_detail_info").html('OK');
		})
		.fail(function() {
			$("#gcode_detail_info").html('ERROR');
		})
		.always(function() {
			var_ajax_lock = false;
		});
	}
}

function spinnerStart() {
	$("#overlay").addClass("gray-overlay");
	$(".ui-loader").css("display", "block");
}

function startSubmit(formid) {
	var var_verify;

	spinnerStart();
	
	$.post("https://stat.service.zeepro.com/log.ashx", {printersn: "{serial}", version: "1", category: "send", action: "gcodefile"});

	var_verify = verifyRfid();
	if (var_verify == false) {
		$("#overlay").removeClass("gray-overlay");
		$(".ui-loader").css("display", "none");
		return false;
	}
	else {
		var_verifyRFIDInterval = setInterval(function() {
			if (var_ajax_lock == false) {
				spinnerStart();
				$(formid).submit();
				clearInterval(var_verifyRFIDInterval);
			}
		}, 100);
		return true;
	}
}

</script>

</div>
