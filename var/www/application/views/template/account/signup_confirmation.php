<div data-role="page">
	<header data-role="header" class="page-header"></header>
	<div class="logo">
		<div id="link_logo"></div>
	</div>
	<div data-role="content">
		<div id="container">
			<h3>{code_title}</h3>
			<div class="zim-error">{error}</div>
			<?php
				$this->load->helper('form');
			
				echo form_open('/account/signup_confirmation', array('data-ajax' => 'false'));
				echo form_hidden('email', $this->session->flashdata('email'));
				echo form_hidden('password', $this->session->flashdata('password'));
				echo '<p>'.t("code_text").'</p>';
				echo form_input('code');
				echo '<br />';
				echo form_submit('submit', 'Ok');
				echo form_close();
			?>
		</div>
	</div>
</div>