<?php
/*
Plugin Name: WSU SSL
Plugin URI: http://web.wsu.edu/
Description: Manage various bits around SSL on the WSUWP Platform
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

class WSU_SSL {

	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'wpmu_new_blog', array( $this, 'determine_new_site_ssl' ), 10, 3 );
	}

	/**
	 * Determine if a new site should be flagged for SSL configuration.
	 *
	 * If this domain has already been added for another site, we'll assume the SSL status
	 * of that configuration and allow it to play out. If this is the first time for this
	 * domain, then we should flag it as SSL disabled.
	 *
	 * @param $blog_id
	 * @param $user_id
	 * @param $domain
	 */
	public function determine_new_site_ssl( $blog_id, $user_id, $domain ) {
		/* @type WPDB $wpdb */
		global $wpdb;

		$domain_exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE domain = %s LIMIT 1 AND blog_id != %d ", $domain, $blog_id ) );

		if ( ! $domain_exists ) {
			switch_to_blog( 1 );
			update_option( $domain . '_ssl_disabled', 1 );
			restore_current_blog();
		}
	}
}
new WSU_SSL();