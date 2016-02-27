<div data-role="page">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
	</header>
	<div class="logo">
		<div id="link_logo"></div>
	</div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			<div class="zim-error">{errors}</div>
			<br />
			<?php
				$this->load->helper('form');
				echo form_open('/account/signin/{returnUrl}', array('data-ajax' => "false"));
				echo form_label('Email', 'email');
				echo form_input(array("name" => "email", "required" => "required")) . '<br />' . '<br />';
				echo form_label('{password}', 'password');
				echo form_password(array("name" => "password", "required" => "required")) . '<br />';
				echo '<div>';
				echo '<label><input type="checkbox" name="show_pass" data-mini=true>{show_password}</label>';
				echo "</div><br />";
				echo form_submit('submit', '{sign_in}');
				echo form_close();
			?>
			<a href="https://home.zeepro.com/login/privacy" data-ajax="false" style="font-weight:normal;" target="_blank"><i>{privacy_policy_link}</i></a>
		</div>
	</div>
	<script>
	$(document).ready(function()
	{
		$(".ui-loader").css('display', 'none');
	});
	$("input[type=submit]").on('click', function()
	{
		$(".ui-loader").css('display', 'block');
	});
	$("input[name=show_pass]").on("click", function()
	{
		if ($("input[name=show_pass]").is(':checked'))
			$("input[name=password]").attr("type", "text");
		else
			$("input[name=password]").attr("type", "password");
	});
	</script>
</div>