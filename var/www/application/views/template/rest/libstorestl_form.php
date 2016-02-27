<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Test - LibStoreSTL</title>
	</head>
	<body>
            <?php
//				$attributes = array('accept-charset' => 'utf-8'); 
//				echo form_open_multipart('t_printlist/send', $attributes);
            ?>
            <form action="/rest/libstorestl" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo form_label('Name', 'Name'); ?><br />
            <?php echo form_input('name'); ?><br />
            <?php echo form_label('Stl', 'L_Stl'); ?><br />
            <?php echo form_upload('f1'); ?><br />
            <?php echo form_upload('f2'); ?><br />
            <?php echo form_submit('submit', 'submit'); ?><br />
            <?php echo form_close('<br />'); ?>
	</body> 
</html>