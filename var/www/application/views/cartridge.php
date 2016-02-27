<div data-role="page" id="home">
	<script type="text/javascript" src="/scripts/spectrum.js"></script>
	<link rel="stylesheet" type="text/css" href="/styles/spectrum.css" />
	<link rel="stylesheet" type="text/css" href="/assets/jquery-mobile-fluid960.min.css" />
	
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a id="back_button" href="javascript:history.back();" data-icon="back" data-ajax="false" style="display:none">{back}</a>
		<a id="home_button" href="/" data-icon="home" data-ajax="false" style="display:none">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2 style="text-align: center;">{side_cartridge}</h2>
			
			<p>{cartridge_code_hint} <span id="code">{cartridge_code}</span></p>
			<table border="1">
				<tr>
					<td>{magic_number}</td>
					<td>{cartridge_type}</td>
					<td>{material_type}</td>
					<td>{red_label}</td>
					<td>{green_label}</td>
					<td>{blue_label}</td>
					<td>{initial_length_mm}</td>
					<td>{used_length_mm}</td>
					<td>{temperature}</td>
					<td>{temperature_first}</td>
					<td>{pack_label}</td>
					<td>{checksum_label}</td>
				</tr>
				<tr>
					<td id="case_1">XXXX</td>
					<td id="case_2">X</td>
					<td id="case_21">X</td>
					<td id="case_3">XX</td>
					<td id="case_4">XX</td>
					<td id="case_5">XX</td>
					<td id="case_6">XXXXX</td>
					<td id="case_7">XXXXX</td>
					<td id="case_8">XX</td>
					<td id="case_9">XX</td>
					<td id="case_10">XXXX</td>
					<td id="case_11">XX</td>
				</tr>
				<tr>
					<td colspan="6"></td>
					<td id="t_case_6">XXXXX</td>
					<td id="t_case_7">XXXXX</td>
					<td id="t_case_8">XX</td>
					<td id="t_case_9">XX</td>
					<td id="t_case_10">XXXX</td>
					<td></td>
				</tr>
			</table>
			<br><br>
			
			<div class="container_16">
				<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 2em;">
					<label for="showPaletteOnly">{color}</label>
				</div></div>
				<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 2em;">
					<input name="c" id="showPaletteOnly" value="{rfid_color}" data-role="none" />
				</div></div>
				<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
					<label for="material_input">{cartridge_type}</label>
				</div></div>
				<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
					<select name="ct" id="cartridge_input">
						{cartridge_array}
							<option value="{value}" {on}>{name}</option>
						{/cartridge_array}
					</select>
				</div></div>
				<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
					<label for="material_input">{material_type}</label>
				</div></div>
				<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
					<select name="m" id="material_input">
						{material_array}
							<option value="{value}" {on}>{name}</option>
						{/material_array}
					</select>
				</div></div>
				<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
					<label for="temper_input">{temperature}</label>
				</div></div>
				<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
					<input type='range' name="t" id="temper_input" value="{temper_value}" min="160" max="260" />
				</div></div>
				<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
					<label for="temper_first_input">{temperature_first}</label>
				</div></div>
				<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
					<input type='range' name="tf" id="temper_first_input" value="{temper_f_value}" min="160" max="260" />
				</div></div>
				<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
					<label for="length_input">{initial_length}</label>
				</div></div>
				<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
					<input type='range' name="l" id="length_input" value="{initial_length_value}" min="10" max="200" />
				</div></div>
				<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
					<label for="length_used_input">{used_length}</label>
				</div></div>
				<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
					<input type='range' name="lu" id="length_used_input" value="{used_length_value}" min="0" max="200" />
				</div></div>
			</div>
			
			<button onclick="javascript: inputUserChoice(flag);">{write_button}</button>
			<div id="hint_message" class="zim-error">{error}</div>
		</div>
	</div>

<script type="text/javascript">
var_next_phase = '{next_phase}';
var flag = false;

var code = $("#code").html();

if (code.length == 32) {
	var var_date = '{pack_date_val}';
	$("#case_1").html(code.substr(0, 4));
	$("#case_2").html(code[4]);
	$("#case_21").html(code[5]);
	$("#case_3").html(code.substr(6, 2));
	$("#case_4").html(code.substr(8, 2));
	$("#case_5").html(code.substr(10, 2));
	$("#case_6").html(code.substr(12, 5));
	$("#case_7").html(code.substr(17, 5));
	$("#case_8").html(code.substr(22, 2));
	$("#case_9").html(code.substr(24, 2));
	$("#case_10").html(code.substr(26, 4));
	$("#case_11").html(code.substr(30, 2));
	
	$("#t_case_6").html({initial_length_value} * 1000);
	$("#t_case_7").html({used_length_value} * 1000);
	$("#t_case_8").html({temper_value});
	$("#t_case_9").html({temper_f_value});
	$("#t_case_10").html(var_date);
}

$("#showPaletteOnly").spectrum(
{
	showPaletteOnly: true,
	showPalette:true,
	preferredFormat:"name",
	palette:
	[
		['black', 'white', 'silver', 'cyan', 'orange', 'brown'],
		['red', 'yellow', 'blue', 'green', 'purple', 'pink']
	]
});

$("#home").trigger('create');

$("input#showPaletteOnly").on('change', function() { flag = true; });
$("input#temper_input").on('change', function() { flag = true; });
$("input#temper_first_input").on('change', function() { flag = true; });
$("input#length_input").on('change', function() { flag = true; });
$("input#length_used_input").on('change', function() { flag = true; });
$("select#material_input").on('change', function() { flag = true; });
$("select#cartridge_input").on('change', function() { flag = true; });

function inputUserChoice(flag) {
	if (flag == true) {
		var_action = $.ajax({
			url: "/cartridge/readnwrite_ajax",
			type: "GET",
			data: {
					c: $("#showPaletteOnly").val(),
					t: $("#temper_input").val(),
					l: $("#length_input").val(),
					m: $("#material_input").val(),
					v: "{abb_cartridge}",
					tf: $("#temper_first_input").val(),
					ct: $("#cartridge_input").val(),
					lu: $("#length_used_input").val(),
				},
			cache: false,
			beforeSend: function() {
				$("#hint_message").html('');
				$("#overlay").addClass("gray-overlay");
				$(".ui-loader").css("display", "block");
			},
// 			complete: function() {
// 				$("#overlay").removeClass("gray-overlay");
// 				$(".ui-loader").css("display", "none");
// 			},
		});
	}
	else {
		$("#hint_message").html("{info_not_changed}");
	}
	
	if (var_action) {
		var_action.done(function() {
			$("#hint_message").html("{writing_successed}");
			location.reload();
		}).fail(function() {
			$("#hint_message").html("{error_writing}");
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		});
	}

	return (false);
}

</script>

</div>