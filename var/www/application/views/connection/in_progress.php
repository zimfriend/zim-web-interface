<div data-role="page">
	<header data-role="header" class="page-header"></header>
	<div class="logo">
		<div id="link_logo"></div>
	</div>
	<div data-role="content">
		<div id="container" style="text-align: center;">
			<p id="hint_box">{config_printer}</p>
			<div id="error_box" style="display: none;">{connect_error_msg}</div>
			<div id="err_popup" data-role="popup" data-dismissible="false"
				class="ui-content">
				<p>{popup}</p>
				<a id="try_again" data-role="button" href="#">Try again</a>
			</div>
		</div>
	</div>

	<script>

$(document).on('pageshow', function()
{
	$(".ui-loader").css("display", "block");
});

/*
// Start magic
*/

$("#try_again").on("click", function()
{
	$(".ui-loader").css("display", "block");
	$("div#err_popup").popup("close");
	check_internet();
});

function ping_printer()
{
	var interval;
	var counter = 0;
	var localip = "";
	
	interval = setInterval(function()
	{
		var image = new Image();
		var image2 = new Image();
		var image3 = new Image();

		if (localip.length == 0)
		{
			$.ajax({
			cache: false,
			type: "POST",
			url: "https://sso.zeepro.com/getlocalip.ashx",
			data: { "printersn": "{printersn}"},
			dataType: "json",
			success: function (data) {
				if (data.state == "ok")
				{
					localip = data.localIP;
				}
			},
//			error:function (xhr, ajaxOptions, thrownError){}
		});
		}

		if (counter >= 90)
		{
			clearInterval(interval);
			$("p#hint_box").css('display', 'none');
			$("div#error_box").css('display', 'block');
			$(".ui-loader").css("display", "none");
		}
		else
		{
			counter += 1;
		}
		
		if (localip.length > 0)
		{
			image3.src = "http://" + localip + "/images/pixel.png?_=" + (new Date()).getTime();
		}
		image.src = "http://{hostname}/images/pixel.png?_=" + (new Date()).getTime();
		image2.src = "http://{hostname}.local/images/pixel.png?_=" + (new Date()).getTime();
		setTimeout(function()
		{
			if (image3.height != 0)
			{
				clearInterval(interval);
				window.location.href = "http://" + localip + "/account/first_signup/";
			}
			if (image.height != 0)
			{
				clearInterval(interval);
				window.location.href = "http://{hostname}/account/first_signup/";
			}
			if (image2.height != 0)
			{
				clearInterval(interval);
				window.location.href = "http://{hostname}.local/account/first_signup/";
			}
		}, 1000);
	}, 2000);
}

function check_internet()
{
	var count = -1;
	check_internet_interval = setInterval(function()
	{
		var pixel = new Image();
		
	
		pixel.src = "http://home.zeepro.com/assets/img/pixel.png?_=" + (new Date()).getTime();
		count++;
		if (count == 60)
		{
			clearInterval(check_internet_interval);
			$(".ui-loader").css("display", "none");
			$("div#err_popup").popup();
			$("div#err_popup").popup("open");
		}
		setTimeout(function()
		{
			if (pixel.height != 0)
			{
				clearInterval(check_internet_interval);
				ping_printer();
			}
		}, 500);
	}, 1000);
}

check_internet();

</script>

</div>