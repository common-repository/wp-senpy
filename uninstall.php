<?php
/**
	* Runs on Uninstall of wp-senpy
	*
	* @package   wp-senpy
	* @author    Álvaro Gericke Parga
	* @license   GPL-2.0
	* @link      senpy.readthedocs.io
*/

// Check that we should be doing this
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit; // Exit if accessed directly
}

// Delete Options
$option = 'wpsenpy_settings';

if ( get_option( $option ) ) {
	delete_option( $option );
}

?>