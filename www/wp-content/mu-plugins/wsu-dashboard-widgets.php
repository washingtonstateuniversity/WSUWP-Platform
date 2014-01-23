<?php
/*
Plugin Name: WSU Remove Dashboard Widgets
Plugin URI: http://web.wsu.edu/
Description: Removes parts of the WordPress dashboard that WSU does not need.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

add_action( 'wp_dashboard_setup', 'wsu_remove_dashboard_widgets' );
/**
 * Remove all of the dashboard widgets and panels when a user logs
 * in except for the Right Now area.
 */
function wsu_remove_dashboard_widgets() {
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

add_action( 'wp_network_dashboard_setup', 'wsu_remove_network_dashboard_widgets' );
function wsu_remove_network_dashboard_widgets() {
	remove_meta_box( 'network_dashboard_right_now', 'dashboard-network', 'normal' );
	remove_meta_box( 'dashboard_plugins'          , 'dashboard-network', 'normal' );
	remove_meta_box( 'dashboard_primary'          , 'dashboard-network', 'side'   );
	remove_meta_box( 'dashboard_secondary'        , 'dashboard-network', 'side'   );
}