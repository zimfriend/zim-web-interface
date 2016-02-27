				<p>{unload_info}</p>

<script type="text/javascript">
var_next_phase = '{next_phase}';
var var_in_heating = {in_heating};

if (var_in_heating == true) {
	$('#cartridge_detail_info').append('<span style="text-align: center;">{hint_temper}</span>');
	$('<input>').appendTo('#cartridge_detail_info').attr({'name':'slider','id':'sliderT','data-highlight':'true','min':'0','max':'260','value':'{value_temper}','type':'range'}).slider({
		create: function( event, ui ) {
			$(this).parent().find('input').hide();
			$(this).parent().find('input').css('margin-left','-9999px'); // Fix for some FF versions
			$(this).parent().find('.ui-slider-track').css('margin-left','0px');
			$(this).parent().find('.ui-slider-track').css('margin-right','0px');
			$(this).parent().find('.ui-slider-handle').hide();
		}
	});
	$('<div>').appendTo('#cartridge_detail_info')
	.attr({'id': 'cancel_button', 'onclick': 'javascript: inputUserChoice("cancel_unload");'}).html('{cancel_button}')
	.button().button('refresh');
}

$("a#home_button").css("display", "none");

</script>
