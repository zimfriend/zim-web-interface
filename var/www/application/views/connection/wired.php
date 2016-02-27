<div data-role="page" data-url="/connection/wired">
	<header data-role="header" class="page-header">
		<a data-icon="arrow-l" data-role="button" data-direction="reverse"
			data-rel="back">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			{text1} 
			<ul data-role="listview" data-inset="true" id="listview"
				class="shadowBox">
				<li><a href="/connection/wiredauto">{auto_btn}</a></li>
			</ul>
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
			{text2}
			<ul data-role="listview" data-inset="true" id="listview"
				class="shadowBox">
				<li><a href="/connection/wiredadvanced" data-prefetch>{adv_btn}</a></li>
			</ul>
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
	</div>
</div>
