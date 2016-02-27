<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Test - 3dslash</title>
	</head>
	<body>
		<form action="/3dslash" method="post" accept-charset="utf-8">
		<?php echo form_label('Name', 'name'); ?><br />
		<?php echo form_input('name'); ?><br />
		<?php echo form_label('Token', 'token'); ?><br />
		<?php echo form_input('token'); ?><br />
		<?php echo form_submit('submit', 'submit'); ?><br />
		<?php echo form_close('<br />'); ?>
	</body>
</html>