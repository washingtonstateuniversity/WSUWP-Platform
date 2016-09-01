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
	 * Network meta fields to allow editing access too in Edit Network.
	 *
	 * @var array Network meta fields and properties.
	 */
	private $network_meta_edit = array(
		'site_name' => array(
			'label'    => 'Network Name:',
			'input'    => 'text',
			'validate' => 'sanitize_text_field',
		),
		'welcome_email' => array(
			'label'    => 'Welcome Email:',
			'input'    => 'textarea',
			'rows'     => 15,
			'validate' => 'wp_kses_data',
		),
		'siteurl' => array(
			'label'    => 'Site URL:',
			'input'    => 'text',
			'validate' => 'esc_url_raw',
		),
	);

	/**
	 * These options are normally accessed via get_site_option()
	 *
	 * @var array List of network options.
	 */
	private $global_network_options = array(
		'fileupload_maxk' => 200000,
		'blog_upload_space' => 2000,
		'upload_filetypes' => 'jpg jpeg png gif mp3 webp oga ogg ogv webm mp4 pdf ai psd eps doc ppt xls csv key numbers pages dmg zip txt mat',
		'add_new_users' => 1,
		'registrationnotification' => 'no',
		'registration' => 'none',
		'upload_space_check_disabled' => 1,
	);

	/**
	 * Add the filters and actions used
	 */
	public function __construct() {
		add_action( 'load-sites.php',                    array( $this, 'networks_php' ), 10, 1 );
		// Load at 9 for now until we sort our conflict with wsu-network-site-new.php
		add_action( 'load-site-new.php',                 array( $this, 'network_new_php' ),  9, 1 );
		add_action( 'load-site-info.php',                array( $this, 'network_info_php' ), 10, 1 );
		add_filter( 'parent_file',                       array( $this, 'add_master_network_menu' ), 10, 1 );
		add_action( 'admin_menu',                        array( $this, 'my_networks_dashboard' ),  1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'my_networks_dashboard_scripts' ), 10 );
		add_filter( 'wpmu_validate_user_signup',         array( $this, 'validate_user_signup' ), 10, 1 );
		add_filter( 'plugin_action_links',               array( $this, 'remove_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'remove_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'plugin_action_links' ), 15, 2 );
		add_action( 'activate_plugin',                   array( $this, 'activate_global_plugin' ), 10, 1 );
		add_action( 'deactivate_plugin', array( $this, 'deactivate_global_plugin' ), 10, 1 );
		add_filter( 'views_plugins-network',             array( $this, 'add_plugin_table_views' ), 10, 1 );
		add_filter( 'all_plugins',                       array( $this, 'all_plugins' ), 10, 1 );
		add_filter( 'parent_file',                       array( $this, 'parent_file' ), 10, 1 );

		add_filter( 'pre_site_option_fileupload_maxk', array( $this, 'set_fileupload_maxk' ), 10, 1 );
		add_filter( 'pre_site_option_blog_upload_space', array( $this, 'set_blog_upload_space' ), 10, 1 );

		add_filter( 'pre_site_option_upload_filetypes', array( $this, 'set_upload_filetypes' ), 10, 1 );
		add_filter( 'upload_mimes', array( $this, 'set_mime_types' ), 10, 1 );

		add_filter( 'pre_site_option_add_new_users', array( $this, 'set_add_new_users' ), 10, 1 );
		add_filter( 'pre_site_option_registrationnotification', array( $this, 'set_registrationnotification' ), 10, 1 );
		add_filter( 'pre_site_option_registration', array( $this, 'set_registration' ), 10, 1 );
		add_filter( 'pre_site_option_upload_space_check_disabled', array( $this, 'set_upload_space_check_disabled' ), 10, 1 );

		add_action( 'admin_init', array( $this, 'remove_upgrade_notices' ) );

		add_action( 'refresh_blog_details', array( $this, 'clear_site_request_cache' ) );
		add_action( 'delete_blog', array( $this, 'clear_site_request_cache' ) );

		add_filter( 'pre_update_site_option_user_count', array( $this, 'update_network_user_count' ) );
	}

	/**
	 * Retrieve the current, filtered list of network options that are provided on a global level.
	 *
	 * We parse this against the default list of arguments after filtering so that we can assume
	 * the defaults are available to us at a later time.
	 *
	 * @return array List of default network options.
	 */
	public function get_global_network_options() {
		$global_network_options = apply_filters( 'wsuwp_global_network_options', $this->global_network_options );

		return wp_parse_args( $global_network_options, $this->global_network_options );
	}

	/**
	 * Modify the results of a request for all plugins.
	 *
	 * @param array $plugins Current list of installed plugins.
	 *
	 * @return array Modified list of installed plugins.
	 */
	public function all_plugins( $plugins ) {
		if ( ! wsuwp_is_multi_network() || is_main_network() ) {
			return $plugins;
		}

		$global_plugins = wsuwp_get_active_global_plugins();
		foreach ( $plugins as $k => $v ) {
			if ( isset( $global_plugins[ $k ] ) ) {
				unset( $plugins[ $k ] );
			}
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
		if ( ! is_main_network() ) {
			return $views;
		}

		$global_plugins = wsuwp_get_active_global_plugins();

		$count = count( $global_plugins );
		$url = add_query_arg( 'plugin_status', 'global', 'plugins.php' );
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
	 *
	 * @return array The resulting array of actions and links assigned to the plugin.
	 */
	public function plugin_action_links( $actions, $plugin_file ) {
		// If this is not the main network, our requirements differ slightly.
		if ( ! is_main_network() && wsuwp_is_multi_network() ) {
			// If the plugin is globally activated, remove standard options at the network level.
			if ( wsuwp_is_plugin_active_for_global( $plugin_file ) ) {
				unset( $actions['deactivate'] );
				unset( $actions['activate'] );
				$actions['global'] = 'Activated Globally';
			}
			return $actions;
		} elseif ( is_main_network() && wsuwp_is_plugin_active_for_global( $plugin_file ) ) {
			unset( $actions['deactivate'] );
			unset( $actions['activate'] );
		}

		if ( ! wsuwp_is_plugin_active_for_global( $plugin_file ) ) {
			$actions['global'] = '<a href="' . wp_nonce_url( 'plugins.php?action=activate&amp;wsu-activate-global=1&amp;plugin=' . $plugin_file, 'activate-plugin_' . $plugin_file ) . '" title="Activate this plugin for all sites on all networks" class="edit">Activate Globally</a>';
		} else {
			$actions['global'] = '<a href="' . wp_nonce_url( 'plugins.php?action=deactivate&amp;wsu-deactivate-global=1&amp;plugin=' . $plugin_file, 'deactivate-plugin_' . $plugin_file ) . '" title="Deactivate this plugin for all sites on all networks" class="edit">Deactivate Globally</a>';
		}

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

		if ( ! isset( $_GET['wsu-activate-global'] ) || ! is_main_network() ) {
			return null;
		}

		wsuwp_activate_global_plugin( $plugin );
	}

	/**
	 * Deactivate a plugin globally on all sites in all networks.
	 * @param $plugin
	 *
	 * @return null
	 */
	public function deactivate_global_plugin( $plugin ) {
		if ( ! isset( $_GET['wsu-deactivate-global'] ) || ! is_main_network() ) {
			return null;
		}

		wsuwp_deactivate_global_plugin( $plugin );
	}

	/**
	 * Temporarily override user validation in anticipation of ticket #17904. In reality, we'll
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
		$user_email = $result['user_email'];

		$result = array();
		$result['errors'] = new WP_Error();

		// User login cannot be empty
		if ( empty( $user_login ) ) {
			$result['errors']->add( 'user_name', __( 'Please enter a username.' ) );
		}

		// User login must be at least 3 characters
		if ( strlen( $user_login ) < 3 ) {
			$result['errors']->add( 'user_name',  __( 'Username must be at least 3 characters.' ) );
		}

		// Strip any whitespace and then match against case insensitive characters a-z 0-9 _ . - @
		$user_login = preg_replace( '/\s+/', '', sanitize_user( $user_login, true ) );

		// If the previous operation generated a different value, the username is invalid
		if ( $user_login !== $original_user_login ) {
			$result['errors']->add( 'user_name', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
		}

		// Check the user_login against an array of illegal names
		$illegal_names = get_site_option( 'illegal_names' );
		if ( false == is_array( $illegal_names ) ) {
			$illegal_names = array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );
			add_site_option( 'illegal_names', $illegal_names );
		}

		if ( true === in_array( $user_login, $illegal_names ) ) {
			$result['errors']->add( 'user_name',  __( 'That username is not allowed.' ) );
		}

		// User login must have at least one letter
		if ( preg_match( '/^[0-9]*$/', $user_login ) ) {
			$result['errors']->add( 'user_name', __( 'Sorry, usernames must have letters too!' ) );
		}

		// Check if the username has been used already.
		if ( username_exists( $user_login ) ) {
			$result['errors']->add( 'user_name', __( 'Sorry, that username already exists!' ) );
		}

		if ( is_multisite() ) {
			// Is a signup already pending for this user login?
			$signup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->signups WHERE user_login = %s ", $user_login ) );
			if ( null != $signup ) {
				$registered_at = mysql2date( 'U', $signup->registered );
				$now = current_time( 'timestamp', true );
				$diff = $now - $registered_at;
				// If registered more than two days ago, cancel registration and let this signup go through.
				if ( $diff > 2 * DAY_IN_SECONDS ) {
					$wpdb->delete( $wpdb->signups, array( 'user_login' => $user_login ) );
				} else { $result['errors']->add( 'user_name', __( 'That username is currently reserved but may be available in a couple of days.' ) );
				}
			}
		}

		$result['user_login']          = $user_login;
		$result['original_user_login'] = $original_user_login;
		$result['user_email']          = $user_email;

		return $result;
	}

	/**
	 * Add a top level menu item for 'Networks' to the network administration sidebar
	 *
	 * @param string $parent_file
	 *
	 * @return string Unmodified parent file.
	 */
	function add_master_network_menu( $parent_file ) {
		global $menu, $submenu;

		if ( is_network_admin() && is_main_network() ) {
			$menu[6] = $menu[5];
			unset( $menu[5] );
			$menu[6][4] = 'menu-top menu-icon-site';
			$menu[5] = array(
				'Networks',
				'manage_networks',
				'sites.php?display=network',
				'',
				'menu-top menu-icon-site menu-top-first',
				'menu-site',
				'dashicons-networking',
			);

			$submenu['sites.php?display=network'][5] = array(
				'All Networks',
				'manage_sites', // @todo not correct, though is_network_admin may be good enough.
				'sites.php?display=network',
			);
			$submenu['sites.php?display=network'][10] = array(
				'Add New',
				'manage_sites', // @todo also likely not correct ^
				'site-new.php?display=network',
			);
			ksort( $menu );
		}

		// Some network wide plugins will provide a settings menu. We maintain a whitelist and
		// assume all others should be unset.
		$allowed_network_submenus = array(
			'bp-components',
		);

		// Remove submenu items that have not been whitelisted for 'Settings' in the Network Admin dashboard.
		if ( is_network_admin() && isset( $submenu['settings.php'] ) ) {
			if ( 2 <= count( $submenu['settings.php'] ) ) {
				foreach ( $submenu['settings.php'] as $k => $submenu_value ) {
					if ( isset( $submenu_value[2] ) && in_array( $submenu_value[2], $allowed_network_submenus ) ) {
						continue;
					}
					unset( $submenu['settings.php'][ $k ] );
				}
			} else {
				unset( $submenu['settings.php'] );
			}
		}

		return $parent_file;
	}

	/**
	 * Make modifications necessary to displaying the correct styling in the
	 * admin menu bar when the All Networks or Add New network sites are displayed.
	 *
	 * @param string $parent_file Current file being accessed.
	 *
	 * @return string Possible modification of the current file being accessed.
	 */
	public function parent_file( $parent_file ) {
		global $self, $submenu_file;

		if ( '/wp-admin/network/sites.php' === $_SERVER['DOCUMENT_URI'] ) {
			// The All Networks page.
			if ( isset( $_GET['display'] ) && 'network' === $_GET['display'] ) {
				$self = 'sites.php?display=network';
				$parent_file = 'sites.php?display=network';
			}
		}

		if ( '/wp-admin/network/site-new.php' === $_SERVER['DOCUMENT_URI'] ) {
			// The New Network page.
			if ( isset( $_GET['display'] ) && 'network' === $_GET['display'] ) {
				$self = 'sites.php?display=network';
				$parent_file = 'sites.php?display=network';
				$submenu_file = 'site-new.php?display=network';
			} else {
				// One of our other changes conflicts with the standard new site. Set this as well.
				$self = 'site-new.php';
				$parent_file = 'sites.php';
				$submenu_file = 'site-new.php';
			}
		}

		return $parent_file;
	}

	/**
	 * Enqueue styles and scripts to be used in the My Networks dashboard.
	 */
	function my_networks_dashboard_scripts() {
		if ( ! is_admin() || 'dashboard_page_my-networks' !== get_current_screen()->id ) {
			return;
		}

		wp_enqueue_style( 'wsuwp-my-networks', plugins_url( '/css/dashboard-my-networks.css', __FILE__ ), array(), wsuwp_global_version() );
	}

	/**
	 * Add a dashboard page to manage all WSU Networks that a user has access to
	 */
	function my_networks_dashboard() {
		add_dashboard_page( 'My Networks Dashboard', 'My Networks', 'read', 'my-networks', array( $this, 'display_my_networks' ) );
	}

	/**
	 * Output a "My Networks" dashboard page. This should provide an alternative method for
	 * navigating a lengthy list of networks and their sites.
	 */
	function display_my_networks() {
		?>
		<div class="wrap">
			<h2>My Networks</h2><?php

			foreach ( wsuwp_get_user_networks() as $network ) {
				wsuwp_switch_to_network( $network->id );
				$network->name = get_site_option( 'site_name' );
				$network->admin_email = get_site_option( 'admin_email' );
				$network->user_count = get_site_option( 'user_count', 0 );
				$network->site_count = get_site_option( 'blog_count', 0 );
				$network->admin_url = network_admin_url();
				$network->view_sites = network_admin_url( 'sites.php' );
				wsuwp_restore_current_network();

				?>
				<div class="single-network">
				<h3><a href="<?php echo esc_url( $network->admin_url ); ?>"><?php echo esc_html( $network->name ); ?></a></h3>
				<div class="single-network-url"><strong>URL:</strong> <?php echo esc_url( $network->domain . $network->path ); ?></div>
				<div class="single-network-admin"><strong>Admin:</strong> <?php echo esc_html( $network->admin_email ); ?></div>
				<div class="single-network-counts">
					<div class="single-network-user-count">Users<br /><?php echo esc_html( $network->user_count ); ?></div>
					<div class="single-network-site-count">Sites<br /><?php echo esc_html( $network->site_count ); ?></div>
					<div class="clear"></div>
				</div>
				<a href="<?php echo esc_url( $network->view_sites ); ?>">View sites</a>.
				</div>

				<?php
			}
	}

	/**
	 * Display All Networks in the admin dashboard.
	 */
	public function networks_php() {
		global $title, $parent_file;

		if ( ! isset( $_GET['display'] ) || 'network' !== $_GET['display'] ) {
			return;
		}

		$title = __( 'Networks' );
		$parent_file = 'sites.php?display=network';

		require( ABSPATH . 'wp-admin/admin-header.php' );

		require_once( dirname( __FILE__ ) . '/wsu-network-admin/class-wsuwp-networks-list-table.php' );
		$wsuwp_networks = new WSUWP_Networks_List_Table();
		$wsuwp_networks->prepare_items();
		?>
		<div class="wrap">
			<h2><?php

				echo $title;

			if ( current_user_can( 'create_sites' ) ) {
				?> <a href="<?php echo network_admin_url( 'site-new.php?display=network' ); ?>" class="add-new-h2"><?php echo esc_html_x( 'Add New', 'network' ); ?></a><?php
			}

			if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] ) {
				printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $_REQUEST['s'] ) );
			}
			?></h2>
			<form action="" method="get" id="ms-search">
				<?php $wsuwp_networks->search_box( __( 'Search Networks' ), 'network' ); ?>
				<input type="hidden" name="display" value="network" />
				<input type="hidden" name="action" value="search-networks" />
			</form>

			<form id="form-site-list" action="sites.php?display=network&action=all-networks" method="post">
				<?php $wsuwp_networks->display(); ?>
			</form>
		</div>
		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}

	/**
	 * Create a new network via the Networks dashboard screen.
	 *
	 * @param array $network Contains array of POST information for new network.
	 */
	private function _create_new_network( $network ) {
		$network_id = wsuwp_create_network( $network );

		if ( is_wp_error( $network_id ) ) {
			wp_die( $network_id );
		}

		// If a primary site name was not provided, inherit the network name.
		if ( ! isset( $network['site_name'] ) || '' === trim( $network['site_name'] ) ) {
			$network['site_name'] = $network['network_name'];
		}

		wpmu_create_blog( $network['domain'], '/', $network['site_name'], get_current_user_id(), '', $network_id );

		wp_redirect( network_admin_url( 'sites.php?display=network' ) );
		exit;
	}

	/**
	 * Display Add New Network in the admin dashboard.
	 */
	public function network_new_php() {
		global $title, $parent_file;

		if ( ! isset( $_GET['display'] ) || 'network' !== $_GET['display'] ) {
			return;
		}

		if ( isset( $_GET['action'] ) && 'add-network' === $_GET['action'] ) {
			check_admin_referer( 'add-network', '_wpnonce_add-network' );

			if ( ! is_array( $_POST['network'] ) ) {
				wp_die( __( 'Can&#8217;t create an empty network.' ) );
			}

			$this->_create_new_network( $_POST['network'] );
		}

		$title = __( 'Add New Network' );
		$parent_file = 'sites.php?display=network';

		require( ABSPATH . 'wp-admin/admin-header.php' );

		?>
		<div class="wrap">
			<h2 id="add-new-site"><?php _e( 'Add New Network' ) ?></h2>
			<form method="post" action="<?php echo network_admin_url( 'site-new.php?display=network&action=add-network' ); ?>">
				<?php wp_nonce_field( 'add-network', '_wpnonce_add-network' ) ?>
				<table class="form-table">
					<tr class="form-field form-required">
						<th scope="row"><?php _e( 'Network Domain' ) ?></th>
						<td><input name="network[domain]" type="text" class="regular-text" title="<?php esc_attr_e( 'Domain' ); ?>" /></td>
					</tr>
					<tr class="form-field form-required">
						<th scope="row"><?php _e( 'Network Title' ) ?></th>
						<td><input name="network[network_name]" type="text" class="regular-text" title="<?php esc_attr_e( 'Title' ) ?>"/></td>
					</tr>
					<tr class="form-field">
						<th scope="row"><?php _e( 'Primary Site Title' ); ?></th>
						<td><input name="network[site_name]" type="text" class="regular=text" title="<?php esc_attr_e( 'Site Title' ); ?>" /></td>
					</tr>
					<tr class="form-field form-required">
						<th scope="row"><?php _e( 'Admin Email' ) ?></th>
						<td><input name="network[email]" type="text" class="regular-text" title="<?php esc_attr_e( 'Email' ) ?>"/></td>
					</tr>
				</table>
				<?php submit_button( __( 'Add Network' ), 'primary', 'add-network' ); ?>
			</form>
		</div>
		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}

	/**
	 * Update a network with information passed from the Edit Network screen. This allows for general
	 * network options to be changed as well as the domain and path attached to a network. The domain
	 * and path are not touched if they do not differ from the current settings.
	 *
	 * @param int   $network_id   ID of the network to update.
	 * @param array $network_meta Information to update for the network.
	 */
	private function _update_network( $network_id, $network_meta ) {
		/**
		 * @var WPDB $wpdb
		 */
		global $wpdb;

		wsuwp_switch_to_network( $network_id );

		foreach ( $network_meta as $key => $value ) {
			if ( array_key_exists( $key, $this->network_meta_edit ) ) {
				$value = $this->network_meta_edit[ $key ]['validate']( $value );
				update_site_option( $key, $value );
			}
		}

		if ( isset( $network_meta['domain'] ) || isset( $network_meta['path'] ) ) {
			$network = wp_get_network( $network_id );

			$domain = untrailingslashit( $network_meta['domain'] );
			if ( false === wsuwp_validate_domain( $domain ) ) {
				wp_die( __( 'Invalid site address. Non standard characters were found in the domain name.' ) );
			} else {
				$domain = strtolower( $domain );
			}

			// Ensure a leading slash is always on the path.
			$path = '/' . ltrim( $network_meta['path'], '/' );
			$path = trailingslashit( $path );

			if ( false === wsuwp_validate_path( $path ) ) {
				wp_die( __( 'Invalid site address. Non standard characters were found in the path name.' ) );
			} else {
				$path = strtolower( $path );
			}

			if ( ! empty( $network ) && ( $network->domain !== $domain || $network->path !== $path ) ) {
				// Find the network's primary site to change it's domain and path as well.
				$site_id = $wpdb->get_var( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s AND site_id = %d", $network->domain, $network->path, $network_id ) );

				// Update the domain and path of the network.
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->site SET domain = %s, path = %s WHERE id = %d", $domain, $path, $network_id ) );

				// Update the domain and path of the site.
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->blogs SET domain = %s, path = %s WHERE blog_id = %d", $domain, $path, $site_id ) );

				// Site level options need to be set properly for the request to work.
				update_blog_option( $site_id, 'siteurl', esc_url_raw( 'http://' . $domain . $path ) );
				update_blog_option( $site_id, 'home', esc_url_raw( 'http://' . $domain . $path ) );

				// Using update_blog_option above clears the site level cache. We need to clear the network level cache.
				wp_cache_delete( $network_id, 'wsuwp:network' );
			}
		}

		wsuwp_restore_current_network();
	}

	/**
	 * Display an Edit Network screen in the admin dashboard.
	 */
	public function network_info_php() {
		global $title, $parent_file, $submenu_file, $wpdb;

		if ( ! isset( $_GET['display'] ) || 'network' !== $_GET['display'] ) {
			return;
		}

		if ( ! isset( $_GET['network_id'] ) || 0 === absint( $_GET['network_id'] ) ) {
			wp_safe_redirect( network_admin_url( 'sites.php?display=network' ) );
			exit;
		}

		$network_id = absint( $_GET['network_id'] );

		if ( isset( $_GET['action'] ) && 'update-network' === $_GET['action'] ) {
			check_admin_referer( 'update-network', '_wpnonce_update-network' );

			if ( ! is_array( $_POST['network'] ) ) {
				wp_die( __( 'Can&#8217;t edit a network without information.' ) );
			}

			$this->_update_network( $network_id, $_POST['network'] );
		}

		$title = __( 'Edit Network' );
		$parent_file = 'sites.php?display=network';
		$submenu_file = 'sites.php?display=network';

		require( ABSPATH . 'wp-admin/admin-header.php' );

		$network = get_network( $network_id );

		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->sitemeta} WHERE site_id = %d", $network_id );
		$network_data = $wpdb->get_results( $query, ARRAY_A ); // WPCS: unprepared SQL OK.

		$network_display_fields = array(
			'blog_count',
			'user_count',
			'initial_db_version',
			'wpmu_upgrade_site',
			'admin_user_id',
			'WPLANG',
			'admin_email',
			'can_compress_scripts',
		);
		?>
		<div class="wrap">
			<h2 id="edit-network"><?php _e( 'Edit Network' ); ?>: <?php echo $network->domain; ?></h2>
			<?php
			$display_output = '';
			$edit_output = '';
			foreach ( $network_data as $item ) {
				if ( array_key_exists( $item['meta_key'], $this->network_meta_edit ) ) {
					$edit_output .= '<tr class="form-field"><th scope="row"><label for="network[' . esc_attr( $item['meta_key'] ) . ']">' . esc_html( $this->network_meta_edit[ $item['meta_key'] ]['label'] ) . '</label></th>';
					if ( 'text' === $this->network_meta_edit[ $item['meta_key'] ]['input'] ) {
						$edit_output .= '<td><input class="wide-text" type="text" name="network[' . esc_attr( $item['meta_key'] ) . ']" value="' . esc_attr( $item['meta_value'] ) . '" /></td></tr>';
					} elseif ( 'textarea' === $this->network_meta_edit[ $item['meta_key'] ]['input'] ) {
						$edit_output .= '<td><textarea name="network[' . esc_attr( $item['meta_key'] ) . ']" rows="' . esc_attr( $this->network_meta_edit[ $item['meta_key'] ]['rows'] ) . '">' . esc_textarea( $item['meta_value'] ) . '</textarea></td></tr>';
					}
				}
				if ( in_array( $item['meta_key'], $network_display_fields ) ) {
					$display_output .= '<tr><td style="padding: 5px 10px;">' . esc_html( $item['meta_key'] ) . '</td><td style="padding: 5px 10px;">' . esc_html( $item['meta_value'] ) . '</td></tr>';
				}
			}
			?>
			<form method="post" action="<?php echo network_admin_url( 'site-info.php?display=network&action=update-network&network_id=' . $network_id ); ?>">
				<?php wp_nonce_field( 'update-network', '_wpnonce_update-network' ) ?>
				<table class="form-table">
					<tbody>
					<tr class="form-field">
						<th scope="row">
							<label for="network[domain]">Network Domain:</label>
						</th>
						<td><input class="wide-text" type="text" name="network[domain]" value="<?php echo esc_attr( $network->domain ); ?>">
						<p class="description">Changing the domain or path of an existing network may have severe consequences.</p></td>
					</tr>
					<tr class="form-field">
						<th scope="row">
							<label for="network[path]">Network Path:</label>
						</th>
						<td><input class="wide-text" type="text" name="network[path]" value="<?php echo esc_attr( $network->path ); ?>"></td>
					</tr>
					<?php echo $edit_output; ?>
					<tr class="form-field">
						<th scope="row"></th>
						<td><span style="display: block;width: 125px;"><?php submit_button( __( 'Update Network' ), 'primary', 'update-network' ); ?></span></td>
					<tr class="form-field">
						<th scope="row">Additional Network Information:</th>
						<td><table><?php echo $display_output; ?></table></td>
					</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}

	/**
	 * Return the default value for max fileupload size.
	 *
	 * @return int Size in KB
	 */
	public function set_fileupload_maxk() {
		$network_options = $this->get_global_network_options();

		return $network_options['fileupload_maxk'];
	}

	/**
	 * Return the default value for total site upload space.
	 *
	 * @return int Size in MB
	 */
	public function set_blog_upload_space() {
		$network_options = $this->get_global_network_options();

		return $network_options['blog_upload_space'];
	}

	/**
	 * Return the default value for allowed filetypes.
	 *
	 * @return string Space delimited list of allowed filetypes.
	 */
	public function set_upload_filetypes() {
		$network_options = $this->get_global_network_options();

		// Global admins can upload EXE files.
		if ( is_super_admin() ) {
			$network_options['upload_filetypes'] .= ' exe';
		}

		return $network_options['upload_filetypes'];
	}

	/**
	 * Modify the default list of allowed mime types.
	 *
	 * @param array $mime_types List of recognized mime types.
	 *
	 * @return array Modified list of mime types.
	 */
	public function set_mime_types( $mime_types ) {
		$mime_types['dmg'] = 'application/octet-stream';
		$mime_types['eps'] = 'application/postscript';
		$mime_types['txt'] = 'text/plain';
		$mime_types['mat'] = 'application/x-matlab-data';

		// global admins can upload exe files.
		if ( is_super_admin() ) {
			$mime_types['exe'] = 'application/x-msdownload';
		}

		return $mime_types;
	}

	/**
	 * Return the default value for allowing site admins to add new users.
	 *
	 * @return int|string 0 or 1 or '0' or '1'
	 */
	public function set_add_new_users() {
		$network_options = $this->get_global_network_options();

		return $network_options['add_new_users'];
	}

	/**
	 * Return the default value for notifying network admins of new user registrations.
	 *
	 * @return string yes or no
	 */
	public function set_registrationnotification() {
		$network_options = $this->get_global_network_options();

		return $network_options['registrationnotification'];
	}

	/**
	 * Return the default value for allowing registrations on networks.
	 *
	 * @return string
	 */
	public function set_registration() {
		$network_options = $this->get_global_network_options();

		return $network_options['registration'];
	}

	/**
	 * Return the default value for whether upload space should be checked for a site.
	 *
	 * @return int 0 to enable, 1 to disable
	 */
	public function set_upload_space_check_disabled() {
		$network_options = $this->get_global_network_options();

		return $network_options['upload_space_check_disabled'];
	}

	/**
	 * Remove the default site admin notice displayed by WordPress to inform super admins
	 * of a database upgrade after updating WordPress. We use the same hook to replace this
	 * functionality.
	 */
	public function remove_upgrade_notices() {
		remove_action( 'admin_notices', 'site_admin_notice' );
		remove_action( 'network_admin_notices', 'site_admin_notice' );
	}

	/**
	 * Clear the cache used to match a requested domain and path with a site record in the database.
	 *
	 * @param $blog_id
	 */
	public function clear_site_request_cache( $blog_id ) {
		$site_details = get_site( $blog_id );

		// Remove the cache attached to the old domain and path.
		wp_cache_delete( $site_details->domain . $site_details->path, 'wsuwp:site' );

		// Remove the cache attached to the new domain and path when updating a site.
		if ( isset( $_POST['blog'] ) && isset( $_POST['blog']['domain'] ) && isset( $_POST['blog']['path'] ) ) {
			wp_cache_delete( $_POST['blog']['domain'] . $_POST['blog']['path'], 'wsuwp:site' );
		}
	}

	/**
	 * Provide a rudimentary filter of the user count for each network by looking at the
	 * network capabilities meta key instead of relying on the WordPress user count, which
	 * really only looks for global users.
	 *
	 * @param int $value Count of users provided by `wp_update_network_user_counts()`.
	 *
	 * @return int Correct count of users for the network.
	 */
	public function update_network_user_count( $value ) {
		global $wpdb;

		if ( get_main_network_id() == $wpdb->siteid ) {
			return $value;
		}

		$meta_key = 'wsuwp_network_' . $wpdb->siteid . '_capabilities';
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(user_id) FROM $wpdb->usermeta WHERE meta_key = %s", $meta_key ) );
		return $count;
	}
}
new WSU_Network_Admin();
