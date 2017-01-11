<?php
/*
 * Plugin Name: WSUWP Platform Global
 * Plugin URI: https://web.wsu.edu/
 * Description: Controls WSUWP Platform global version
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: https://web.wsu.edu/
 * Version: 1.5.6
 * Network: true
 */

$wsuwp_global_version = '1.5.6';
$wsuwp_wp_changeset = '39871';

/**
 * Returns the current deployment version of WSUWP Platform
 *
 * @return string Combines the WSUWP global version and WordPress changeset.
 */
function wsuwp_global_version() {
	global $wsuwp_global_version, $wsuwp_wp_changeset;
	return $wsuwp_global_version . '-' . $wsuwp_wp_changeset;
}
