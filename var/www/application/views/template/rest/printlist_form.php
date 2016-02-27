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
            <form action="/rest/storemodel" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo form_label('Name', 'L_Name'); ?><br />
            <?php echo form_textarea('n'); ?><br />
            <?php echo form_label('Desc', 'L_Desp');?><br />
            <?php echo form_textarea('d'); ?><br />
            <?php echo form_label('Time', 'L_Time'); ?><br />
            <?php echo form_input('t'); ?><br />
            <?php echo form_label('Length1', 'L_Leng1'); ?><br />
            <?php echo form_input('l1'); ?><br />
            <?php echo form_label('Length2', 'L_Leng2'); ?><br />
            <?php echo form_input('l2'); ?><br />
            <?php echo form_label('Color1', 'L_Col1'); ?><br />
            <?php echo form_input('c1'); ?><br />
            <?php echo form_label('Color2', 'L_Col2'); ?><br />
            <?php echo form_input('c2'); ?><br />
            <?php echo form_label('Gcode', 'L_Gcode'); ?><br />
            <?php echo form_upload('f'); ?><br />
            <?php echo form_label('Pic1', 'L_Pic1'); ?><br />
            <?php echo form_upload('p1'); ?><br />
            <?php echo form_label('Pic2', 'L_Pic2'); ?><br />
            <?php echo form_upload('p2'); ?><br />
            <?php echo form_label('Pic3', 'L_Pic3'); ?><br />
            <?php echo form_upload('p3'); ?><br />
            <?php echo form_label('Pic4', 'L_Pic4'); ?><br />
            <?php echo form_upload('p4'); ?><br />
            <?php echo form_label('Pic5', 'L_Pic5'); ?><br />
            <?php echo form_upload('p5'); ?><br />
            <?php echo form_submit('submit', 'submit'); ?><br />
            <?php echo form_close('<br />'); ?>
	</body> 
</html>


