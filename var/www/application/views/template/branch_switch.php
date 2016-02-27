<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Test - UpgrandeSwitch</title>
	</head>
	<body>
		<form action="/test_version/branch" method="post" accept-charset="utf-8">
		<?php echo form_label('URL', 'url'); ?><br />
		<?php
		$array_options = array(
				'http://repo.zeepro.com/upgrade/profile/prod-b.profile'		=> 'prod-b',
				'http://repo.zeepro.com/upgrade/profile/prod-a.profile'		=> 'prod-a',
				'http://repo.zeepro.com/upgrade/profile/preprod.profile'	=> 'preprod',
				'http://repo.zeepro.com/upgrade/profile/beta.profile'		=> 'beta',
				'http://repo.zeepro.com/upgrade/profile/dev.profile'		=> 'dev',
		);
		echo form_dropdown('url', $array_options);
		?><br />
		<?php echo form_label('Permanent', 'permanent'); ?><br />
		<?php echo form_checkbox('permanent', 1); ?><br />
		<?php echo form_label('Force', 'force'); ?><br />
		<?php echo form_checkbox('force', 1); ?><br />
		<?php echo form_submit('submit', 'submit'); ?><br />
		<?php echo form_close('<br />'); ?>
		<hr>
		<form action="/test_version/branch" method="post" accept-charset="utf-8">
		<?php echo form_label('URL', 'url'); ?><br />
		<?php echo form_input('url'); ?><br />
		<?php echo form_label('Permanent', 'permanent'); ?><br />
		<?php echo form_checkbox('permanent', 1); ?><br />
		<?php echo form_label('Force', 'force'); ?><br />
		<?php echo form_checkbox('force', 1); ?><br />
		<?php echo form_submit('submit', 'submit'); ?><br />
		<?php echo form_close('<br />'); ?>
	</body>
</html>