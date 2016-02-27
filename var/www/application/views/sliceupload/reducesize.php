<div data-role="page" data-url="/sliceupload/reducesize">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false" style="display: none;">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<style>
    	input[type=number]
    	{
        	display : none !important;
		}
	</style>
	<div data-role="content">
		<div id="container">
			<h2>{reducesize_title}</h2>
			<label>{reducesize_text}</label>
			<br />
			<form action="" method="post">
				<input type="hidden" name="id" value="{id}">
				<input type="hidden" name="x" value="{xsize}">
				<input type="hidden" name="y" value="{ysize}">
				<input type="hidden" name="z" value="{zsize}">
				<input type="hidden" name="ms" value="{max_percent}">
				<label for="sizepercentage">{reducesize_scale}</label><br />
				<div class="ui-grid-a">
					<div class="ui-block-a" style="width:1%;position:relative;top:11px;left:28px;">
						<span id="size_percentage"></span>
					</div>
					<div class="ui-block-b" id="size-slider" style="width:75%">
						<input type="range" name="sizepercentage" id="sizepercentage" value="{max_percent}" min="1" max="100" />
					</div>
				</div>
				<div id="dimension"><center>{reduced_size} <span id="x_size"></span>mm x <span id="y_size"></span>mm x <span id="z_size"></span>mm</center></div>
				<input id="resize_button" type="button" value="{resize_button}">
				<input type="button" value="{cancel_button}" onclick='javascript: window.location.href = "/sliceupload/upload";'>
			</form>
		</div>
	</div>

<script>
var var_xsize = {xsize};
var var_ysize = {ysize};
var var_zsize = {zsize};
var var_max_percent = {max_percent};
var var_model_id = {id};
var var_ajax;

$(document).ready(function () {
	$("span#size_percentage").html($("#sizepercentage").val() + "%");
	$(".ui-slider-handle").attr('style', "left: 100%;");
	$("#sizepercentage").attr('max', var_max_percent);
// 	$('#sizepercentage').val(var_max_percent);
	$("#x_size").text((var_xsize * var_max_percent / 100).toFixed(2));
	$("#y_size").text((var_ysize * var_max_percent / 100).toFixed(2));
	$("#z_size").text((var_zsize * var_max_percent / 100).toFixed(2));
	
	$('#sizepercentage').on('change', function () {
		$("#x_size").text((var_xsize * $('#sizepercentage').val() / 100).toFixed(2));
		$("#y_size").text((var_ysize * $('#sizepercentage').val() / 100).toFixed(2));
		$("#z_size").text((var_zsize * $('#sizepercentage').val() / 100).toFixed(2));
	});
});

// $("input[type=submit]").on('click', function() {
// 	$("#overlay").addClass("gray-overlay");
// 	$(".ui-loader").css("display", "block");
// });

$('#resize_button').on('click', function() {
	var_ajax = $.ajax({
		url: "/sliceupload/preview_change_ajax",
		type: "GET",
		cache: false,
		data: {
			id:	var_model_id,
			s:	$('#sizepercentage').val(),
		},
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		window.location.replace("/sliceupload/slice");
	})
	.fail(function() { // not allowed
		alert('failed');
	});
	
	return;
});

$("#size-slider").on("change", function()
{
	$("span#size_percentage").html($("#sizepercentage").val() + "%");
});
</script>

</div>
