<div data-role="page" data-url="/printerstate/offset_setting">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{nozzles_title}</h2>
			<p>{nozzles_intro}</p>
			<br />
			<form action="/printerstate/offset_setting" method="POST"  data-ajax="false">
				<div id="col1" class="hideIcon">
					<h3>{collapsible_1}</h3>
					<div class="ui-grid-c" >
						<div class="ui-block-a" style="width:20%">
							<input type="radio" id="radio-1" name="side1" style="margin:0" checked />
						</div>
						<div class="ui-block-b" style="width:40%">
							<img src="/images/top.png" />
						</div>
						<div class="ui-block-c">
							<input type="number" name="top_offset" value="0.0" step="0.1" min="0" />
						</div>
						<div class="ui-block-d" style="width:5%;padding-top:1em;margin-left:2px">
							mm
						</div>
						<div class="ui-block-a" style="width:20%">
							<input type="radio" id="radio-2" name="side1" style="margin:0" />
						</div>
						<div class="ui-block-b" style="width:40%">
							<img src="/images/bot.png" />
						</div>
						<div class="ui-block-c">
							<input type="number" name="bot_offset" value="0.0" step="0.1" min="0" />
						</div>
						<div class="ui-block-d" style="width:5%;padding-top:1em;margin-left:2px">
							mm
						</div>
					</div>
				</div>
				<div id="col2">
					<h3>{collapsible_2}</h3>
					<div class="ui-grid-c">
						<div class="ui-block-a" style="width:20%">
							<input type="radio" id="radio-3" name="side2" style="margin:0" checked />
						</div>
						<div class="ui-block-b" style="width:40%">
							<img src="/images/right_side.png" />
						</div>
						<div class="ui-block-c">
							<input type="number" name="right_offset" value="0.0" step="0.1" min="0"/>
						</div>
							<div class="ui-block-d" style="width:5%;padding-top:1em;margin-left:2px">
							mm
						</div>
						<div class="ui-block-a" style="width:20%">
							<input type="radio" id="radio-4" name="side2" style="margin:0" />
						</div>
						<div class="ui-block-b" style="width:40%">
							<img src="/images/left.png" />
						</div>
						<div class="ui-block-c">
							<input type="number" name="left_offset" value="0.0" step="0.1" min="0" />
						</div>
						<div class="ui-block-d" style="width:5%;padding-top:1em;margin-left:2px">
							mm
						</div>
					</div>
				</div>
				<input type="submit" value="Ok" />
			</form>
			<div class="zim-error">{error}</div>
		</div>
		<script>
		$("#col1").collapsible({collapsed: false, disabled: true, iconpos: "none"});
		$("#col2").collapsible({collapsed: false, disabled: true, iconpos: "none"});

		$(document).ready(function()
		{
			$("input[name=bot_offset]").attr("disabled", "disabled");
			$("input[name=bot_offset]").css("background-color", "lightgray");
			$("input[name=left_offset]").attr("disabled", "disabled");
			$("input[name=left_offset]").css("background-color", "lightgray");
		});
		
		$('input[type=radio]').on('change', function()
		{
			var radio_id = $(this).attr("id");
			
			if (radio_id == "radio-1")
			{
				$("input[name=top_offset]").removeAttr("disabled");
				$("input[name=top_offset]").css("background-color", "white");

				$("input[name=bot_offset]").attr("disabled", "disabled");
				$("input[name=bot_offset]").css("background-color", "lightgray");
				$("input[name=bot_offset]").val("0.0");
			}
			else if (radio_id == "radio-2")
			{
				$("input[name=bot_offset]").removeAttr("disabled");
				$("input[name=bot_offset]").css("background-color", "white");

				$("input[name=top_offset]").attr("disabled", "disabled");
				$("input[name=top_offset]").css("background-color", "lightgray");
				$("input[name=top_offset]").val("0.0");
			}
			else if (radio_id == "radio-3")
			{
				$("input[name=right_offset]").removeAttr("disabled");
				$("input[name=right_offset]").css("background-color", "white");

				$("input[name=left_offset]").attr("disabled", "disabled");
				$("input[name=left_offset]").css("background-color", "lightgray");
				$("input[name=left_offset]").val("0.0");
			}
			else if (radio_id == "radio-4")
			{
				$("input[name=left_offset]").removeAttr("disabled");
				$("input[name=left_offset]").css("background-color", "white");

				$("input[name=right_offset]").attr("disabled", "disabled");
				$("input[name=right_offset]").css("background-color", "lightgray");
				$("input[name=right_offset]").val("0.0");
			}
		});
		$("input[name=top_offset]").on("change", function()
		{
			$("input[name=bot_offset]").val("0.0");
		});
		$("input[name=bot_offset]").on("change", function()
		{
			$("input[name=top_offset]").val("0.0");
		});

		$("input[name=right_offset]").on("change", function()
		{
			$("input[name=left_offset]").val("0.0");
		});

		$("input[name=left_offset]").on("change", function()
		{
			$("input[name=right_offset]").val("0.0");
		});
				
	</script>
	</div>
</div>