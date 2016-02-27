<div data-role="page" data-url="/sliceupload/upload">
	<header data-role="header" class="page-header"></header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<div id="wait_message" style="text-align: center;">{wait_message}</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var var_ajax;

$(document).ready(add_model());

function add_model() {
	var_ajax = $.ajax({
		url: "/sliceupload/add_model_ajax",
		type: "POST",
		data: {
			file: '{model_name}',
			},
		cache: false,
		timeout: 1000*60*10,
	})
	.done(function(html) {
// 		$('#wait_message').html("{fin_message}");
// 		setTimeout(function(){
// 			window.location.href="/sliceupload/slice";
// 			}, 3000);
		
		if (var_ajax.status == 200) {
			window.location.href="/sliceupload/slice";
		}
		else if (var_ajax.status == 202) {
			var response = JSON.parse(html);
			var mid = response.id;
			var xsize = response.xsize;
			var ysize = response.ysize;
			var zsize = response.zsize;
			var scalemax = response.{key_smax};
			
			window.location.href="/sliceupload/reducesize?id=" + mid + "&x="
					+ xsize + "&y=" + ysize + "&z=" + zsize + "&ms=" + scalemax;
		}
	})
	.fail(function() { // not in printing
		$('#wait_message').html("{fail_message}");
		$('<div>').appendTo('#container')
		.attr({'id': 'return_button', 'onclick': 'javascript: window.location.href="/sliceupload/upload";'}).html('{return_button}')
		.button().button('refresh');
	});
}
</script>