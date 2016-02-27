<div data-role="page">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
	</header>
	<div class="logo">
		<div id="link_logo"></div>
	</div>
	<div data-role="content">
		<div id="container">
			<h1>{name_printer}</h1>
			<div id="error"><?php $this->load->helper('form'); echo validation_errors(); ?></div>
			<form action="/activation/activation_form/{returnUrl}" data-ajax="false" method="POST">
				<input type="hidden" name="email" value='{email}' />
				<input type="hidden" name="password" value='{password}' />
				<label for="printer_name"></label>
				<input type="text" name="printer_name" value="" pattern="^[a-zA-Z0-9][a-zA-Z0-9\-]{0,7}[a-zA-Z0-9]$|^[a-zA-Z0-9]$" title="{format}" required />
				<input type="submit" name="submit" value="Ok" />
			</form>
		</div>
	</div>
</div>