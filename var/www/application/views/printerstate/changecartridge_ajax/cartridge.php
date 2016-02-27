<script type="text/javascript" src="/scripts/spectrum.js"></script>
<link rel="stylesheet" type="text/css" href="/styles/spectrum.css" />
<link rel="stylesheet" type="text/css" href="/assets/jquery-mobile-fluid960.min.css" />

<div id="overlay"></div>
<div class="container_16">
	<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 2em;">
		<label for="showPaletteOnly">{color_label}</label>
	</div></div>
	<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 2em;">
		<input name="c" id="showPaletteOnly" value="{rfid_color}" data-role="none" />
	</div></div>
	<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
		<label for="material_input">{material_label}</label>
	</div></div>
	<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
		<select name="m" id="material_input">
			{material_array}
				<option value="{value}" {on}>{name}</option>
			{/material_array}
		</select>
	</div></div>
	<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
		<label for="temper_input">{temper_label}</label>
	</div></div>
	<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
		<input type='range' name="t" id="temper_input" value="{temper_value}" min="160" max="260" />
	</div></div>
	<div class="grid_5"><div class="ui-bar ui-bar-d" style="height: 3em;">
		<label for="length_input">{length_label}</label>
	</div></div>
	<div class="grid_11"><div class="ui-bar ui-bar-c" style="height: 3em;">
		<input type='range' name="l" id="length_input" value="{length_value}" min="{length_min}" max="200" />
	</div></div>
</div>

<button onclick="javascript: inputUserChoice('write', flag);">{write_button}</button>


<script type="text/javascript">
var_next_phase = '{next_phase}';
var flag = false;

$("#showPaletteOnly").spectrum(
{
	showPaletteOnly: true,
	showPalette:true,
	preferredFormat:"name",
	palette:
	[
		['black', 'white', 'silver', 'cyan', 'orange', 'brown'],
		['red', 'yellow', 'blue', 'green', 'purple', 'pink']
	]
});

$("#home").trigger('create');

$("input#showPaletteOnly").on('change', function()
{
	flag = true;
});
$("input#temper_input").on('change', function(){flag = true;});
$("input#length_input").on('change', function(){flag = true;});
$("select#material_input").on('change', function(){flag = true;});

</script>