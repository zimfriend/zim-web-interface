<div data-role="page" data-url="/share/video_upload">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<div id="youtube_popup" data-role="popup" data-dismissible="false" class="ui-content" style="max-width:250px; text-align: center;" data-overlay-theme="a" data-theme="b">
				{yt_upload_popup_text}
				<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" onclick="javascript: finish_yt_upload();">{yt_callback_ok}</a>
			</div>
			<p>{uploading}</p>
			<div id="yt_upload_loading" class="ui-loader ui-corner-all ui-body-a ui-loader-default" style="display:block">
				<span class="ui-icon-loading"></span>
			</div>
		</div>
	</div>
	<script>
		$.get('/share/connect_google/true?state={state}&code={code}')
		.done(function(data)
		{
			$("div#yt_upload_loading").hide();
			$("div#youtube_popup").popup("open");
		});
		
		function finish_yt_upload() {
			window.location.href = "/printdetail/timelapse";
		}
	</script>
</div>	
