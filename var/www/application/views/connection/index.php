<div data-role="page" data-url="/connection">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			{hint}<br><br>
			<ul data-role="listview" data-inset="true" id="listview"
				class="shadowBox">
				<li><a href="/connection/wifissid" data-prefetch>{wifissid}</a></li>
				<li><a href="/connection/wifip2p" data-prefetch>{wifip2p}</a></li>
				<li><a href="/connection/wired" data-prefetch>{wired}</a></li>
			</ul>
<!-- 			<br> -->
<!-- 			<a href="/printerstate/sethostname?norestart" data-role="button" style="font-weight: lighter;">{set_hostname}</a> -->
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
	</div>
</div>
