<?php
/*
 * Plugin Name: Bland Index File
 * Plugin URI: http://web.wsu.edu
 * Description: This is a blank index file with an unnecessary description.
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: http://web.wsu.edu
 * Version: 0.2
 * Network: true
 */

$wsuwp_global_version = '0.7.4';
$wsuwp_wp_changeset = '28354';

add_filter( 'spine_enable_builder_module', '__return_true' );

/**
 * Returns the current deployment version of WSUWP Platform
 *
 * @return string Combines the WSUWP global version and WordPress changeset.
 */
function wsuwp_global_version() {
	global $wsuwp_global_version, $wsuwp_wp_changeset;
	return $wsuwp_global_version . '-' . $wsuwp_wp_changeset;
}