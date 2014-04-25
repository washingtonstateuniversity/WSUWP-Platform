<?php
/**
 * Class WSU_Network_Users
 */
class WSU_Network_Users {
	/**
	 * Add hooks and filters for managing network users.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'set_super_admins' ) );

		add_action( 'wpmu_new_user',            array( $this, 'add_user_to_network' ) );
		add_action( 'personal_options_update',  array( $this, 'add_user_to_network' ) );
		add_action( 'edit_user_profile_update', array( $this, 'add_user_to_network' ) );

		add_action( 'wpmu_new_user',            array( $this, 'add_user_to_global' ) );
		add_action( 'personal_options_update',  array( $this, 'add_user_to_global' ) );
		add_action( 'edit_user_profile_update', array( $this, 'add_user_to_global' ) );

		add_action( 'edit_user_profile', array( $this, 'toggle_super_admin' ) );
		add_action( 'edit_user_profile_update', array( $this, 'toggle_super_admin_update' ) );

		add_filter( 'user_has_cap', array( $this, 'user_can_manage_network' ), 10, 4 );
	}

	/**
	 * Use the $super_admins global to define super admins based on
	 * the network being loaded. Users assigned as super admins to the
	 * primary network should also be considered super admins on other
	 * networks.
	 */
	public function set_super_admins() {
		global $super_admins;

		$global_admins = $this->get_global_admins();

		if ( wsuwp_get_current_network()->id != wsuwp_get_primary_network_id() ) {
			$network_admins = get_site_option( 'site_admins', array() );
			$super_admins = array_unique( array_merge( $network_admins, $global_admins ) );
		}

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
	 * Display an option to assign super admin privileges to a user.
	 *
	 * Only global admins are able to see this option.
	 *
	 * @param WP_User $profile_user The user being edited.
	 */
	public function toggle_super_admin( $profile_user ) {
		if ( $this->is_global_admin() && ! $this->is_global_admin( $profile_user->ID ) ) {
			?>
			<tr>
				<th><?php _e( 'Super Admin' ); ?></th>
				<td><p><label><input type="checkbox" id="super_admin" name="super_admin"<?php checked( is_super_admin( $profile_user->ID ) ); ?> /> <?php _e( 'Grant this user super admin privileges for the Network.' ); ?></label></p></td>
			</tr>
			<?php
		} elseif ( $this->is_global_admin() ) {
			?>
			<tr>
				<th><?php _e( 'Super Admin' ); ?></th>
				<td><p>This user is a global admin and cannot be modified.</p></td>
			</tr>
			<?php
		}
	}

	/**
	 * Handle the super admin checkbox from the user edit page.
	 *
	 * @param int $user_id User ID for user being saved.
	 */
	public function toggle_super_admin_update( $user_id ) {
		if ( $this->is_global_admin() && isset( $_POST['super_admin'] ) ) {
			if ( empty( $_POST['super_admin'] ) ) {
				$this->revoke_super_admin( $user_id );
			} else {
				$this->grant_super_admin( $user_id );
			}
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
		}
		update_site_option( 'site_admins', $network_admins );
	}

	/**
	 * Determine if a user has capabilities to manage a specific network.
	 *
	 * manage_network checks in WordPress core do not pass a network ID, so we
	 * do not check those as they are handled by the default is_super_admin()
	 * call during a single page load.
	 *
	 * @param array   $allcaps All capabilities set for the user right now.
	 * @param array   $caps    The capabilities being checked.
	 * @param array   $args    Arguments passed with the has_cap() call.
	 * @param WP_User $user    The current user being checked.
	 *
	 * @return array Modified list of capabilities for the user.
	 */
	public function user_can_manage_network( $allcaps, $caps, $args, $user ) {
		if ( $args[0] === 'manage_network' && isset( $args[2] ) ) {
			$network_id = absint( $args[2] );
			wsuwp_switch_to_network( $network_id );
			$network_admins = get_site_option( 'site_admins', array() );
			if ( $user && in_array( $user->user_login, $network_admins ) ) {
				$allcaps['manage_network'] = true;
			}
			wsuwp_restore_current_network();
		}

		return $allcaps;
	}
}
new WSU_Network_Users();