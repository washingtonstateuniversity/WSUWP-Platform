<?php
/*
Plugin Name: WSU Network Admin
Plugin URI: http://web.wsu.edu/
Description: Modifications to handle multiple networks in WordPress.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

/**
 * Class WSU_Network_Admin
 *
 * Adds various WSU customizations to handle networks in WordPress
 */
class WSU_Network_Admin {

	/**
	 * Add the filters and actions used
	 */
	public function __construct() {
		add_filter( 'parent_file',                       array( $this, 'add_master_network_menu'    ), 10, 1 );
		add_action( 'admin_menu',                        array( $this, 'my_networks_dashboard'      ),  1    );
		add_filter( 'wpmu_validate_user_signup',         array( $this, 'validate_user_signup'       ), 10, 1 );
		add_filter( 'plugin_action_links',               array( $this, 'remove_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'remove_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'plugin_action_links'        ), 15, 2 );
		add_action( 'activate_plugin',                   array( $this, 'activate_global_plugin'     ), 10, 1 );
		add_filter( 'views_plugins-network',             array( $this, 'add_plugin_table_views',    ), 10, 1 );
		add_filter( 'all_plugins',                       array( $this, 'all_plugins',               ), 10, 1 );
	}

	/**
	 * Modify the results of a request for all plugins.
	 *
	 * @param array $plugins Current list of installed plugins.
	 *
	 * @return array Modified list of installed plugins.
	 */
	public function all_plugins( $plugins ) {
		if ( ! is_multi_network() || is_main_network() )
			return $plugins;

		$global_plugins = wp_get_active_global_plugins();
		foreach( $plugins as $k => $v ) {
			if ( isset( $global_plugins[ $k ] ) )
				unset( $plugins[ $k ] );
		}
		return $plugins;
	}

	/**
	 * Modify the view links displayed at the top of the plugins list table.
	 *
	 * @param array $views List of view links for the plugin list table.
	 *
	 * @return array Modified list of view links.
	 */
	public function add_plugin_table_views( $views ) {
		if ( ! is_main_network() )
			return $views;

		$global_plugins = wp_get_active_global_plugins();

		$count = count( $global_plugins );
		$url = add_query_arg('plugin_status', 'global', 'plugins.php');
		$views['global'] = '<a href="' . $url . '">Global <span class="count">(' . $count . ')</span></a>';

		return $views;
	}

	/**
	 * Remove the edit and delete action links from plugin displays.
	 *
	 * @param array $actions Action links to display under each plugin.
	 *
	 * @return array Modified list of action links.
	 */
	public function remove_plugin_action_links( $actions ) {
		unset( $actions['edit'] );
		unset( $actions['delete'] );

		return $actions;
	}

	/**
	 * Modify the plugin action links with our custom functionality
	 *
	 * @param array  $actions     Current assigned actions and links.
	 * @param string $plugin_file The plugin file associated with the action.
	 * @param array  $plugin_data Information about the plugin from the header.
	 *
	 * @return array The resulting array of actions and links assigned to the plugin.
	 */
	public function plugin_action_links( $actions, $plugin_file ) {
		// If this is not the main network, our requirements differ slightly.
		if ( ! is_main_network() && is_multi_network() ) {
			// If the plugin is globally activated, remove standard options at the network level.
			if ( is_plugin_active_for_global( $plugin_file ) ) {
				unset( $actions['deactivate'] );
				unset( $actions['activate'] );
				$actions['global'] = 'Activated Globally';
			}
			return $actions;
		} elseif( is_main_network() && is_plugin_active_for_global( $plugin_file ) ) {
			unset( $actions['deactivate'] );
			unset( $actions['activate'] );
		}

		if ( ! is_plugin_active_for_global( $plugin_file ) )
			$actions['global'] = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;wsu-activate-global=1&amp;plugin=' . $plugin_file, 'activate-plugin_' . $plugin_file) . '" title="Activate this plugin for all sites on all networks" class="edit">Activate Globally</a>';
		else
			$actions['global'] = '<a href="">Deactivate Globally</a>';

		return $actions;
	}

