<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Test - cartridge</title>
	</head>
	<body>
            <?php
//				$attributes = array('accept-charset' => 'utf-8'); 
//				echo form_open_multipart('t_printlist/send', $attributes);
            ?>
            <form action="/test_cartridge" method="get" accept-charset="utf-8"">
            <?php echo form_hidden('p', 'l'); ?>
            <?php echo form_label('Left', 'L_Left'); ?><br />
            <?php echo form_dropdown('v', $cartridge_data_l); ?><br />
            <?php echo form_submit('submit', 'change', $disable_l); ?><br />
            <?php echo form_close('<br />'); ?>
            <form action="/test_cartridge" method="get" accept-charset="utf-8"">
            <?php echo form_hidden('p', 'r'); ?>
            <?php echo form_label('Right', 'L_Right'); ?><br />
            <?php echo form_dropdown('v', $cartridge_data_r); ?><br />
            <?php echo form_submit('submit', 'change', $disable_r); ?><br />
            <?php echo form_close('<br />'); ?>
	</body> 
</html>
