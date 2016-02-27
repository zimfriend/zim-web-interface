<div data-role="page" data-url="/share/facebook_share">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<form action="/share/facebook_upload" method="POST" id="fb_form" data-ajax="false">
				<div class="ui-field-contain">
					<label for="fb_title" style="margin-top: 1.7em">{title_label}</label>
				    <input type="text" name="fb_title" id="fb_title" required="required" />
				</div>
				<div class="ui-field-contain">
					<label for="fb_desc" style="margin-top: 1.7em">{desc_label}</label>
				    <input type="text" name="fb_desc" id="fb_desc" required="required" />
				</div>
				<br />
				<input type="submit" value="{upload_to_fb}" />
			</form>
		</div>
	</div>
</div>
