<div data-role="page" data-url="/connection/wifinotvisiblessid">
	<header data-role="header" class="page-header">
		<a data-icon="arrow-l" data-role="button" data-direction="reverse"
			data-rel="back">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			<form action="/connection/wifinotvisiblessid" method="post"
				accept-charset="utf-8">
				<input type="text" name="ssid" id="ssid" value=""  data-clear-btn="true"/>
				<div class="zim-error">{error}</div>
				<div>
					<input type="submit" value="{submit}" />
				</div>
			</form>
		</div>
	</div>
</div>