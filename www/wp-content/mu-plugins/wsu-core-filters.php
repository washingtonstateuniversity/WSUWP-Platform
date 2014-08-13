<?php
/*
Plugin Name: WSU Core Filters
Plugin URI: http://web.wsu.edu/
Description: Various filters used to modify core data.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

/**
 * ms_files_rewriting should never be enabled.
 */
add_filter( 'pre_option_ms_files_rewriting', '__return_false' );

/**
 * We should always use yearmonth folders for uploads.
 */
add_filter( 'pre_option_uploads_use_yearmonth_folders', '__return_true' );

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