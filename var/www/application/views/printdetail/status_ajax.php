					<p>{in_finish}</p>
					<div id="print_detail_info_temper_l">{print_temperL}<br></div><br>
					<div id="print_detail_info_temper_r">{print_temperR}<br></div><br>
 					<p>{percent_title}</p>
					<div id="print_progress"></div>
					<p>{print_remain}</p>
					<p>{print_passed}</p>

<script>
var value_percent = {value_percent};

$('<input>').appendTo("div#print_progress")
.attr({'id':'percentage_slider','data-highlight':'true','value':value_percent, 'type':'range', 'min':'0','max':'100'}).slider(
{
	create: function (e, ui)
	{
		$(this).parent().find('input').hide();
		$(this).parent().find('input').css('margin-left','-9999px'); // Fix for some FF versions
		$(this).parent().find('.ui-slider-track').css('margin-left','0px');
		$(this).parent().find('.ui-slider-track').css('margin-right','0px');
		$(this).parent().find('.ui-slider-handle').hide();
	}
});

var_temper_holder = {hold_temper};
if (var_temper_holder == false) {
	var_temper_l = {value_temperL};
	var_temper_r = {value_temperR};
}
else {
	$("span#print_detail_info_temper_l_value").html(var_temper_l);
	$("span#print_detail_info_temper_r_value").html(var_temper_r);
}

if (var_temper_l !== null) {
	$("div#print_detail_info_temper_l").show();
	$('<input>').appendTo('#print_detail_info_temper_l')
	.attr({'name':'slider','id':'sliderL','data-highlight':'true','min':'0','max':'260','value':var_temper_l,'type':'range'})
	.slider({
		create: function( event, ui ) {
			$(this).parent().find('input').hide();
			$(this).parent().find('input').css('margin-left','-9999px'); // Fix for some FF versions
			$(this).parent().find('.ui-slider-track').css('margin-left','0px');
			$(this).parent().find('.ui-slider-track').css('margin-right','0px');
			$(this).parent().find('.ui-slider-handle').hide();
		}
	});
}
else {
	$("div#print_detail_info_temper_l").hide();
}

if (var_temper_r !== null) {
	$("div#print_detail_info_temper_r").show();
	$('<input>').appendTo('#print_detail_info_temper_r')
	.attr({'name':'slider','id':'sliderR','data-highlight':'true','min':'0','max':'260','value':var_temper_r,'type':'range'})
	.slider({
		create: function( event, ui ) {
			$(this).parent().find('input').hide();
			$(this).parent().find('input').css('margin-left','-9999px'); // Fix for some FF versions
			$(this).parent().find('.ui-slider-track').css('margin-left','0px');
			$(this).parent().find('.ui-slider-track').css('margin-right','0px');
			$(this).parent().find('.ui-slider-handle').hide();
		}
	});
}
else {
	$("div#print_detail_info_temper_r").hide();
}

refreshVideoURL({reload_player});

</script>
