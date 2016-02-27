<div data-role="page" data-url="/printerstoring/liststl" style="overflow:hidden;">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<p>{uploaded}</p>
		<p>{list_info}</p>
		<div id="container">
<!-- 			<h2>{title}</h2> -->
			<ul data-role="listview" id="listview" class="shadowBox" data-inset="true" data-filter="true" data-filter-placeholder="" data-filter-theme="d" data-split-icon="delete" data-split-theme="b">
				{list}
				<li id="stlmodel_{id}">
					<a href='#' id="printmodel-{id}" onclick="printmodel('{id}');">
						<img src="/printerstoring/getpicture?type=stl&id={id}" style="vertical-align:middle">
						<h2>{name}</h2><p>{creation_date}</p>
					</a>
					<a href='#delete_popup' data-rel="popup" id="deletemodel-{id}" onclick="javascript: pre_deletemodel('{id}');">{delete-model}</a>
				</li>
				{/list}
			</ul>
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
		<div id="delete_popup" data-role="popup" data-dismissible="false" class="ui-content" style="max-width: 250px; text-align: center;">
				{delete_popup_text}
				<br />
				<br />
				<div class="ui-grid-a">
					<div class="ui-block-a">
						<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-rel="back" data-transition="flow" onclick="javascript: deletemodel();">{delete_yes}</a>
					</div>
					<div class="ui-block-b">
						<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-rel="back">{delete_no}</a>
					</div>
				</div>
			</div>
	</div>
	
<script type="text/javascript">
var var_id_delete = 0;

function printmodel(id){

	$.ajax({
			cache: false,
			type: "GET",
			url: "/rest/libprintstl",
			data: { "id": id},
//			dataType: "json",
			beforeSend: function() {
				$("#overlay").addClass("gray-overlay");
				$(".ui-loader").css("display", "block");
			},
			complete: function() {
				$("#overlay").removeClass("gray-overlay");
				$(".ui-loader").css("display", "none");
			},
			success: function (data, textStatus, xhr) {
				window.location.href = "/sliceupload/slice";
			},
			error: function (data, textStatus, xhr) {
				alert("{print_error}");
				console.log(data);
			},
	});
}

function pre_deletemodel(id) {
	if (typeof id === 'undefined') {
		console.log('undefined id');
		return;
	}
	var_id_delete = id;
	
	return;
}

function deletemodel(){
	if (var_id_delete <= 0) {
		console.log('invalid delete action');
		return;
	}
	
	$.ajax({
			cache: false,
			type: "GET",
			url: "/rest/libdeletestl",
			data: { "id": var_id_delete},
//			dataType: "json",
			beforeSend: function() {
				$("#overlay").addClass("gray-overlay");
				$(".ui-loader").css("display", "block");
			},
			complete: function() {
				$("#overlay").removeClass("gray-overlay");
				$(".ui-loader").css("display", "none");
			},
			success: function (data, textStatus, xhr) {
				$('#stlmodel_' + var_id_delete).remove();
			},
			error: function (data, textStatus, xhr) {
				console.log(data);
				console.log(textStatus);
				console.log(xhr);
				alert('{delete_error}');
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
