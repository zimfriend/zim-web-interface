<div data-role="page" data-url="/printdetail/recovery">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<h2>{title}</h2>
			<div id="recovery_detail_info">
				<p>{wait_info}</p>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var var_refreshRecoveryStatus;
var var_ajax;
var var_ajax_lock = false;
$(document).ready(checkRecoveryStatus());

function checkRecoveryStatus() {
	refreshRecoveryStatus();
	var_refreshRecoveryStatus = setInterval(refreshRecoveryStatus, 5000);
	
	function refreshRecoveryStatus() {
		if (var_ajax_lock == true) {
			return;
		}
		else {
			var_ajax_lock = true;
		}
		var_ajax = $.ajax({
			url: "/printdetail/recovery_ajax",
			cache: false,
		})
		.done(function(html) {
			if (var_ajax.status == 202) { // finished printing
				finishAction();
			}
			else if (var_ajax.status == 200) { // in printing
				$("#recovery_detail_info").html(html);
			}
		})
		.fail(function() { // not in printing
// 			window.location.replace("/");
			finishAction();
<!--	//	<?php //FIXME just disable redirection and do same as finished for simulation ?> -->
		})
		.always(function() {
			var_ajax_lock = false;
		});
	}

	return;
}


function finishAction() {
	clearInterval(var_refreshRecoveryStatus);
	// display info
	$("#recovery_detail_info").html('<p>{finish_info}</p>');
	// add return button for Home
	$('<button>').appendTo('#container')
	.attr({'id': 'print_action', 'onclick': 'javascript: window.location.href="{return_url}";'}).html('{return_button}')
	.button().button('refresh');

	return;
}
</script>

