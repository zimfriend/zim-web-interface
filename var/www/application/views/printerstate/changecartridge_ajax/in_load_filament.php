				<p id="in_loading_hint">{load_info}</p>

<script type="text/javascript">
var_next_phase = '{next_phase}';

if (var_auto_prime == -1) {
	var_auto_prime = 1;
}
if (var_auto_prime == 1) {
	$('<div>').appendTo('#cartridge_detail_info')
	.attr({'id': 'cancel_button', 'onclick': 'javascript: cancel_auto_prime();'}).html('{cancel_button}')
	.button().button('refresh');
}
else if (var_auto_prime == 0) {
	$("p#in_loading_hint").html("{cancel_info}");
}

function cancel_auto_prime() {
	var_auto_prime = 0;
// 	$('div#cancel_button').button().button('destroy');
// 	$("#cancel_button").hide();
	$("#cancel_button").parent().hide();
	$("p#in_loading_hint").html("{cancel_info}");
}

$("a#home_button").css("display", "none");

</script>
