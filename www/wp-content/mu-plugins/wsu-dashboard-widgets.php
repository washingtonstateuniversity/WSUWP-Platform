<?php
/*
Plugin Name: WSUWP Dashboard
Plugin URI: http://web.wsu.edu/
Description: Modifications to the WordPress Dashboard.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

class WSUWP_WordPress_Dashboard {

	/**
	 * Add our hooks.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
		add_action( 'wp_network_dashboard_setup', array( $this, 'remove_network_dashboard_widgets' ) );
		add_filter( 'update_footer', array( $this, 'update_footer_text' ), 11 );
	}

	/**
	 * Remove all of the dashboard widgets and panels when a user logs
	 * in except for the Right Now area.
	 */
	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_incoming_links' , 'dashboard', 'normal' );
		remove_meta_box( 'tribe_dashboard_widget'   , 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins'        , 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary'        , 'dashboard', 'side'   );
		remove_meta_box( 'dashboard_secondary'      , 'dashboard', 'side'   );
		remove_meta_box( 'dashboard_quick_press'    , 'dashboard', 'side'   );
		remove_meta_box( 'dashboard_recent_drafts'  , 'dashboard', 'side'   );

		remove_action( 'welcome_panel', 'wp_welcome_panel' );
	}

	/**
	 * Remove all default widgets from the network dashboard.
	 */
	public function remove_network_dashboard_widgets() {
		remove_meta_box( 'network_dashboard_right_now', 'dashboard-network', 'normal' );
		remove_meta_box( 'dashboard_plugins'          , 'dashboard-network', 'normal' );
		remove_meta_box( 'dashboard_primary'          , 'dashboard-network', 'side'   );
		remove_meta_box( 'dashboard_secondary'        , 'dashboard-network', 'side'   );
	}

	/**
	 * Customize the update footer text a bit.
	 *
	 * @param $text
	 *
	 * @return mixed|string
	 */
	public function update_footer_text( $text ) {
		global $wsuwp_global_version, $wsuwp_wp_changeset;

		$version_text = explode( ' ', $text );
		$version = explode( '-', $version_text[1] );

		$text = 'WSUWP Platform <a target=_blank href="https://github.com/washingtonstateuniversity/WSUWP-Platform/tree/v' . $wsuwp_global_version . '">' . $wsuwp_global_version . '</a> | ';
		$text .= 'WordPress ' . $version[0];

		if ( isset( $version[1] ) ) {
			$text .= ' ' . ucwords( $version[1] );
		}

		$text .= ' [<a target=_blank href="https://core.trac.wordpress.org/changeset/' . $wsuwp_wp_changeset . '">' . $wsuwp_wp_changeset . '</a>]';

		return $text;
	}
}
$wsuwp_wordpress_dashboard = new WSUWP_WordPress_Dashboard();