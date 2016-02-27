<div data-role="page" data-url="/sliceupload/restart">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="#" data-icon="back" data-ajax="false" style="visibility:hidden">{back}</a>
		<a href="#" onclick="javascript:window.location.href='/';" data-icon="home" data-ajax="false" style="float:right">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h3>{wait_msg}</h3>
		</div>
	</div>

<script type="text/javascript">
var var_ajax;
var var_ajax_lock = false;
var var_check_in_boot = {check_in_boot};
var var_refreshChecking;

$(document).ready(loopChecking());

function loopChecking() {
	if (var_check_in_boot == false) {
		checkOnline(true);
	}
	var_refreshChecking = setInterval(checkOnline, 3000);
}

function checkOnline(var_first) {
	var_first = typeof var_first !== 'undefined' ? var_first : false;
	
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}
	
	var_ajax = $.ajax({
		url: "/sliceupload/restart_ajax",
		cache: false,
		data: var_first ? {action: "restart"} : null,
		type: "GET",
	})
	.done(function(html) {
		if (var_ajax.status == 202) { // already open
			clearInterval(var_refreshChecking);
			window.location.replace("/sliceupload/upload");
		}
	})
	.fail(function() {
		window.location.replace("/");
	})
	.always(function() {
		var_ajax_lock = false;
	});
}
</script>

</div>