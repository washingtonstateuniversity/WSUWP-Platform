<?php
/*
* Plugin Name: WSUWP Manage Network Users
* Plugin URI: http://web.wsu.edu
* Description: Customizations around global and network admins.
* Author: washingtonstateuniversity, jeremyfelt
* Author URI: http://web.wsu.edu
* Version: 0.1
* Network: true
*/

/**
 * Class WSU_Network_Users
 */
class WSU_Network_Users {

	/**
	 * Allows us to track the intent of a users list table load.
	 *
	 * @var bool True if a user search is being performed. False if not.
	 */
	var $is_user_search = false;

	/**
	 * Add hooks and filters for managing network users.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'set_super_admins' ), 1 );

		add_action( 'wpmu_new_user',            array( $this, 'add_user_to_network' ) );
		add_action( 'personal_options_update',  array( $this, 'add_user_to_network' ) );
		add_action( 'edit_user_profile_update', array( $this, 'add_user_to_network' ) );
		add_action( 'added_existing_user', array( $this, 'add_user_to_network' ) );

		add_action( 'wpmu_new_user',            array( $this, 'add_user_to_global' ) );
		add_action( 'personal_options_update',  array( $this, 'add_user_to_global' ) );
		add_action( 'edit_user_profile_update', array( $this, 'add_user_to_global' ) );

		add_action( 'edit_user_profile', array( $this, 'toggle_capabilities' ) );
		add_action( 'edit_user_profile_update', array( $this, 'toggle_capabilities_update' ) );

		if ( ! defined( 'WP_CLI' ) ) {
			add_filter( 'user_has_cap', array( $this, 'user_can_manage_network' ), 10, 4 );
			add_filter( 'user_has_cap', array( $this, 'user_can_manage_plugins' ), 10, 4 );
			add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
			add_filter( 'user_has_cap', array( $this, 'remove_secondary_network_caps' ), 99, 4 );
		}

		add_action( 'pre_user_query', array( $this, 'pre_user_query' ), 10, 1 );
		add_filter( 'user_search_columns', array( $this, 'user_search_columns' ), 10, 1 );
	}

	/**
	 * Set the $super_admins global to define global super admins.
	 *
	 * The super admin role is a global admin role only. Capabilities should
	 * be used to handle permissions at the individual network level.
	 */
	public function set_super_admins() {
		global $super_admins;

		$super_admins = $this->get_global_admins();

		return $super_admins;
	}

	/**
	 * Retrieve an array of super admins at the global level.
	 *
	 * @return array List of global admins.
	 */
	public function get_global_admins() {
		wsuwp_switch_to_network( wsuwp_get_primary_network_id() );
		$global_admins = get_site_option( 'site_admins', array() );
		wsuwp_restore_current_network();

		return $global_admins;
	}

	/**
	 * Determine if a user is a global admin.
	 *
	 * @param int $user_id
	 *
	 * @return bool True if the user is a global admin. False if not.
	 */
	public function is_global_admin( $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user = wp_get_current_user();
		} else {
			$user = get_userdata( $user_id );
		}

		$global_admins = $this->get_global_admins();

