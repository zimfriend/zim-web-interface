<div data-role="page" data-url="/sliceupload/upload" id="slicer_uploadModel">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<form action="/sliceupload/upload" method="post" enctype="multipart/form-data" data-ajax="false" id="uploadfile_model">
			<div class="widget_monocolor" style="display: none;">
				<h3>{header_single}</h3>
				<label for="file">{select_hint_multi}</label>
				<input type="file" data-clear-btn="true" name="file" id="file_upload_mono" />
			</div>
			<div id="set" data-role="collapsible-set" class="widget_bicolor" style="display: none;">
				<div id="upload_model_tab_s" data-role="collapsible" data-collapsed="false">
					<h3> {header_single} </h3>
					<label for="file">{select_hint}</label>
					<input type="file" data-clear-btn="true" name="file" id="file_upload_s" />
				</div>
				<div id="upload_model_tab_m" data-role="collapsible">
					<h3> {header_multi} </h3>
					<label for="file_c1">{select_hint_multi}</label>
					<input type="file" data-clear-btn="true" name="file_c1" id="file_upload_m1" />
					<br />
					<input type="file" data-clear-btn="true" name="file_c2" id="file_upload_m2" />
				</div>
			</div>
			<input type="submit" value="{upload_button}" data-icon="arrow-r" data-iconpos="right" onclick='javascript: uploadfile_wait();' />
			</form>
			{goto_slice}
			<span id="upload_error">{error}</span>
		</div>
	</div>

<script>
var var_bicolor = {bicolor};

// $("#slicer_uploadModel").on("pagecreate",function() {
	if (var_bicolor == true) {
		$(".widget_bicolor").show();
		$("#file_upload_mono").attr("disabled", "disabled");
	}
	else {
		$(".widget_monocolor").show();
		$("#file_upload_s").attr("disabled", "disabled");
	}
	
	function uploadfile_wait() {
		// this create a blocked spinner when we return to this page by back button
		$("#overlay").addClass("gray-overlay");
		$(".ui-loader").css("display", "block");
	}
	
	$("#upload_model_tab_s").on("collapsibleexpand", function(event)
	{
		$("#file_upload_m1").val("");
		$("#file_upload_m2").val("");
	}); 
	
	$("#upload_model_tab_m").on("collapsibleexpand", function(event)
	{
		$("#file_upload_s").val("");
	});
// }

</script>
</div>