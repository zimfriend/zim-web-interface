<div data-role="page" data-url="/test_version">
	<style type="text/css"> .ui-table-columntoggle-btn { display: none !important; } </style>
	<header data-role="header" class="page-header"></header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<table data-role="table" data-mode="columntoggle" id="test-table" class="ui-shadow table-stroke" style="background-color:#e7e7e7">
				<tbody>
					{array_info}
					<tr>
						<td>{title}</td>
						<td>{value}</td>
					</tr>
					{/array_info}
					<tr>
						<td>{port_test_title}</td>
						<td>
							<p style="margin: 0px;">
								{port_test_r80} <span id="return_printerport_80">...</span>
								<br/>
								{port_test_r443} <span id="return_printerport_443">...</span>
								<br/>
								{port_test_r4443} <span id="return_printerport_4443">...</span>
							</p>
							<p style="margin: 0px;">
								<a href="http://portquiz.net" target="_blank">{port_test_l80}</a>
								<br/>
								<a href="http://portquiz.net:443" target="_blank">{port_test_l443}</a>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
<script>
function testPrinterPort(var_port) {
	var var_ajax;
	
	if (typeof var_port == 'undefined') {
		return;
	}
	else {
		var var_check_port = parseInt(var_port);
		if (var_check_port <= 0 || var_check_port != var_port) {
			return;
		}
	}
	
	var_ajax = $.ajax({
		url: "/test_version/test_port",
		data: { v: var_port },
		async: false,
		cache: false,
		type: "HEAD"
	})
	.done(function() {
		$('span#return_printerport_' + var_port).html('{port_test_ok}');
	})
	.fail(function() {
		$('span#return_printerport_' + var_port).html('{port_test_ko}');
	});
}

$(document).ready(function() {
	testPrinterPort(80);
	testPrinterPort(443);
	testPrinterPort(4443);
});

</script>

</div>
