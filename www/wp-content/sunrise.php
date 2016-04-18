<?php
/**
 * Sunrise file for WSUWP
 *
 * The primary function of this sunrise file is as a router for the sites being accessed
 * on various networks within the WSUWP system. As each site is accessed, we do our best
 * to cache that information for future requests, which limits the load on the database
 * for simple site lookups.
 *
 * This file is loaded before many things in WordPress.
 *
 * Our current expectation is that all domains are stored in the wp_site and wp_blogs
 * tables without leading or trailing slashes. e.g. wp.wsu.edu
 *
 * We also expect that all paths stored in the wp_site and wp_blogs tables do have
 * leading and trailing slashes. e.g. /sub-site-path/
 *
 * Support is currently available for networks that are setup in subdomain mode and
 * in subdirectory mode. Networks setup in subdirectory mode can only be one path deep.
 *
 *     These work:
 *         - http://site1.network1.wp.wsu.edu/
 *         - http://network2.wp.wsu.edu/site1/
 *
 *     This does not work:
 *         - http://network2.wp.wsu.edu/site1/site2/
 *
 * When this file has done its intended job, several things will have been setup for use
 * by the remaining parts of WordPress:
 *
 *     - $blog_id
 *     - $site_id
 *     - $current_blog
 *     - $current_site
 *     - COOKIE_DOMAIN
 *
 * @type WPDB $wpdb
 */

// Remove strict standards reporting, only show notices and warnings.
if ( WP_DEBUG && WSU_DISABLE_STRICT ) {
	error_reporting( E_ALL ^ E_STRICT );
}

if ( ! defined( 'WSU_LOCAL_CONFIG' ) ) {
	define( 'WSU_LOCAL_CONFIG', false );
}

if ( defined( 'COOKIE_DOMAIN' ) ) {
	die( 'The constant "COOKIE_DOMAIN" is defined (probably in wp-config.php). Please remove or comment out that define() line.' );
}

//Capture the domain and path from the current request
$requested_domain    = $_SERVER['HTTP_HOST'];
$requested_uri       = trim( $_SERVER['REQUEST_URI'], '/' );

// We currently support one subdirectory deep, and therefore only look at the first path level
$requested_uri_parts = explode( '/', $requested_uri );
$requested_path = $requested_uri_parts[0] . '/';

wp_cache_add_global_groups( 'wsuwp:network' );
wp_cache_add_global_groups( 'wsuwp:site' );

// If we're dealing with a root domain, we want to leave it at a path of '/'
if ( '/' !== $requested_path ) {
	$requested_path = '/' . $requested_path;
}

