<div data-role="page" data-url="/debug">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
<!-- 			<h2>{title}</h2> -->
			
			<div class="ui-grid-a" style="margin-bottom: 1em;">
				<div class="ui-block-a"><div class="ui-bar ui-bar-d" style="height:2em;">{txt_level_current}</div></div>
				<div class="ui-block-b"><div class="ui-bar ui-bar-c" style="height:2em;">{level}</div></div>
			</div>
			<form action="/debug" method="POST">
			<label for="new_level_select">{txt_level_set}</label>
			<select name="level" id="new_level_select">
				{level_array}
				<option value="{value}">{name}</option>
				{/level_array}
			</select>
			<input type="submit" value="OK">
			</form>
		</div>
	</div>
</div>
