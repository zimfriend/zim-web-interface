<div data-role="page" id="home" data-url="/printerstate/changecartridge">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a id="back_button" href="javascript:history.back();" data-icon="back" data-ajax="false" style="display:none">{back}</a>
		<a id="home_button" href="/" data-icon="home" data-ajax="false" style="display:none">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2 style="text-align: center;">{title}</h2>
			<div id="cartridge_detail_info" style="text-align: center;">
				<p>{wait_info}</p>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var var_refreshChangeStatus;
var var_ajax;
var var_ajax_lock = false;
var var_next_phase = '{first_status}';
var var_auto_prime = -1;
$(document).ready(checkChangeStatus());

function checkChangeStatus() {
	refreshChangeStatus();
	var_refreshChangeStatus = setInterval(refreshChangeStatus, 5000);
	function refreshChangeStatus() {
		if (var_ajax_lock == true) {
			return;
		}
		else {
			var_ajax_lock = true;
		}
		
		var_ajax = $.ajax({
			url: "/printerstate/changecartridge_ajax",
			type: "POST",
			data: {
				abb_cartridge: "{abb_cartridge}",
				need_filament: "{need_filament}",
				mid: "{id_model}",
				next_phase: var_next_phase,
				},
			cache: false,
		})
		.done(function(html) {
			if (var_ajax.status == 202) { // finished checking, wait user to input
				clearInterval(var_refreshChangeStatus);
				$("#cartridge_detail_info").html(html);
			}
			else if (var_ajax.status == 200) { // in checking
				$("#cartridge_detail_info").html(html);
			}
		})
		.fail(function() { // not allowed
			window.location.replace("/");
// 			clearInterval(var_refreshChangeStatus);
// 			$("#print_detail_info").html('<p>{finish_info}</p>');
// 			$('button#print_action').click(function(){window.location.href='/'; return false;});
// 			$('button#print_action').parent().find('span.ui-btn-text').text('{return_button}');
// 			$('button#print_action').html('{return_button}');
// 			alert("failed");
		})
		.always(function() {
			var_ajax_lock = false;
		});
	}
}

function inputUserChoice(action, flag) {
	var var_action = null;

	switch (action) {
		case 'load':
			var_action = $.ajax({
				url: "/printerstate/changecartridge_action/load",
				type: "GET",
				data: {v: "{abb_cartridge}"},
				cache: false,
				beforeSend: function()
				{
					$("#overlay").addClass("gray-overlay");
					$(".ui-loader").css("display", "block");
				},
				complete: function()
				{	
					$("#overlay").removeClass("gray-overlay");
					$(".ui-loader").css("display", "none");
				},
			});
			break;

		case 'unload':
			var_action = $.ajax({
				url: "/printerstate/changecartridge_action/unload",
				type: "GET",
				data: {v: "{abb_cartridge}"},
				cache: false,
				beforeSend: function()
				{
					$("#overlay").addClass("gray-overlay");
					$(".ui-loader").css("display", "block");
				},
				complete: function()
				{	
					$("#overlay").removeClass("gray-overlay");
					$(".ui-loader").css("display", "none");
				},
			});
			break;

		case 'unload_r':
			var_action = $.ajax({
				url: "/printerstate/changecartridge_action/unload_r",
				type: "GET",
				data: {v: "{abb_cartridge}"},
				cache: false,
			});
			break;

		case 'cancel_unload':
			var_action = $.ajax({
				url: "/printerstate/changecartridge_action/cancel_unload",
				type: "GET",
				data: {v: "{abb_cartridge}"},
				cache: false,
				beforeSend: function()
				{
					$("#overlay").addClass("gray-overlay");
					$(".ui-loader").css("display", "block");
				},
				complete: function()
				{	
					$("#overlay").removeClass("gray-overlay");
					$(".ui-loader").css("display", "none");
				},
			});
			break;

		case 'change':
			var_next_phase = '{insert_status}';
			checkChangeStatus();
			break;

		case 'write':
			if (flag == true) {
				var_action = $.ajax({
				url: "/printerstate/changecartridge_action/write",
				type: "GET",
				data: {
					c: $("#showPaletteOnly").val(),
					t: $("#temper_input").val(),
					l: $("#length_input").val(),
					m: $("#material_input").val(),
					v: "{abb_cartridge}"
					},
				cache: false,
				beforeSend: function()
				{
					$("#overlay").addClass("gray-overlay");
					$(".ui-loader").css("display", "block");
				},
				complete: function()
				{	
					$("#overlay").removeClass("gray-overlay");
					$(".ui-loader").css("display", "none");
				},
			});
			}
			else {
				$('#showPaletteOnly').spectrum('destroy');
				checkChangeStatus();
			}
			break;

		default:
// 			alert("unknown action");
			return false;
			break;
	}

	if (var_action) {
		var_action.done(function() {
			if ($('#showPaletteOnly').length > 0) {
				$('#showPaletteOnly').spectrum('destroy');
			}
			if (action == 'cancel_unload') {
				window.location.replace("/");
			}
// 			alert("done choice");
			checkChangeStatus();
		}).fail(function() {
// 			alert("failed choice");
		});
	}

	return false;
}

</script>
