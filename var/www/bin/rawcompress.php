<?php

// enable_compress();
$path = '/var/www/tmp/test_lighttpd.stl';

// header('Content-type: application/octet-stream');
// print @file_get_contents($path);

function enable_compress() {
	if( empty($_SERVER['HTTP_ACCEPT_ENCODING']) ) { return false; }
	
	//If zlib is not ALREADY compressing the page - and ob_gzhandler is set
	if (( ini_get('zlib.output_compression') == 'On'
			OR ini_get('zlib.output_compression_level') > 0 )
			OR ini_get('output_handler') == 'ob_gzhandler' ) {
		return false;
	}
	
	//Else if zlib is loaded start the compression.
	if ( extension_loaded( 'zlib' ) AND (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) ) {
		ob_start('ob_gzhandler');
	}
	return true;
}

function _compress( $path ) {

	$supportsGzip = strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false;


	if ( $supportsGzip ) {
		exec("gzip -c -f -1 " . $path . " > " . $path . ".gz");
		$content = file_get_contents($path . ".gz");
		//gzencode( trim( preg_replace( '/\s+/', ' ', $data ) ), 1);
	} else {
// 		$content = $data;
		$content = file_get_contents($path);
	}

	$offset = 60 * 60;
	$expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";

	header('Content-Encoding: gzip');
	header("content-type: text/plain; charset: UTF-8");
	header("cache-control: must-revalidate");
	header( $expire );
	header( 'Content-Length: ' . strlen( $content ) );
	header('Vary: Accept-Encoding');

	echo $content;

}

// _compress( @file_get_contents($path) );
_compress($path);

// ob_end_flush();
