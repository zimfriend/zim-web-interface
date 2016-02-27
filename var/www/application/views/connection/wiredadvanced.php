<div data-role="page" data-url="/connection/wiredadvanced">
	<header data-role="header" class="page-header">
		<a data-icon="arrow-l" data-role="button" data-direction="reverse"
			data-rel="back">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			<form action="/connection/wiredadvanced" method="post" accept-charset="utf-8" data-ajax="false">
				<label for="ip">{ip_label}</label>
				<input type="text" name="ip" id="ip" value="{ip_value}" placeholder="{ip_hint}" data-clear-btn="true"/>
				<div class="zim-error">{ip_error}</div>
				<br />
				<label for="mask">{mask_label}</label>
				<input type="text" name="mask" id="mask" value="{mask_value}" placeholder="{mask_hint}" data-clear-btn="true"/>
				<div class="zim-error">{mask_error}</div>
				<br />
				<label for="gateway">{gateway_label}</label>
				<input type="text" name="gateway" id="gateway" value="{gateway_value}" placeholder="{gateway_hint}" data-clear-btn="true"/>
				<div class="zim-error">{gateway_error}</div>
				<br />
				<label for="dns">{dns_label}</label>
				<input type="text" name="dns" id="dns" value="{dns_value}" placeholder="{dns_hint}" data-clear-btn="true"/>
				<div class="zim-error">{dns_error}</div>
				<br />
				<div>
					<input type="submit" value="{submit}" />
				</div>
			</form>
		</div>
	</div>
</div>
