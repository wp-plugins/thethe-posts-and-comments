<?php
/**
 * Function TheTheFly_require
 * 
 * @param $dir
 * @param $load
 * @return void
 */
if (!function_exists('TheTheFly_require')) :
function TheTheFly_require($dir,$load = array('class.','func.','lib.'))
{
	if ( is_dir($dir)) {
		$handle = @opendir( $dir );
		while (($file = readdir( $handle ) ) !== false ) {
			if ( substr($file, 0, 1) == '.' ) continue;
			if ( is_dir($inc = realpath($dir.'/'.$file)) ) {
				TheTheFly_require($inc,$load);
			} elseif (is_file($inc = realpath($dir.'/'.$file))) {
				foreach ($load as $needle) {
					if (strstr($file,$needle) !== false) {
						require_once $inc;
					}
				}
			}
		}
		@closedir( $handle );
	}
} // end func TheTheFly_require
endif;