		if ( is_array( $global_admins ) && in_array( $user->user_login, $global_admins ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add capabilities for the current network to a user.
	 *
	 * @param int $user_id User ID of a new user added to a network.
	 */
	public function add_user_to_network( $user_id ) {
		$network_id = absint( wsuwp_get_current_network()->id );
		if ( 0 < $network_id ) {
			add_user_meta( $user_id, 'wsuwp_network_' . $network_id . '_capabilities', array(), true );
		}
	}

	/**
	 * Add capabilities for the global platform to a user.
	 *
	 * Capabilities are only added if the wsuwp_global_capabilities key does
	 * not currently exist.
	 *
	 * @param int $user_id User ID of a new user added to the global platform.
	 */
	public function add_user_to_global( $user_id ) {
		add_user_meta( $user_id, 'wsuwp_global_capabilities', array(), true );
	}

	/**
	 * Provide a method for adding custom capabilities to a user through the user edit screen.
	 *
	 * Depending on the capability being added, only network or global admins will be able to
	 * see these options.
	 *
	 * @param WP_User $profile_user User currently being edited.
	 */
	public function toggle_capabilities( $profile_user ) {
		if ( is_network_admin() && $this->is_global_admin() && ! $this->is_global_admin( $profile_user->ID ) ) {
			?>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Network Admin' ); ?></th>
					<td><p><label><input type="checkbox" id="network_admin"  name="network_admin" <?php checked( user_can( $profile_user->ID, 'manage_network', wsuwp_get_current_network()->id ) ); ?> /><?php _e( 'Grant this user admin privileges for the Network.' ); ?></label></p></td>
				</tr>
			</table>
		<?php
		}

		if ( $this->is_global_admin() ) {
			?>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Javascript Editor' ); ?></th>
					<td><p><label><input type="checkbox" id="javascript_editor"  name="javascript_editor" <?php checked( user_can( $profile_user->ID, 'edit_javascript' ) ); ?> /><?php _e( 'Grant this user access to the Custom Javascript Editor.' ); ?></label></p></td>
				</tr>
			</table><?php
		}
	}

	/**
	 * Handle the updating of custom capabilities through the user edit screen.
	 *
	 * @param int $user_id ID of the user being saved.
	 */
	public function toggle_capabilities_update( $user_id ) {
		if ( ! $this->is_global_admin() ) {
			return;
		}

		// Process network admin assignment at the network level.
		if ( is_network_admin() && $this->is_global_admin() ) {
			if ( empty( $_POST['network_admin'] ) ) {
				$this->revoke_super_admin( $user_id );
			} elseif ( 'on' === $_POST['network_admin'] ) {
				$this->grant_super_admin( $user_id );
			}
		}

		// Process Javascript editor assigment at any level.
		if ( empty( $_POST['javascript_editor'] ) ) {
			delete_user_meta( $user_id, 'wsuwp_can_edit_javascript' );
		} elseif ( 'on' === $_POST['javascript_editor'] ) {
			update_user_meta( $user_id, 'wsuwp_can_edit_javascript', '1' );
		}
	}

	/**
	 * Revoke super admin privileges on this network.
	 *
	 * @param int $user_id User ID being demoted.
	 */
	public function revoke_super_admin( $user_id ) {
		if ( ! $this->is_global_admin() ) {
			return;
		}

		$network_admins = get_site_option( 'site_admins', array() );
		$user = get_userdata( $user_id );
		if ( $user &&  false !== ( $key = array_search( $user->user_login, $network_admins ) ) ) {
			unset( $network_admins[ $key ] );
			update_site_option( 'site_admins', $network_admins );
		}
	}

	/**
	 * Grant super admin privileges on this network.
	 *
	 * @param int $user_id User ID being promoted.
	 */
	public function grant_super_admin( $user_id ) {
		if ( ! $this->is_global_admin() ) {
			return;
		}

		$network_admins = get_site_option( 'site_admins', array() );
		$user = get_userdata( $user_id );
		if ( $user && ! in_array( $user->user_login, $network_admins ) ) {
			$network_admins[] = $user->user_login;
			// A super admin should also be added as a member to the primary site.
			add_user_to_blog( get_current_blog_id(), $user_id, 'administrator' );
		}
		update_site_option( 'site_admins', $network_admins );
	}

	/**
	 * Determine if a user login has been assigned as a network
	 * level administrator.
	 *
	 * @param string $user_login User login to check.
	 * @param int    $network_id Network ID to check against.
	 *
	 * @return bool True if the user is a network admin. False if not.
	 */
	public function is_network_admin( $user_login, $network_id = 0 ) {
		if ( 0 === absint( $network_id ) ) {
			$network_id = wsuwp_get_current_network()->id;
		}

		$network_id = absint( $network_id );

		wsuwp_switch_to_network( $network_id );
		$network_admins = get_site_option( 'site_admins', array() );
		wsuwp_restore_current_network();

		if ( in_array( $user_login, $network_admins ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add network admin capabilities based on the user's admin role.
	 *
	 * @param array   $allcaps All capabilities set for the user right now.
	 * @param array   $caps    The capabilities being checked.
	 * @param array   $args    Arguments passed with the has_cap() call.
	 * @param WP_User $user    The current user being checked.
	 *
	 * @return array Modified list of capabilities for the user.
	 */
	public function user_can_manage_network( $allcaps, $caps, $args, $user ) {
		if ( 'manage_network' === $args[0] ) {
			$network_id = isset( $args[2] ) ? $args[2] : 0;
		} else {
			$network_id = 0;
		}

		if ( $user && $this->is_network_admin( $user->user_login, $network_id ) ) {
			$allcaps[ $args[0] ] = true;

			// activate_plugins and manage_network_plugins are tied together here.
			if ( 'activate_plugins' === $args[0] ) {
				$allcaps['manage_network_plugins'] = true;
			}

			// promote_users is a meta cap, but needs to be added here for our network admin role.
			if ( 'promote_user' === $args[0] ) {
				$allcaps['promote_users'] = true;
			}
		}

		if ( $user && 'edit_javascript' === $args[0] ) {
			$edit_javascript = get_user_meta( $user->ID, 'wsuwp_can_edit_javascript', true );
			if ( '1' === $edit_javascript ) {
				$allcaps['edit_javascript'] = true;
			}
		}

		return $allcaps;
	}

	/**
	 * Individual site administrators should be able to manage plugins on their site.
	 *
	 * @param array   $allcaps All capabilities set for the user right now.
	 * @param array   $caps    The capabilities being checked.
	 * @param array   $args    Arguments passed with the has_cap() call.
	 * @param WP_User $user    The current user being checked.
	 *
	 * @return array Modified list of capabilities for the user.
	 */
	public function user_can_manage_plugins( $allcaps, $caps, $args, $user ) {
		if ( $user && in_array( 'administrator', $user->roles ) ) {
			// activate_plugins and manage_network_plugins are tied together here.
			if ( 'activate_plugins' === $args[0] ) {
				$allcaps['manage_network_plugins'] = true;
			}
		}

		return $allcaps;
	}

	/**
	 * Allow network admins access to many capabilities disabled by default.
	 *
	 * WordPress core checks for is_super_admin(), which disqualifies many of our
	 * network admins. This overwrites that decision.
	 *
	 * @param array  $caps    List of associated capabilities with this meta cap.
	 * @param string $cap     Specific capability being requested.
	 * @param int    $user_id User ID of the user being checked.
	 * @param array  $args    Miscellaneous arguments passed.
	 *
	 * @return array Capabilities mapped to the user.
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( isset( $caps[0] ) && 'do_not_allow' === $caps[0] ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user && $this->is_network_admin( $user->user_login ) ) {
				$caps[0] = $cap;

				if ( 'edit_user' === $cap ) {
					$caps[0] = 'edit_users';
				} elseif ( 'delete_user' === $cap ) {
					$caps[0] = 'delete_users';
				} elseif ( 'promote_user' === $cap ) {
					$caps[0] = 'promote_users';
				}
			}
		}

		return $caps;
	}

	/**
	 * Remove some blanket capabilities after initial filtering.
	 *
	 * Some capabilities should not be enabled at the individual network level.
	 *
	 * @param array   $allcaps All capabilities set for the user right now.
	 * @param array   $caps    The capabilities being checked.
	 * @param array   $args    Arguments passed with the has_cap() call.
	 * @param WP_User $user    The current user being checked.
	 *
	 * @return array Modified list of capabilities for the user.
	 */
	public function remove_secondary_network_caps( $allcaps, $caps, $args, $user ) {
		$remove_caps = array(
			'delete_themes',
			'install_themes',
			'update_themes',
			'edit_themes',
			'update_plugins',
			'install_plugins',
			'edit_plugins',
			'edit_files',
			'update_core',
		);

		if ( in_array( $args[0], $remove_caps ) ) {
			$allcaps[ $args[0] ] = false;
		}

		return $allcaps;
	}

	/**
	 * Rewrite user queries for networks when showing the network users list table.
	 *
	 * @param WP_User_Query $query The current user query.
	 */
	public function pre_user_query( $query ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		if ( ! is_admin() || 'users-network' !== get_current_screen()->id ) {
			return;
		}

		// The primary network (global) should show all users.
		if ( wsuwp_get_primary_network_id() == wsuwp_get_current_network()->id ) {
			return;
		}

		$global_admin_ids = array();
		foreach( $this->get_global_admins() as $global_admin ) {
			$user = get_user_by( 'login', $global_admin );
			$global_admin_ids[] = $user->ID;
		}

		wp_enqueue_script( 'wsuwp-network-users', plugins_url( '/js/wsuwp-network-users.js', __FILE__ ), array( 'jquery' ), wsuwp_global_version(), true );

		$network_id = absint( wsuwp_get_current_network()->id );

		$query->query_from = 'FROM wp_users INNER JOIN wp_usermeta ON (wp_users.ID = wp_usermeta.user_id)';
		$query->query_where = "WHERE 1=1 AND (wp_usermeta.meta_key = 'wsuwp_network_" . $network_id . "_capabilities' )";

		if ( isset( $_REQUEST['role'] ) && 'super' === $_REQUEST['role'] ) {
			$network_admins = get_site_option( 'site_admins', array() );
			foreach( $network_admins as $network_admin ) {
				$network_admin = get_user_by( 'login', $network_admin );
				$query->query_vars['include'][] = $network_admin->ID;
			}
		}

		// Specific users are being included or excluded from search.
		if ( ! empty( $query->query_vars['include'] ) ) {
			$ids = implode( ',', wp_parse_id_list( $query->query_vars['include'] ) );
			$query->query_where .= " AND $wpdb->users.ID IN ($ids)";
			$global_admin_ids = implode( ',', wp_parse_id_list( $global_admin_ids ) );
			$query->query_where .= " AND $wpdb->users.ID NOT IN ($global_admin_ids)";
		} elseif ( ! empty( $query->query_vars['exclude'] ) ) {
			$query->query_vars['exclude'] += $global_admin_ids;
			$ids = implode( ',', wp_parse_id_list( $query->query_vars['exclude'] ) );
			$query->query_where .= " AND $wpdb->users.ID NOT IN ($ids)";
		} else {
			$global_admin_ids = implode( ',', wp_parse_id_list( $global_admin_ids ) );
			$query->query_where .= " AND $wpdb->users.ID NOT IN ($global_admin_ids)";
		}

		// Copied wildcard detection from core's WP_User_Query::prepare_query()
		if ( $this->is_user_search ) {
			$search = $query->query_vars['search'];

			$leading_wild = ( ltrim( $search, '*' ) != $search );
			$trailing_wild = ( rtrim( $search, '*' ) != $search );

			if ( $leading_wild && $trailing_wild ) {
				$wild = 'both';
			} elseif ( $leading_wild ) {
				$wild = 'leading';
			} elseif ( $trailing_wild ) {
				$wild = 'trailing';
			} else {
				$wild = false;
			}

			if ( $wild ) {
				$search = trim( $search, '*' );
			}

			$query->query_where .= $query->get_search_sql( $search, $search, $wild );
		}
	}

	/**
	 * Determine if a user search is being done via filter on the original query.
	 *
	 * HACK - this sets a property that we use to detect if a search has been performed.
	 *
	 * @param array $search_columns Array of search columns.
	 *
	 * @return array Array of search columns
	 */
	public function user_search_columns( $search_columns  ) {
		$this->is_user_search = true;

		return $search_columns;
	}
}
$wsu_network_users = new WSU_Network_Users();

/**
 * Wrapper function to determine if a user is a network admin.
 *
 * @param string $user_login Username for the user being checked.
 * @param int    $network_id ID of the network.
 *
 * @return bool  True if the user is a network admin. False if not.
 */
function wsuwp_is_network_admin( $user_login, $network_id = 0 ) {
	global $wsu_network_users;

	return $wsu_network_users->is_network_admin( $user_login, $network_id );
}

/**
 * Email login credentials to a newly-registered user.
 *
 * Plugin function to replace core's wp_new_user_notification
 *
 * @param int    $user_id        User ID.
 * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
 */
function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
	$user = get_userdata( $user_id );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );

	$message  = sprintf( __( 'A new user has been added to %s:' ), $blogname ) . "\r\n\r\n";
	$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
	$message .= sprintf( __( 'E-mail: %s' ), $user->user_email ) . "\r\n\r\n";
	$message .= "No action is necessary. This message is purely informative." . "\r\n\r\n";
	$message .= "- WSUWP Platform (wp.wsu.edu)";

	@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Added' ), $blogname ), $message );
}