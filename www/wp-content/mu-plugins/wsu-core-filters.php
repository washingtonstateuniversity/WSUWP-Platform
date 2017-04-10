<?php
/*
Plugin Name: WSU Core Filters
Plugin URI: http://web.wsu.edu/
Description: Various filters used to modify core data.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

add_filter( 'upload_dir', 'wsuwp_upload_dir' );
/**
 * @param array $uploads Data associated with the upload URL. We assume the subdir key is correct.
 *
 * @return array Modified upload URL data.
 */
function wsuwp_upload_dir( $uploads ) {
	if ( ! defined( 'WP_CONTENT_DIR' ) ) {
		return $uploads;
	}

	$site_id = get_current_blog_id();

	$uploads['basedir'] = WP_CONTENT_DIR . '/uploads/sites/' . $site_id;
	$uploads['baseurl'] = content_url() . '/uploads/sites/' . $site_id;

	$uploads['path'] = $uploads['basedir'] . $uploads['subdir'];
	$uploads['url'] = $uploads['baseurl'] . $uploads['subdir'];

	return $uploads;
}

add_filter( 'cje_capability', 'wsuwp_cje_capability' );
/**
 * Set the capability required to edit custom Javascript if the Custom Javascript Editor is enabled.
 *
 * @return string
 */
function wsuwp_cje_capability() {
	return 'edit_javascript';
}

add_filter( 'pre_option_permalink_structure', 'wsuwp_filter_permalink_structure' );
/**
 * Provide a default permalink structure of year/month/day/post-slug/ to avoid the
 * default '/blog/' prefix on every network's main site.
 *
 * @return string Modified permalink structure.
 */
function wsuwp_filter_permalink_structure() {
	return '/%year%/%monthnum%/%day%/%postname%/';
}

add_filter( 'pre_option_category_base', 'wsuwp_filter_category_base' );
/**
 * Force the category base to '/category/' to avoid the default '/blog/' prefix on
 * every network's main site.
 *
 * @return string Modified category base structure.
 */
function wsuwp_filter_category_base() {
	return '/category';
}

add_filter( 'pre_option_tag_base', 'wsuwp_filter_tag_base' );
/**
 * Force the tag base to '/tag/' to avoid the default '/blog/' prefix on every
 * network's main site.
 *
 * @return string Modified tag base structure.
 */
function wsuwp_filter_tag_base() {
	return '/tag';
}

add_action( '_admin_menu', 'wsuwp_filter_admin_menu' );
/**
 * Permalinks can impact so much in strange ways. It's best to disable this now until
 * we can determine the best approach.
 *
 * Do realize that we're using a "private" action here with _admin_menu, so stability
 * is not guaranteed.
 */
function wsuwp_filter_admin_menu() {
	global $submenu;
	unset( $submenu['options-general.php'][40] );
}

add_filter( 'update_footer', 'wsuwp_update_footer_text', 11 );
/**
 * Displays WSUWP Platform and WordPress version information in the admin footer.
 *
 * @since 1.6.0
 *
 * @return string
 */
function wsuwp_update_footer_text() {
	global $wsuwp_global_version, $wsuwp_wp_changeset;

	$version = ltrim( get_bloginfo( 'version' ), '(' );
	$version = rtrim( $version, ')' );
	$version = explode( '-', $version );

	$text = 'WSUWP Platform <a target=_blank href="https://github.com/washingtonstateuniversity/WSUWP-Platform/tree/v' . $wsuwp_global_version . '">' . $wsuwp_global_version . '</a> | ';
	$text .= 'WordPress ' . $version[0];

	if ( isset( $version[1] ) ) {
		$text .= ' ' . ucwords( $version[1] );
	}

	$text .= ' [<a target=_blank href="https://core.trac.wordpress.org/changeset/' . $wsuwp_wp_changeset . '">' . $wsuwp_wp_changeset . '</a>]';

	return $text;
}
