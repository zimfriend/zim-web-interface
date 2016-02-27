<div data-role="page" data-url="/preset/listpreset" style="overflow-y: hidden;">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
<!-- 			<h2>{title}</h2> -->
			<form action="/preset/detail" method="get" data-ajax="false">
				<div data-role="fieldcontain">
<!-- 					<legend>Vertical controlgroup:</legend> -->
					<label for="new_preset_select">{new_preset_label}</label>
					<select name="id" id="new_preset_select">
						{newmodel_lists}
						<option value="{id}">{name}</option>
						{/newmodel_lists}
					</select>
				</div>
				<input type="hidden" name="new" id="new_preset_hidden">
				<div id="submit_container"><input type="submit" value="{submit_button}"></div>
			</form>
			<div style="height:50px;"></div>
			<div id="delete_popup" data-role="popup" data-dismissible="false" class="ui-content" style="max-width: 250px; text-align: center;">
				{delete_popup_text}
				<div class="ui-grid-a">
					<div class="ui-block-a">
						<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-transition="flow" onclick="javascript: delete_preset();">{delete_yes}</a>
					</div>
					<div class="ui-block-b">
						<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-rel="back">{delete_no}</a>
					</div>
				</div>
			</div>
			<ul data-role="listview" id="listview" class="shadowBox" data-inset="true" data-filter="true" data-filter-placeholder="{search_hint}" data-filter-theme="d" data-filter-theme="d" data-split-icon="delete" data-split-theme="b">
				{model_lists}
				<li>
					<a href="/preset/detail?id={id}"><h2>{name}</h2></a>
					<a id="preset_del_{id}" data-rel="popup" data-transition="pop" href="#delete_popup" onclick="javascript: confirm_delete_preset('{id}');">Delete</a>
				</li>
				{/model_lists}
			</ul>
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
	</div>

<script type="text/javascript">
var var_preset_id = 0;

function confirm_delete_preset(id) {
	if(typeof id === 'undefined') {
		return;
	}
	else {
		var_preset_id = id;
	}
	
	return;
}

function delete_preset() {
	window.location.href = "/preset/delete?id=" + var_preset_id;
	
	return;
}
</script>
</div>
