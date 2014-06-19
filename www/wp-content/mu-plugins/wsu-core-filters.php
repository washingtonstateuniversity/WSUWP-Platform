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