<div data-role="page" data-url="/printerstoring/listgcode" style="overflow:hidden;">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<p>{list_info}</p>
		<div id="container">
			<div data-role="fieldcontain">
				<select name="select_sort" id="select_sort">
					<option value="alphabetical">{select_alphabetical}</option>
					<option value="mostrecent" selected="selected">{select_mostrecent}</option>
				</select>
			</div>
			<ul data-role="listview" id="listview-gcode" class="shadowBox" data-inset="true" data-filter="true" data-filter-placeholder="" data-filter-theme="d" data-split-icon="delete" data-split-theme="b"></ul>
<!-- 			<h2>{title}</h2> -->
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
		<div id="delete_popup" data-role="popup" data-dismissible="false" class="ui-content" style="max-width: 250px; text-align: center;">
			{delete_popup_text}
			<br /><br />
			<div class="ui-grid-a">
				<div class="ui-block-a">
					<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-rel="back" data-transition="flow" onclick="javascript: deletegcode();">{delete_yes}</a>
				</div>
				<div class="ui-block-b">
					<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-rel="back">{delete_no}</a>
				</div>
			</div>
		</div>
	</div>
	
<script type="text/javascript">
var var_id_delete = 0;
var modellist = {encoded_list};
modellist = $.map(modellist, function(value, index) {
	return [value];
});

function compare_date(a,b) {
	if (a.creation_date < b.creation_date)
		return 1;
	else if (a.creation_date > b.creation_date)
		return -1;
	else
		return 0;
}

function compare_name(a,b) {
	var comp_a = a.modelname.toLowerCase();
	var comp_b = b.modelname.toLowerCase();
	
	if (comp_a < comp_b)
		return -1;
	else if (comp_a > comp_b)
		return 1;
	else
		return 0;
}

function displaylist() {
	var listelement = $('#listview-gcode');
	listelement.empty();
	
	for(k in modellist) {
		var model = modellist[k];
		listelement.append('<li id="gcodemodel-' + model.mid + '"><a href="/printerstoring/gcodedetail?id=' + model.mid + '">'
				+ '<img src="/printerstoring/getpicture?type=gcode&id=' + model.mid + '" style="margin-top: 20px;"><h2>' + model.modelname + '</h2><p>' + model.creation_datestr + '</p>'
				+ '<p>{preset_name_title}' + model.presetname + '</p></a>'
				+ '<a href="#delete_popup" data-rel="popup" onclick=\'javascript: pre_deletegcode("' + model.mid + '");\'>Delete</a></li>');
	}
	listelement.listview("refresh");
}

$(document).ready(function() {
	modellist.sort(compare_date);
	displaylist();

	$('#select_sort').change(function () {
		console.log($(this).find(":selected").val());
		if ($(this).find(":selected").val() == 'alphabetical') {
			modellist.sort(compare_name);
			displaylist();
		}
		else if ($(this).find(":selected").val() == 'mostrecent') {
			modellist.sort(compare_date);
			displaylist();
		}
	}).change();
});

function pre_deletegcode(id) {
	if (typeof id === 'undefined') {
		console.log('undefined id');
		return;
	}
	var_id_delete = id;
	
	return;
}

function deletegcode(){
	if (var_id_delete <= 0) {
		console.log('invalid delete action');
		return;
	}
	
	$.ajax({
			cache: false,
			type: "GET",
			url: "/rest/libdeletegcode",
			data: { "id": var_id_delete },
			beforeSend: function() {
				$("#overlay").addClass("gray-overlay");
				$(".ui-loader").css("display", "block");
			},
			complete: function() {
				$(".ui-loader").css("display", "none");
				$("#overlay").removeClass("gray-overlay");
			},
//			dataType: "json",
			success: function (data, textStatus, xhr) {
				$('#gcodemodel-' + var_id_delete).remove();
			},
			error: function (data, textStatus, xhr) {
				alert('{delete_error}');
				console.log(xhr);
			},
	});
}

function load_wait() {
	$("#overlay").addClass("gray-overlay");
	$(".ui-loader").css("display", "block");
}

$(document).on("pagebeforehide", function() {
	$(".ui-loader").css("display", "none");
	$("#overlay").removeClass("gray-overlay");
});
</script>

</div>
