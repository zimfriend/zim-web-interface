<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

function DectectOS_checkWindows() {
	return (strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN');
}
