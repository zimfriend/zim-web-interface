<div data-role="page" data-url="/test_endstop">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="#" data-icon="back" data-ajax="false" style="visibility:hidden">{back}</a>
		<a href="#" onclick="javascript:window.location.href='/';" data-icon="home" data-ajax="false" style="float:right">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="background-color:white;">
			<div style="display:{error}">
				<span class="zim-error">Couldn't get endstop information</span>
			</div>
			<div>
				<div style="text-align:center;"><b>X axis</b></div>
				<div class="ui-grid-a" style="text-align:center;">
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f">
							<label for="slider"><h2>Left (X-)</h2></label>
						</div>
					</div>
					<div class="ui-block-b">
						<div class="ui-bar ui-bar-f">
							<label for="slider"><h2>Right (X+)</h2></label>
						</div>
					</div>
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f">
							<input readonly id="xmin" value="{xleft}" />
						</div>
					</div>
					<div class="ui-block-b">
						<div class="ui-bar ui-bar-f">
							<input readonly id="xmax" value="{xright}" />
						</div>
					</div>
				</div>
			</div>
			<br />
			<br />
			<div>
				<div style="text-align:center;"><b>Y axis</b></div>
				<div class="ui-grid-a" style="text-align:center;">
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f" style="height:3em;">
							<label for="slider"><h2>Back (Y+)</h2></label>
						</div>
					</div>
					<div class="ui-block-b">
					<div class="ui-bar ui-bar-f">
							<input readonly id="ymax" value="{yback}" />
					</div>
					</div>
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f" style="height:3em;">
							<label for="slider"><h2>Front (Y-)</h2></label>
						</div>
					</div>
					<div class="ui-block-b">
						<div class="ui-bar ui-bar-f">
							<input readonly id="ymin" value="{yfront}" />
						</div>
					</div>
				</div>
			</div>
			<br />
			<br />
			<div>
				<div style="text-align:center;"><b>Z axis</b></div>
				<div class="ui-grid-a" style="text-align:center;">
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f" style="height:3em;">
							<label for="slider"><h2>Top (Z-)</h2></label>
						</div>
					</div>
					<div class="ui-block-b">
						<div class="ui-bar ui-bar-f">
							<input readonly id="zmin" value="{ztop}" />
						</div>
					</div>
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f" style="height:3em;">
							<label for="slider"><h2>Bottom (Z+)</h2></label>
						</div>
					</div>
					<div class="ui-block-b">
						<div class="ui-bar ui-bar-f">
							<input readonly id="zmax" value="{zbottom}" />
						</div>
					</div>
				</div>
			</div>
			<br />
			<br />
			<div style="text-align:center;"><b>Cartridge holder</b></div>
			<div class="widget_monocolor" style="display: none;">
				<input readonly id="E_mono" value="{monocart}" />
			</div>
			<div class="ui-grid-a widget_bicolor" style="text-align: center; display: none;">
				<div class="ui-block-a">
					<b>Left</b>
				</div>
				<div class="ui-block-b">
					<b>Right</b>
				</div>
				<div class="ui-block-a">
					<div class="ui-bar ui-bar-f">
						<input readonly id="E1" value="{leftcart}" />
					</div>
				</div>
				<div class="ui-block-b">
					<div class="ui-bar ui-bar-f">
						<input readonly id="E0" value="{rightcart}" />
					</div>
				</div>
			</div>
		</div>
	</div>

<script>
var var_bicolor = {bicolor};

if (var_bicolor == true) {
	$(".widget_bicolor").show();
}
else {
	$(".widget_monocolor").show();
}

setInterval(function() {
	$.ajax({
		url:'/test_endstop/endstop_ajax'
	})
	.done(function(jsontab) {
		var endstop = JSON.parse(jsontab);
		
		$("#xmin").val(endstop["xmin"] ? "Pressed" : "Not pressed");
		$("#xmax").val(endstop["xmax"] ? "Pressed" : "Not pressed");
		$("#ymin").val(endstop["ymin"] ? "Pressed" : "Not pressed");
		$("#ymax").val(endstop["ymax"] ? "Pressed" : "Not pressed");
		$("#zmin").val(endstop["zmin"] ? "Pressed" : "Not pressed");
		$("#zmax").val(endstop["zmax"] ? "Pressed" : "Not pressed");
		if (var_bicolor == true) {
			$("#E0").val(endstop["E0"] ? "Filament" : "No filament");
			$("#E1").val(endstop["E1"] ? "Filament" : "No filament");
		}
		else {
			$("#E_mono").val(endstop["E0"] ? "Filament" : "No filament");
		}
	});
}, 1000);
</script>
</div>