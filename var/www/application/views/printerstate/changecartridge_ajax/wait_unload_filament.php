<button id="unload_button" onclick='javascript: inputUserChoice("unload");'>{unload_button}</button>
<br />
<button id="prime_button" onclick='prime()'>{prime_button}</button>

<script type="text/javascript">
var_next_phase = '{next_phase}';
// var_enable_unload = {enable_unload};

// $("a#back_button").css("display", "block");
$("a#home_button").css("display", "block");

function prime() {
	$("#overlay").addClass("gray-overlay");
	$(".ui-loader").css("display", "block");
	window.location.href="/printdetail/printprime?v={abb_cartridge}&cb={id_model}";
}

$('#cartridge_detail_info').trigger("create");

// if (var_enable_unload == true)
// {
// // 	$('#unload_button').button("enable");
// 	$('#unload_button').attr('enable', 'enable');
// }
// else {
// // 	$('#unload_button').button().disable();
// 	$('#unload_button').attr('disabled', 'disabled');

// 	var check_temp_interval = setInterval(function ()
// 	{
// 		$.ajax
// 		({
// 			url:"/printerstate/changecartridge_temper?v={abb_cartridge}",
// 			type: "GET",
// 			cache: false,
// 			statusCode:	{
// 				200: function (response) {
// 					$('#unload_button').removeAttr('enabled');
// 					$('#unload_button').attr('disabled', 'disabled');
// 				},
// 				202: function (response) {
// 					$('#unload_button').removeAttr('disabled');
// 					$('#unload_button').attr('enable', 'enable');
// 					clearInterval(check_temp_interval);
// 				},
// 				403: function (response) {
// 					window.location.href = "/error";
// 				},
// 				500: function (response) {
// 					window.location.href = "/error";
// 				}
// 			}
// 		})
// 	}, 5000);
// }
</script>
