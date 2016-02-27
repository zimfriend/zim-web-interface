<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Test video</title>
<script type="text/javascript" src="/assets/jwplayer/jwplayer.js"></script>
<script type="text/javascript">jwplayer.key="Jh6aqwb1m2vKLCoBtS7BJxRWHnF/Qs3LMjnt13P9D6A=";</script>
<style type="text/css">div#myVideo_wrapper {margin: 0 auto;}</style>
</head>
<body>
	<div id="myVideo">Loading the player...</div>
	<script type="text/javascript">
		jwplayer("myVideo").setup({
			file: "{video_url}",
			autostart: true,
			analytics: {
				enabled: false
			}
		});
	</script>
</body>
</html>