if ( ! $current_blog = wp_cache_get( $requested_domain . $requested_path, 'wsuwp:site' ) ) {
	// Treat www the same as the root URL
	$alternate_domain = preg_replace( '|^www\.|', '', $requested_domain );

	//suppress errors and capture current suppression setting
	$suppression = $wpdb->suppress_errors();

	if ( $requested_domain !== $alternate_domain ) {
		$domain_where = $wpdb->prepare( 'domain IN ( %s, %s )', $requested_domain, $alternate_domain );
	} else {
		$domain_where = $wpdb->prepare( 'domain = %s', $requested_domain );
	}

	/**
	 * The following query will find any one level deep subfolder sites on any page view, but
	 * will only help us with subdomain networks if it is a root visit with an empty path. If
	 * this returns null, we'll want to go to a backup.
	 */
	$found_site_id = $wpdb->get_var( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE $domain_where AND path = %s", $requested_path ) ); // WPCS: unprepared SQL OK.

	/**
	 * If the query for domain and path has failed, then we'll assume this is a site that has
	 * no path assigned and search for that accordingly.
	 */
	if ( ! $found_site_id ) {
		$found_site_id = $wpdb->get_var( "SELECT blog_id FROM $wpdb->blogs WHERE $domain_where and path = '/' " ); // WPCS: unprepared SQL OK.
	}

	//reset error suppression setting
	$wpdb->suppress_errors( $suppression );

	/**
	 * If we found a blog_id to match the domain above, then we turn to WordPress to get the
	 * remaining bits of info from the standard wp_blogs and wp_site tables. Then we squash
	 * it all together in the $current_site, $current_blog, $site_id, and $blog_id globals so
	 * that it is available for the remaining operations on this page request.
	 */
	if ( $found_site_id ) {
		$current_blog = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE blog_id = %d LIMIT 1", $found_site_id ) );
	}

	// If a row was found, set it in cache for future lookups
	if ( $current_blog ) {
		// Start with the assumption that SSL is available for this domain.
		$current_blog->ssl_enabled = true;

		// We're looking for a base option name of foo.bar.com_ssl_disabled
		$ssl_domain_check = $requested_domain . '_ssl_disabled';
		$non_ssl_domain = $wpdb->get_row( $wpdb->prepare( "SELECT option_id FROM {$wpdb->base_prefix}options WHERE option_name = %s", $ssl_domain_check ) );

		if ( is_object( $non_ssl_domain ) ) {
			$current_blog->ssl_enabled = false;
		}

		wp_cache_add( $requested_domain . $requested_path, $current_blog, 'wsuwp:site', 60 * 60 * 12 );
	}
}

if ( $current_blog ) {
	//set the blog_id and site_id globals that WordPress expects
	$blog_id = $current_blog->blog_id;
	$site_id = $current_blog->site_id;

	// setup the current_site global that WordPress expects
	if ( ! $current_site = wp_cache_get( $site_id, 'wsuwp:network' ) ) {
		$current_site = $wpdb->get_row( $wpdb->prepare( "SELECT * from $wpdb->site WHERE id = %d LIMIT 0,1", $site_id ) );

		// Add blog ID after the fact because it is required by both scenarios
		$current_site->blog_id = $wpdb->get_var( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s LIMIT 0,1", $current_site->domain, $current_site->path ) );

		wp_cache_add( $site_id, $current_site, 'wsuwp:network', 60 * 60 * 12 );
	}

	if ( isset( $current_blog->ssl_enabled ) && true === $current_blog->ssl_enabled && false === WSU_LOCAL_CONFIG ) {
		define( 'FORCE_SSL_ADMIN', true );
		define( 'FORCE_SSL_LOGIN', true );
	}

	/**
	 * Build the cookie domain based on the configuration of the WSUWP_COOKIE_DOMAIN
	 * constant. If it is not set, we set cookies to the originally requested domain.
	 * If it is set as auto, we find the root domain of the request and use that for
	 * cookies. If it is set and not explicitly defined as 'auto', we assume that a
	 * cookie domain is being specified, similar to how you would do when using
	 * COOKIE_DOMAIN originally.
	 */
	if ( defined( 'WSUWP_COOKIE_DOMAIN' ) && 'auto' === WSUWP_COOKIE_DOMAIN ) {
		$requested_domain_parts = explode( '.', $requested_domain );
		$wsuwp_cookie_domain = array_pop( $requested_domain_parts );
		$wsuwp_cookie_domain = array_pop( $requested_domain_parts ) . '.' . $wsuwp_cookie_domain;
	} elseif ( defined( 'WSUWP_COOKIE_DOMAIN' ) ) {
		$wsuwp_cookie_domain = WSUWP_COOKIE_DOMAIN;
	} else {
		$wsuwp_cookie_domain = $requested_domain;
	}

	define( 'COOKIE_DOMAIN', $wsuwp_cookie_domain );
} else {
	/**
	 * If we've made it here, the domain and path provided aren't doing us much good. At this
	 * point, we're okay to forget about the path and focus on best effort for the domain. Our
	 * first bet is to drop off the first part of the domain to see if it really is a subdomain
	 * request.
	 */
	$redirect_domain_parts = explode( '.', $requested_domain );
	array_shift( $redirect_domain_parts );
	$redirect_domain = implode( '.', $redirect_domain_parts );

	// Check to see if this redirect domain is a site that we can handle
	$redirect_site_id = $wpdb->get_var( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s", $redirect_domain ) );

	/** @todo think about sanitizing this properly as esc_url() and wp_redirect() are not available yet */
	if ( $redirect_site_id ) {
		header( 'Location: http://' . $redirect_domain, true, 301 );
	} else {
		header( 'Location: http://wp.wsu.edu/', true, 301 );
	}

	die();
}
