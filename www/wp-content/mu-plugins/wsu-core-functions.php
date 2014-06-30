<?php
/*
 * Plugin Name: WSU Core Functions
 * Plugin URI: http://web.wsu.edu
 * Description: Functions that perform some core functionality that we would love to live inside of WordPress one day.
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: http://web.wsu.edu
 * Version: 0.1
 * Network: true
 */

/**
 * Retrieve a list of sites that with which the passed user has associated capabilities.
 *
 * @param int $user_id ID of the user
 * @param bool $all False to return all sites. True will return only those not marked as archived, spam, or deleted
 *
 * @return array A list of user's sites. An empty array of the user does not have any capabilities to any sites
 */
function wsuwp_get_user_sites( $user_id, $all = false ) {
	return get_blogs_of_user( $user_id, $all );
}

/**
 * Return a list of networks that the user is a member of.
 *
 * @uses wsuwp_get_networks
 * @param null $user_id Optional. Defaults to the current user.
 *
 * @return array containing list of user's networks
 */
function wsuwp_get_user_networks( $user_id = null ) {
	/* @var WPDB $wpdb */
	global $wpdb;

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Global admins should see every network.
	if ( is_super_admin() ) {
		return wsuwp_get_networks();
	}

	$user_id = absint( $user_id );
	$network_keys = $wpdb->get_col( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = $user_id AND meta_key LIKE 'wsuwp_network_%_capabilities'" );

	$user_network_ids = array();

	foreach( $network_keys as $network_key ) {
		$network_id = explode( '_', $network_key );
		if ( isset( $network_id[2] ) ) {
			$user_network_ids[] = absint( $network_id[2] );
		}
	}

	return wsuwp_get_networks( array( 'network_id' => $user_network_ids ) );
}

/**
 * A wrapper with a better name for get_current_site(). Returns what WordPress knows
 * as the current site, which in reality is the current network.
 *
 * @return object with current network information
 */
function wsuwp_get_current_network() {
	return get_current_site();
}

/**
 * A wrapper with a better name for get_blog_details(). Returns what WordPress knows
 * as the current blog (by not passing any arguments), which in reality is the
 * current site.
 *
 * @return object with current site information
 */
function wsuwp_get_current_site() {
	return get_blog_details();
}

/**
 * Switch to another network by backing up the $current_site global so that we can run
 * various queries and functions while impersonating it.
 *
 * The resulting $current_site global will need to include properties for:
 *     - id
 *     - domain
 *     - path
 *     - blog_id
 *     - site_name
 *     - cookie_domain (?)
 *
 * @param int $network_id Network ID to switch to.
 *
 * @return bool
 */
function wsuwp_switch_to_network( $network_id ) {
	/** @type WPDB $wpdb */
	global $current_site, $wpdb;

	if ( ! $network_id ) {
		return false;
	}

	// Create a backup of $current_site in the global scope.
	$GLOBALS['_wsuwp_switched_stack'][] = array( 'network' => $current_site, 'blog_id' => $wpdb->blogid, 'site_id' => $wpdb->siteid );

	$new_network = wsuwp_get_networks( array( 'network_id' => $network_id ) );
	$current_site = array_shift( $new_network );
	$current_site->blog_id = $wpdb->get_var( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s", $current_site->domain, $current_site->path ) );
	$wpdb->set_blog_id( $current_site->blog_id, $current_site->id );

	return true;
}

/**
 * Restore the last network that was in use before switching.
 *
 * @return bool False if there were no networks to switch back to. True if a stack was available.
 */
function wsuwp_restore_current_network() {
	/** @type WPDB $wpdb */
	global $current_site, $wpdb;

	if ( empty( $GLOBALS['_wsuwp_switched_stack'] ) ) {
		return false;
	}

	$network_data = array_pop( $GLOBALS['_wsuwp_switched_stack'] );

	$current_site = $network_data['network'];
	$wpdb->set_blog_id( $network_data['blog_id'], $network_data['site_id'] );

	return true;
}

/**
 * Wrapper function for the WordPress switch_to_blog() intended to better match the
 * name of what we're doing in the backend vs the frontend
 *
 * @param int $site_id ID of the site to switch to
 *
 * @return bool True on success, false if the validation failed
 */
function wsuwp_switch_to_site( $site_id ) {
	return switch_to_blog( $site_id );
}

/**
 * Used after wsuwp_switch_to_site(), this is a wrapper for restore_current_blog() that gets
 * us back to the current site
 *
 * @return bool True on success, false if we're already on the current blog
 */
function wsuwp_restore_current_site() {
	return restore_current_blog();
}

/**
 * Checks to see if there is more than one network defined in the site table
 *
 * @return bool
 */
function wsuwp_is_multi_network() {
	global $wpdb;

	if ( ! is_multisite() ) {
		return false;
	}

	if ( defined( 'WP_MULTI_NETWORK' ) && true === WP_MULTI_NETWORK ) {
		return true;
	}

	if ( false === ( $is_multi_network = get_transient( 'wsuwp_is_multi_network' ) ) ) {
		$rows = (array) $wpdb->get_col("SELECT DISTINCT id FROM $wpdb->site LIMIT 2");
		$is_multi_network = 1 < count( $rows ) ? 1 : 0;
		set_transient( 'wsuwp_is_multi_network', $is_multi_network );
	}

	return apply_filters( 'wsuwp_is_multi_network', (bool) $is_multi_network );
}

/**
 * Get an array of data on requested networks
 *
 * @param array $args Optional.
 *     - 'network_id' a single network ID or an array of network IDs
 *
 * @return array containing network data
 */
function wsuwp_get_networks( $args = array() ) {
	global $wpdb;

	if ( ! is_multisite() ) {
		return array();
	}

	$network_results = (array) $wpdb->get_results( "SELECT * FROM $wpdb->site" );

	if ( isset( $args['network_id'] ) ) {
		$network_id = (array) $args['network_id'];
		foreach( $network_results as $key => $network ) {
			if ( ! in_array( $network->id, $network_id ) ) {
				unset( $network_results[ $key ] );
			}
		}
	}

	return array_values( $network_results );
}

function wsuwp_create_network( $args ) {
	/** @type WPDB $wpdb */
	global $wpdb;

	$errors = new WP_Error();

	$default = array(
		'site_id'           => null,
		'network_name'      => '',
		'user_id'           => get_current_user_id(),
		'domain'            => '',
		'path'              => '/',
		'email'             => '',
		'subdomain_install' => false,
	);
	$args = wp_parse_args( $args, $default );

	if ( '' === trim( $args['domain'] ) )
		$errors->add( 'empty_domain', __( 'You must provide a domain name.' ) );

	if ( '' === trim( $args['network_name'] ) )
		$errors->add( 'empty_sitename', __( 'You must provide a name for your network of sites.' ) );

	if ( ! $site_user = get_user_by( 'id', $args['user_id'] ) )
		$errors->add( 'invalid_user', __( 'You must provide a valid user to be set as network admin.' ) );

	if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->site WHERE domain = %s AND path = %s", $args['domain'], $args['path'] ) ) )
		$errors->add( 'network_exists', __( 'The network already exists.' ) );

	if ( $errors->get_error_codes() )
		return $errors;

	// Get the main site's values for template and stylesheet
	// @todo this can use get_site_option() to duplicate the efforts of another network
	$template   = sanitize_key( get_option( 'template'   ) );
	$stylesheet = sanitize_key( get_option( 'stylesheet' ) );

	$allowed_themes = array( $stylesheet => true );
	if ( $template !== $stylesheet )
		$allowed_themes[ $template ] = true;
	if ( WP_DEFAULT_THEME !== $stylesheet && WP_DEFAULT_THEME !== $template )
		$allowed_themes[ WP_DEFAULT_THEME ] = true;

	// @todo think about grabbing active plugins here as well

	$wpdb->insert( $wpdb->site, array( 'domain' => $args['domain'], 'path' => $args['path'] ) );
	$network_id = $wpdb->insert_id;

	// Assume the current network's admins will have access
	$network_admins = get_site_option( 'site_admins' );

	$network_meta = array(
		'site_name'         => $args['network_name'],
		'admin_email'       => $site_user->user_email,
		'admin_user_id'     => $site_user->ID,
		'site_admins'       => $network_admins,
		'allowedthemes'     => $allowed_themes,
		'subdomain_install' => intval( $args['subdomain_install'] ),
	);
	wsuwp_populate_network_meta( $network_id, $network_meta );

	return $network_id; // maybe even a network object
}

function wsuwp_populate_network_meta( $network_id, $network_meta ) {
	/** @type WPDB $wpdb */
	global $wpdb, $wp_db_version;

	$welcome_email = __( 'Dear User,

Your new SITE_NAME site has been successfully set up at:
BLOG_URL

You can log in to the administrator account with the following information:
Username: USERNAME
Password: PASSWORD
Log in here: BLOG_URLwp-login.php

We hope you enjoy your new site. Thanks!

--The Team @ SITE_NAME' );

	$defaults = array(
		'site_name' => null,
		'admin_email' => null,
		'admin_user_id' => null,
		'registration' => 'none',
		'upload_filetypes' => 'jpg jpeg png gif mp3 mov avi wmv pdf ai psd eps doc xls zip',
		'blog_upload_space' => 100,
		'fileupload_maxk' => 50000,
		'illegal_names' => array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator', 'files' ),
		'wpmu_upgrade_site' => $wp_db_version,
		'welcome_email' => $welcome_email,
		'first_post' => __( 'Welcome to <a href="SITE_URL">SITE_NAME</a>. This is your first post. Edit or delete it, then start blogging!' ),
		// @todo - network admins should have a method of editing the network siteurl (used for cookie hash)
		'siteurl' => get_option( 'siteurl' ) . '/',
		'add_new_users' => '0',
		'upload_space_check_disabled' => is_multisite() ? get_site_option( 'upload_space_check_disabled' ) : '1',
		'global_terms_enabled' => global_terms_enabled() ? '1' : '0',
		'ms_files_rewriting' => is_multisite() ? get_site_option( 'ms_files_rewriting' ) : '0',
		'initial_db_version' => get_option( 'initial_db_version' ),
		'active_sitewide_plugins' => array(),
		'WPLANG' => get_locale(),
	);
	if ( 0 == $network_meta['subdomain_install'] ) {
		$defaults['illegal_names'][] = 'blog';
	}

	$network_meta = wp_parse_args( $network_meta, $defaults );
	/**
	 * Filter meta for a network on creation.
	 *
	 * @since 3.7.0
	 *
	 * @param array $network_meta Associative of meta keys and values to be inserted.
	 * @param int $network_id Network ID being created.
	 */
	$network_meta = apply_filters( 'populate_network_meta', $network_meta, $network_id );

	$insert = '';
	foreach( $network_meta as $meta_key => $meta_value ) {
		if ( is_array( $meta_value ) ) {
			$meta_value = serialize( $meta_value );
		}

		if ( ! empty( $insert ) ) {
			$insert .= ', ';
		}

		$insert .= $wpdb->prepare( "( %d, %s, %s )", $network_id, $meta_key, $meta_value );
	}
	$wpdb->query( "INSERT INTO $wpdb->sitemeta ( site_id, meta_key, meta_value ) VALUES " . $insert );
}

/**
 * Activate a plugin globally on all sites in all networks.
 *
 * @param string $plugin Slug of the plugin to be activated.
 */
function wsuwp_activate_global_plugin( $plugin ) {
	$networks = wsuwp_get_networks();
	foreach ( $networks as $network ) {
		wsuwp_switch_to_network( $network->id );
		$current = get_site_option( 'active_sitewide_plugins', array() );
		$current[ $plugin ] = time();
		update_site_option( 'active_sitewide_plugins', $current );
		wsuwp_restore_current_network();
	}

	wsuwp_switch_to_network( wsuwp_get_primary_network_id() );
	$current_global = get_site_option( 'active_global_plugins', array() );
	$current_global[ $plugin ] = time();
	update_site_option( 'active_global_plugins', $current_global );
	wsuwp_restore_current_network();
}

/**
 * Determine if a plugin has been activated globally.
 *
 * @param $plugin String representing the plugin.
 *
 * @return bool True if plugin is globally activated. False if not.
 */
function wsuwp_is_plugin_active_for_global( $plugin ) {
	if ( ! wsuwp_is_multi_network() )
		return false;

	$current_global = wsuwp_get_active_global_plugins();

	if ( isset( $current_global[ $plugin ] ) )
		return true;

	return false;
}

/**
 * Retrieve an array of globally activated plugins.
 *
 * @return bool|array Current globally activated plugins.
 */
function wsuwp_get_active_global_plugins() {
	if ( ! wsuwp_is_multi_network() )
		return false;

	wsuwp_switch_to_network( wsuwp_get_primary_network_id() );
	$current_global = get_site_option( 'active_global_plugins', array() );
	wsuwp_restore_current_network();

	return $current_global;
}

/**
 * Retrieve the primary network id.
 *
 * If a multinetwork setup, retrieve the primary network ID. If a multisite
 * setup, return 1. If a standard WordPress installation, return 1.
 *
 * @return int The primary network id.
 */
function wsuwp_get_primary_network_id() {
	global $current_site, $wpdb;

	$current_network_id = (int) $current_site->id;

	if ( ! is_multisite() || ! wsuwp_is_multi_network() )
		return 1;

	if ( defined( 'PRIMARY_NETWORK_ID' ) )
		return PRIMARY_NETWORK_ID;

	if ( 1 === $current_network_id )
		return 1;

	$primary_network_id = (int) wp_cache_get( 'primary_network_id', 'site-options' );

	if ( $primary_network_id )
		return $primary_network_id;

	$primary_network_id = (int) $wpdb->get_var( "SELECT id FROM $wpdb->site ORDER BY id LIMIT 1" );
	wp_cache_add( 'primary_network_id', $primary_network_id, 'site-options' );

	return $primary_network_id;
}

/**
 * Validate a domain against a set of allowed characters.
 *
 * Allowed characters are a-z, A-Z, 0-9, -, and .
 *
 * @param string $domain Domain to validate
 *
 * @return bool True if valid, false if not.
 */
function wsuwp_validate_domain( $domain ) {
	if ( preg_match( '|^([a-zA-Z0-9-.])+$|', $domain ) ) {
		return true;
	}

	return false;
}

/**
 * Validate a path against a set of allowed characters.
 *
 * Allowed characters are a-z, A-Z, 0-9, -, ., and /
 *
 * @param string $path Path to validate.
 *
 * @return bool True if valid. False if not.
 */
function wsuwp_validate_path( $path ) {
	if ( preg_match( '|^([a-zA-Z0-9-/])+$|', $path ) ) {
		return true;
	}

	return false;
}

/**
 * Retrieve a count of the total number of networks.
 *
 * @return int The number of networks currently configured.
 */
function wsuwp_network_count() {
	$network_count = count( wsuwp_get_networks() );
	return $network_count;
}

/**
 * Retrieve the total number of users configured in the global environment
 * of all networks.
 *
 * This value is updated twice a day through a cron event and while seemingly
 * attached to a network's "site option", is populated by a query that
 * encompasses the entire user table.
 *
 * @return int The number of global users.
 */
function wsuwp_global_user_count() {
	return absint( get_site_option( 'user_count' ) );
}

/**
 * Retrieve a count of users on a specific network.
 *
 * This data will be generated every 30 minutes. Generation is initiated via
 * an attempt to use the function.
 *
 * @param int $network_id Network ID
 *
 * @return int Count of users on the network
 */
function wsuwp_network_user_count( $network_id = 0 ) {
	global $wpdb;

	if ( 0 === $network_id ) {
		$network_id = wsuwp_get_current_network()->id;
	}

	wsuwp_switch_to_network( $network_id );
	$network_user_data = get_site_option( 'network_user_data', array( 'count' => 0, 'updated' => 0 ) );

	if ( empty( $network_user_data['count'] ) || empty( $network_user_data['updated'] ) || ( time() - 1800 ) > absint( $network_user_data['updated'] ) ) {
		$network_key = 'wsuwp_network_' . absint( $network_id ) . '_capabilities';
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(user_id) as c FROM $wpdb->usermeta WHERE meta_key = %s ", $network_key ) );
		$network_user_data = array( 'count' => absint( $count ), 'updated' => time() );
		update_site_option( 'network_user_data', $network_user_data );
	}
	wsuwp_restore_current_network();

	return absint( $network_user_data['count'] );
}

/**
 * Retrieve a count of sites on the global platform of networks.
 *
 * @return int Count of sites on the global platform.
 */
function wsuwp_global_site_count() {
	global $wpdb;

	wsuwp_switch_to_network( wsuwp_get_primary_network_id() );
	$global_site_data = get_site_option( 'global_site_data', array( 'count' => 0, 'updated' => 0 ) );

	if ( empty( $global_site_data['count'] ) || empty( $global_site_data['updated'] ) || ( time() - 1800 ) > absint( $global_site_data['updated'] ) ) {
		$count = $wpdb->get_var( "SELECT COUNT(blog_id) as c FROM $wpdb->blogs WHERE spam = '0' AND deleted = '0' and archived = '0'" );
		$global_site_data = array( 'count' => absint( $count ), 'updated' => time() );
		update_site_option( 'global_site_data', $global_site_data );
	}
	wsuwp_restore_current_network();

	return absint( $global_site_data['count'] );
}