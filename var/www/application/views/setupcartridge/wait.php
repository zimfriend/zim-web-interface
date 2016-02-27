<div data-role="page" data-url="/setupcartridge/wait">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<h2>Please confirm the information blow 请确认以下信息</h2>
			<div class="ui-grid-a">
				<div class="ui-block-a"><div class="ui-bar ui-bar-d" style="height:2em;">Number of tag to be printed 还需写入的剩余数量</div></div>
				<div class="ui-block-b"><div class="ui-bar ui-bar-c" style="height:2em;">{times}</div></div>
				<div class="ui-block-a"><div class="ui-bar ui-bar-d" style="height:2em;">UTC Date UTC日期</div></div>
				<div class="ui-block-b"><div class="ui-bar ui-bar-c" style="height:2em;">Day 日: {day}; Month 月: {month}; Year 年: {year}</div></div>
				<div class="ui-block-a"><div class="ui-bar ui-bar-d" style="height:2em;">Filament type 打印丝种类</div></div>
				<div class="ui-block-b"><div class="ui-bar ui-bar-c" style="height:2em;">{name}</div></div>
			</div>
			<form method="post" action="/setupcartridge/write" data-ajax="false">
				<input type="hidden" name="type" value="{type}">
				<input type="hidden" name="year" value="{year}">
				<input type="hidden" name="month" value="{month}">
				<input type="hidden" name="day" value="{day}">
				<input type="hidden" name="times" value="{times}">
				<input type="hidden" name="side" value="{side}">
				<input type="submit" value="write tag 写入标签">
			</form>
			<div class="zim-error">{hint}</div>
		</div>
	</div>
</div>

