<div data-role="page" data-url="/menu_home" id="page_menu_home">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<a href="/printerstate/upgradenote?reboot"><b><span id="upgrade_notification">{update_available}</span></b></a>
			<ul data-role="listview" id="listview_print" class="shadowBox" data-inset="true">
				<li><a href="/printmodel/listmodel">
					<h2>{menu_printlist}</h2></a>
				</li>
				<li id="upload_li"><a href="/sliceupload/upload">
					<h2>{upload}</h2></a>
				</li>
			</ul>
			<ul data-role="listview" id="listview_library" class="shadowBox" data-inset="true">
				<li style="display: {library_visible};">
					<a href="/printerstoring/libraries"><h2>{my_library}</h2></a>
				</li>
				<li>
					<a id="zim_shop" href="http://zeepro.com/collections/all" target="_blank"><h2>{my_zim_shop}</h2></a>
				</li>
			</ul>
			<ul data-role="listview" id="listview_manage" class="shadowBox" data-inset="true">
				<li><a href="/manage" data-ajax="false">
					<h2>{manage}</h2></a>
				</li>
			</ul>
			<ul data-role="listview" id="listview_config" class="shadowBox" data-inset="true">
				<li><a href="/printerstate">
					<h2>{menu_printerstate}</h2></a>
				</li>
				<li><a href="/printerstate/printerinfo">
					<h2>{about}</h2></a>
				</li>
			</ul>
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
	</div>
<script>
var var_interval_checkUpgrade;
var var_ajax_checkUpgrade;
var iOS = (navigator.userAgent.match(/(iPad|iPhone|iPod)/g));

if (iOS) {
	$("#upload_li").remove();
}

$('a#zim_shop').click(function(event) {
// 	event.preventDefault();
// 	event.stopPropagation();
// 	window.open(this.href, '_blank');
	
	$.get('/menu_home/stats_my_shop', function() { console.log('stats my shop call sent.'); });
});

function index_checkUpgrade() {
	var currentPage = $.mobile.activePage.attr('id');
	
	if (typeof(currentPage) == 'undefined' || currentPage != 'page_menu_home') {
		// disable any checking when jqm variable is not ready or changing page
		console.log('disable check upgrade ajax in this interval');
		return;
	}
	
	var_ajax_checkUpgrade = $.ajax({
		cache: false,
		type: "GET",
		url: "/printerstate/checkupgrade",
		success: function () {
			if (var_ajax_checkUpgrade.status == 202) {
				$('span#upgrade_notification').html("{update_hint}");
				clearInterval(var_interval_checkUpgrade);
			}
		},
		error: function (data) {
			console.log('check upgrade failed: ' + data);
		},
	});
}

$(document).ready(function() {
	var_interval_checkUpgrade = setInterval(index_checkUpgrade, 30000);
});
</script>
</div>
