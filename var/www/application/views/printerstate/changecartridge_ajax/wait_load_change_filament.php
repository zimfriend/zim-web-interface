				<div id="cartridge_color_info"></div>

<script type="text/javascript">
var_next_phase = '{next_phase}';

/*
$.ajax({
	url: "/printerstate/changecartridge_action/detail",
	type: "GET",
	data: {
		v: "{abb_cartridge}",
		id: "{id_model}",
		},
	cache: false,
})
.done(function(html) {
	$("#cartridge_color_info").html(html);
})
.fail(function() { // not allowed
// 	window.location.replace("/");
	alert('failed');
});
*/

$('<div>').appendTo('#cartridge_detail_info')
.attr({'id': 'load_button', 'onclick': 'javascript: inputUserChoice("load");'}).html('{load_button}')
.button().button('refresh');
$('#cartridge_detail_info').append("<br />");
$('<div>').appendTo('#cartridge_detail_info')
.attr({'id': 'change_button', 'onclick': 'javascript: inputUserChoice("change");'}).html('{change_button}')
.button().button('refresh');
</script>