	/**
	 * Activate a plugin globally on all sites in all networks.
	 *
	 * @param string $plugin Slug of plugin to be activated.
	 *
	 * @return null
	 */
	public function activate_global_plugin( $plugin ) {

		if ( ! isset( $_GET['wsu-activate-global'] ) || ! is_main_network() )
			return null;

		activate_global_plugin( $plugin );
	}

	/**
	 * Temporarily override user validation in anticpation of ticket #17904. In reality, we'll
	 * be doing all of our authentication through active directory, so this won't be necessary,
	 * but it does come in useful during initial testing.
	 *
	 * @param array $result Existing result from the wpmu_validate_user_signup() process
	 *
	 * @return array New results of our own validation
	 */
	public function validate_user_signup( $result ) {
		global $wpdb;

		$user_login = $result['user_name'];
		$original_user_login = $user_login;
		$result = array();
		$result['errors'] = new WP_Error();

		// User login cannot be empty
		if( empty( $user_login ) )
			$result['errors']->add( 'user_name', __( 'Please enter a username.' ) );

		// User login must be at least 4 characters
		if ( strlen( $user_login ) < 4 )
			$result['errors']->add( 'user_name',  __( 'Username must be at least 4 characters.' ) );

		// Strip any whitespace and then match against case insensitive characters a-z 0-9 _ . - @
		$user_login = preg_replace( '/\s+/', '', sanitize_user( $user_login, true ) );

		// If the previous operation generated a different value, the username is invalid
		if ( $user_login !== $original_user_login )
			$result['errors']->add( 'user_name', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );

		// Check the user_login against an array of illegal names
		$illegal_names = get_site_option( 'illegal_names' );
		if ( false == is_array( $illegal_names ) ) {
			$illegal_names = array(  'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );
			add_site_option( 'illegal_names', $illegal_names );
		}

		if ( true === in_array( $user_login, $illegal_names ) )
			$result['errors']->add( 'user_name',  __( 'That username is not allowed.' ) );

		// User login must have at least one letter
		if ( preg_match( '/^[0-9]*$/', $user_login ) )
			$result['errors']->add( 'user_name', __( 'Sorry, usernames must have letters too!' ) );

		// Check if the username has been used already.
		if ( username_exists( $user_login ) )
			$result['errors']->add( 'user_name', __( 'Sorry, that username already exists!' ) );

		if ( is_multisite() ) {
			// Is a signup already pending for this user login?
			$signup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->signups WHERE user_login = %s ", $user_login ) );
			if ( $signup != null ) {
				$registered_at =  mysql2date( 'U', $signup->registered );
				$now = current_time( 'timestamp', true );
				$diff = $now - $registered_at;
				// If registered more than two days ago, cancel registration and let this signup go through.
				if ( $diff > 2 * DAY_IN_SECONDS )
					$wpdb->delete( $wpdb->signups, array( 'user_login' => $user_login ) );
				else
					$result['errors']->add( 'user_name', __( 'That username is currently reserved but may be available in a couple of days.' ) );
			}
		}

		$result['user_login']          = $user_login;
		$result['original_user_login'] = $original_user_login;

		return $result;
	}

	/**
	 * Add a top level menu item for 'Networks' to the network administration sidebar
	 */
	function add_master_network_menu() {
		if ( is_network_admin() ) {
			global $menu;
			$menu[6] = $menu[5];
			unset( $menu[5] );
			$menu[6][4] = 'menu-top menu-icon-site';
			$menu[5] = array(
				'Networks',
				'manage_networks',
				'networks.php',
				'',
				'menu-top menu-icon-site menu-top-first',
				'menu-site',
				'div',
			);
			ksort( $menu );
		}
	}

	/**
	 * Add a dashboard page to manage all WSU Networks that a user has access to
	 */
	function my_networks_dashboard() {
		add_dashboard_page( 'My Networks Dashboard', 'My WSU Networks', 'read', 'my-wsu-networks', array( $this, 'display_my_networks' ) );
	}

	/**
	 * Output the dashboard page for WSU Networks
	 */
	function display_my_networks() {
		?>
		<div class="wrap">
			<?php screen_icon( 'ms-admin' ); ?>
		<h2>My Networks<?php
	}

}
new WSU_Network_Admin();