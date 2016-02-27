<div data-role="page" data-url="/printdetail/cancel">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<div data-role="collapsible" data-collapsed="false" style="align: center;">
				<h4>{title}</h4>
<!--				<video width="320" height="240" autoplay controls>
					<source src="http://88.175.62.75/zim.m3u8" type="application/x-mpegURL" />
					Your browser does not support HTML5 streaming!
				</video> -->
				<script type="text/javascript" src="/assets/jwplayer/jwplayer.js"></script>
	 			<script type="text/javascript">jwplayer.key="Jh6aqwb1m2vKLCoBtS7BJxRWHnF/Qs3LMjnt13P9D6A=";</script>
	 			<style type="text/css">div#myVideo_wrapper {margin: 0 auto;}</style>
				<div id="myVideo">{loading_player}</div>
				<script type="text/javascript">
					jwplayer("myVideo").setup({
						file: "{video_url}",
						autostart: true,
					});
				</script>
				<div id="cancel_detail_info">
					<p>{wait_info}</p>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var var_refreshCancelStatus;
var var_refreshVideoURL;
var var_ajax;
var var_ajax_lock = false;

function load_jwplayer_video() {
	var player = jwplayer("myVideo").setup({
		file: "{video_url}",
		width: "100%",
		autostart: true,
		fallback: false,
		androidhls: true
	});
	player.onSetupError(function() {
		$("#myVideo").empty().append('<img src="/images/error.png" height="280" width="280" />' + "<p>{video_error}</p>");
	});
}

function checkCancelStatus() {
	refreshCancelStatus();
	var_refreshCancelStatus = setInterval(refreshCancelStatus, 5000);
	refreshVideoURL();
	function refreshCancelStatus() {
		if (var_ajax_lock == true) {
			return;
		}
		else {
			var_ajax_lock = true;
			refreshVideoURL();
		}
		var_ajax = $.ajax({
			url: "/printdetail/cancel_ajax",
			cache: false,
		})
		.done(function(html) {
			if (var_ajax.status == 202) { // finished printing
				finishAction();
			}
			else if (var_ajax.status == 200) { // in printing
				$("#cancel_detail_info").html(html);
			}
		})
		.fail(function() { // not in printing
// 			window.location.replace("/");
			finishAction();
<?php //FIXME just disable redirection and do same as finished for simulation ?>
		})
		.always(function() {
			var_ajax_lock = false;
		});
	}

	return;
}

function refreshVideoURL() {
	var_refreshVideoURL = setInterval(refreshVideo, 1000 * 30 * 4);
	function refreshVideo() {
		jwplayer('myVideo').load({file:'{video_url}'});
	}

	return;
}

function again_in_cancel() {
	$("#overlay").addClass("gray-overlay");
	$(".ui-loader").css("display", "block");
	window.location.href="{restart_url}";
};

function finishAction() {
	clearInterval(var_refreshCancelStatus);
	// display info
	$("#cancel_detail_info").html('<p>{finish_info}</p>');
	// add return button for Home
	$('<div>').appendTo('#container')
	.attr({'id': 'print_action', 'onclick': 'javascript: window.location.href="{return_url}";'}).html('{return_button}')
	.button().button('refresh');
	$('<div>').appendTo('#container')
	.attr({'id': 'again_action', 'onclick': 'javascript: again_in_cancel();'}).html('{again_button}')
	.button().button('refresh');

	return;
}

$(document).ready(checkCancelStatus());
var var_video_check = setInterval(function() {
	var req = $.ajax({
		url: "{video_url}",
		type: "HEAD",
		success: function() {
			load_jwplayer_video();
			clearInterval(var_video_check);
		}
	});
}, 1000);
</script>
