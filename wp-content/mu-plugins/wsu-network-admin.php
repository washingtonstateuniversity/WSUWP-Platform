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
	 * Add the filters and actions used
	 */
	public function __construct() {
		add_action( 'load-sites.php',                    array( $this, 'networks_php'               ), 10, 1 );
		// Load at 9 for now until we sort our conflict with wsu-network-site-new.php
		add_action( 'load-site-new.php',                 array( $this, 'network_new_php'            ),  9, 1 );
		add_action( 'load-site-info.php',                array( $this, 'network_info_php'           ), 10, 1 );
		add_filter( 'parent_file',                       array( $this, 'add_master_network_menu'    ), 10, 1 );
		add_action( 'admin_menu',                        array( $this, 'my_networks_dashboard'      ),  1    );
		add_filter( 'wpmu_validate_user_signup',         array( $this, 'validate_user_signup'       ), 10, 1 );
		add_filter( 'plugin_action_links',               array( $this, 'remove_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'remove_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'plugin_action_links'        ), 15, 2 );
		add_action( 'activate_plugin',                   array( $this, 'activate_global_plugin'     ), 10, 1 );
		add_filter( 'views_plugins-network',             array( $this, 'add_plugin_table_views',    ), 10, 1 );
		add_filter( 'all_plugins',                       array( $this, 'all_plugins',               ), 10, 1 );
		add_filter( 'parent_file',                       array( $this, 'parent_file',               ), 10, 1 );
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
				'div',
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

		// Remove the submenu items for 'Settings' in the Network Admin dashboard.
		if ( is_network_admin() ) {
			unset ( $submenu['settings.php'] );
		}
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
	 * Add a dashboard page to manage all WSU Networks that a user has access to
	 */
	function my_networks_dashboard() {
		add_dashboard_page( 'My Networks Dashboard', 'My Networks', 'read', 'my-networks', array( $this, 'display_my_networks' ) );
	}

	/**
	 * Output the dashboard page for WSU Networks
	 */
	function display_my_networks() {
		?>
		<div class="wrap">
			<h2>My Networks<?php
	}

	/**
	 * Display All Networks in the admin dashboard.
	 */
	public function networks_php() {
		global $title, $parent_file;

		if ( ! isset( $_GET['display'] ) || 'network' !== $_GET['display'] ) {
			return;
		}

		$title = __('Networks');
		$parent_file = 'sites.php?display=network';

		require( ABSPATH . 'wp-admin/admin-header.php' );

		require_once( dirname( __FILE__ ) . '/wsu-network-admin/class-wsuwp-networks-list-table.php' );
		$wsuwp_networks = new WSUWP_Networks_List_Table();
		$wsuwp_networks->prepare_items();
		?>
		<div class="wrap">
			<h2><?php

				echo $title;

				if ( current_user_can( 'create_sites') ) {
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

			if ( ! is_array( $_POST['network'] ) )
				wp_die( __( 'Can&#8217;t create an empty network.' ) );

			$this->_create_new_network( $_POST['network'] );
		}

		$title = __('Add New Network');
		$parent_file = 'sites.php?display=network';

		require( ABSPATH . 'wp-admin/admin-header.php' );

		?>
		<div class="wrap">
			<h2 id="add-new-site"><?php _e('Add New Network') ?></h2>
			<form method="post" action="<?php echo network_admin_url('site-new.php?display=network&action=add-network'); ?>">
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
				<?php submit_button( __('Add Network'), 'primary', 'add-network' ); ?>
			</form>
		</div>
		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}

	/**
	 * Update a network's meta data with information passed from the Edit Network screen.
	 *
	 * @param int   $network_id   ID of the network to update.
	 * @param array $network_meta Meta information to update for the network.
	 */
	private function _update_network( $network_id, $network_meta ) {
		switch_to_network( $network_id );
		foreach ( $network_meta as $key => $value ) {
			if ( array_key_exists( $key, $this->network_meta_edit ) ) {
				$value = $this->network_meta_edit[ $key ]['validate']( $value );
				update_site_option( $key, $value );
			}
		}
		restore_current_network();
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

			if ( ! is_array( $_POST['network'] ) )
				wp_die( __( 'Can&#8217;t edit a network without information.' ) );

			$this->_update_network( $network_id, $_POST['network'] );
		}

		$title = __('Edit Network');
		$parent_file = 'sites.php?display=network';
		$submenu_file = 'sites.php?display=network';

		require( ABSPATH . 'wp-admin/admin-header.php' );

		$network = wp_get_networks( array( 'network_id' => $network_id ) );

		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->sitemeta} WHERE site_id = %d", $network_id );
		$network_data = $wpdb->get_results( $query, ARRAY_A );

		$network_display_fields = array(
			'blog_count',
			'user_count',
			'initial_db_version',
			'wpmu_upgrade_site',
			'blog_upload_space',
			'fileupload_max',
			'upload_file_types',
			'admin_user_id',
			'registration',
			'add_new_users',
			'upload_space_check_disabled',
			'subdomain_install',
			'global_terms_enabled',
			'ms_files_rewriting',
			'WPLANG',
			'admin_email',
			'can_compress_scripts',
		);
		?>
		<div class="wrap">
			<h2 id="edit-network"><?php _e( 'Edit Network' ); ?>: <?php echo $network[0]->domain; ?></h2>
			<?php
			$display_output = '';
			$edit_output = '';
			foreach( $network_data as $item ) {
				if ( array_key_exists( $item['meta_key'], $this->network_meta_edit ) ) {
					$edit_output .= '<tr class="form-field"><th scope="row"><label for="network[' . esc_attr( $item['meta_key'] ) . ']">' . esc_html( $this->network_meta_edit[ $item['meta_key'] ]['label'] ) . '</label></th>';
					if ( 'text' === $this->network_meta_edit[ $item['meta_key'] ]['input'] ) {
						$edit_output .= '<td><input class="wide-text" type="text" name="network[' . esc_attr( $item['meta_key'] ) . ']" value="' . esc_attr( $item['meta_value'] ) . '" /></td></tr>';
					} else if ( 'textarea' === $this->network_meta_edit[ $item['meta_key'] ]['input'] ) {
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
					<?php echo $edit_output; ?>
					<tr class="form-field">
						<th scope="row"></th>
						<td><span style="display: block;width: 125px;"><?php submit_button( __('Update Network'), 'primary', 'update-network' ); ?></span></td>
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

}
new WSU_Network_Admin();