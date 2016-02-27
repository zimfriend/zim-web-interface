<div data-role="page" data-url="/sliceupload/slicestatus">
	<header data-role="header" class="page-header"></header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<div id="detail_zone" style="clear: both; text-align: center;">
			</div>
			<div id="cancel_div"><a id="cancel_slice_button" href="#" class="ui-btn" onclick="javascript: stopSlice();">{cancel_button}</a></div>
		</div>
	</div>
</div>

<script type="text/javascript">
var var_slice_status;
var var_slice_status_lock = false;
var var_slice_interval;
var var_slice_cancel = null;


$(document).ready(prepareDisplay());

function prepareDisplay() {
	checkSlice(); // launch checking directly
	var_slice_interval = setInterval(checkSlice, 3000);

	return;
}

function checkSlice() {
	if (var_slice_status_lock == true) {
		return;
	}
	else {
		var_slice_status_lock = true;
	}
	
	var_slice_status = $.ajax({
		url: "/sliceupload/slice_status_ajax",
		type: "GET",
		cache: false,
	})
	.done(function(html) {
		if (var_slice_status.status == 202) { // finished checking, wait user to input
			clearInterval(var_slice_interval);
			window.location.href='/sliceupload/slice?callback';
		}
		else if (var_slice_status.status == 200) { // in checking
			// html => percentage
			if (typeof html == 'string') {
				$("#detail_zone").html("{wait_in_slice} " + html + "{slice_suffix}");
			}
			else if (typeof html == 'object' && typeof html.percent != 'undefined') {
				// we ignore message not found case for this moment
// 				if (typeof html.message != 'undefined') {
				if ((html.percent == 0 || html.percent == 99) && typeof html.message != 'undefined') {
					// in remote slicing special case
					$("#detail_zone").html(html.message + "<br/>{wait_in_slice}");
				}
				else {
					// we do not display message for this moment (need to improve later)
					$("#detail_zone").html("{slice_percent_prefix} " + html.percent + "{slice_percent_suffix}<br/>{wait_in_slice}");
				}
				console.log(html);
			}
			else {
				console.log(html);
			}
			
		}
	})
	.fail(function() { // not allowed
		if (var_slice_cancel == null) {
// 			window.location.replace("/");
			clearInterval(var_slice_interval);
			$('a#cancel_slice_button').attr('onclick','').unbind('click').html('{return_button}');
			$('#detail_zone').html("{slice_failmsg}");
			$('a#cancel_slice_button').click(function() { window.location.replace("/"); return false; });
		}
//			clearInterval(var_refreshChangeStatus);
//			$("#print_detail_info").html('<p>{finish_info}</p>');
//			$('button#print_action').click(function(){window.location.href='/'; return false;});
//			$('button#print_action').parent().find('span.ui-btn-text').text('{return_button}');
//			$('button#print_action').html('{return_button}');
//			alert("failed");
	})
	.always(function() {
		var_slice_status_lock = false;
	});
	
	return;
}

function stopSlice() {
	var_slice_status_lock = true;
	clearInterval(var_slice_interval);
	
	var_slice_cancel = $.ajax({
		url: "/rest/cancelslicing",
		type: "GET",
		cache: false,
	})
	.done(function(html) {
// 		alert("End");
	})
	.always(function() {
		$("#cancel_div").hide();
		$("#detail_zone").html("{wait_cancel}");
		setTimeout(function() { window.location.replace("/"); }, 10000);
	});

	return;
}
</script>