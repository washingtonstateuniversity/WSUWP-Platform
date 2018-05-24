<?php
/*
 * Plugin Name: WSUWP Platform Global
 * Plugin URI: https://github.com/washingtonstateuniversity/WSUWP-Platform
 * Description: Controls WSUWP Platform global version
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: https://web.wsu.edu/
 * Version: 1.7.5
 * Network: true
 */

$wsuwp_global_version = '1.7.5';
$wsuwp_wp_changeset = '43297';

/**
 * Returns the current deployment version of WSUWP Platform
 *
 * @return string Combines the WSUWP global version and WordPress changeset.
 */
function wsuwp_global_version() {
	global $wsuwp_global_version, $wsuwp_wp_changeset;
	return $wsuwp_global_version . '-' . $wsuwp_wp_changeset;
}

/**
 * Loads additional, white-listed, must use plugins.
 *
 * This allows mu-plugins in individual directories to be deployed and then
 * activated separately. This in turn makes the WSUWP Platform slimmer and
 * more flexible as more code can be managed in individual repositories.
 *
 * @since 1.6.0
 */
if ( file_exists( __DIR__ . '/wsuwp-load-mu-plugins/wsuwp-load-mu-plugins.php' ) ) {
	require_once __DIR__ . '/wsuwp-load-mu-plugins/wsuwp-load-mu-plugins.php';
}
$wsuwp_mu_plugins = apply_filters( 'wsuwp_load_mu_plugins', array() );

foreach ( $wsuwp_mu_plugins as $wsuwp_mu_plugin ) {
	if ( file_exists( __DIR__ . '/' . $wsuwp_mu_plugin ) ) {
		require_once __DIR__ . '/' . $wsuwp_mu_plugin;
	}
}

unset( $wsuwp_mu_plugins );
unset( $wsuwp_mu_plugin );
