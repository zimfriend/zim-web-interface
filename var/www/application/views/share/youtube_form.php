<div data-role="page" data-url="/share/youtube_form">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<form action="/share/youtube_form" method="POST" id="yt_form" data-ajax="false">
				<div class="ui-field-contain">
				    <label for="yt_title" style="margin-top: 1.7em">{title_label}</label>
				    <input type="text" name="yt_title" id="yt_title" value="{yt_title}" />
				</div>
				<div class="ui-field-contain">
					<label for="yt_title" style="margin-top: 2em">{desc_label}</label>
				    <textarea name="yt_description" form="yt_form" placeholder="Enter description here">{yt_desc}</textarea>
				</div>
<!-- 				<div class="ui-field-contain"> -->
<!-- 					<label for="yt_title" style="margin-top: 1.7em">{tags_label}</label> -->
<!-- 				    <input name="yt_tags" value="{yt_tags}" /> -->
<!-- 				</div> -->
				<div class="ui-field-contain">
					<label for="yt_title" style="margin-top: 2em">{privacy_label}</label>
				    <select name="yt_privacy">
						<option value="public">{yt_privacy_public}</option>
						<option value="unlisted">{yt_privacy_unlisted}</option>
						<option value="private">{yt_privacy_private}</option>
					</select>
				</div>
				<input type="submit" value="{upload_to_yt}" />
			</form>
		</div>
	</div>
</div>
