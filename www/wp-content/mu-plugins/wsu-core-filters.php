<?php
/*
Plugin Name: WSU Core Filters
Plugin URI: http://web.wsu.edu/
Description: Various filters used to modify core data.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

add_filter( 'theme_root_uri', 'wsuwp_modify_theme_root_uri' );
/**
 * Load theme resources from the home URL rather than the network
 * URL. This can help group DNS requests a bit and avoid unnecessary
 * lookups at the primary domain.
 *
 * @param string $theme_root_uri Current root URI for the theme.
 *
 * @return string Modified root URI for the theme.
 */
function wsuwp_modify_theme_root_uri( $theme_root_uri ) {
	$theme_root = parse_url( $theme_root_uri );
	$home_root = parse_url( get_home_url() );

	if ( isset( $theme_root['host'] ) && isset( $home_root['host'] ) ) {
		$theme_root_uri = str_replace( '/' . $theme_root['host'] . '/', '/' . $home_root['host'] . '/', $theme_root_uri );
	}
	return $theme_root_uri;
}