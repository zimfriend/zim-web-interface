<div data-role="page" data-url="/setupcartridge/write">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<h2>Write tag 写入标签</h2>
			<form method="post" action="/setupcartridge/wait" data-ajax="false">
				{ko}
				<img src="{image}"><br>
				{hint}
				<input type="hidden" name="type" value="{type}">
				<input type="hidden" name="year" value="{year}">
				<input type="hidden" name="month" value="{month}">
				<input type="hidden" name="day" value="{day}">
				<input type="hidden" name="times" value="{times}">
				<input type="hidden" name="side" value="{side}">
				{ok}
			</form>
		</div>
	</div>
</div>

