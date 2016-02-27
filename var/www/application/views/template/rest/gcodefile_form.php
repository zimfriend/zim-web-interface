<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Test - printlist</title>
	</head>
	<body>
            <?php
//				$attributes = array('accept-charset' => 'utf-8'); 
//				echo form_open_multipart('t_printlist/send', $attributes);
            ?>
            <form action="/rest/gcodefile" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo form_label('Gcode', 'L_Gcode'); ?><br />
            <?php echo form_upload('f'); ?><br />
            <?php echo form_submit('submit', 'submit'); ?><br />
            <?php echo form_close('<br />'); ?>
	</body> 
</html>


