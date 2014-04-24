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
	}

	/**
	 * Use the $super_admins global to define super admins based on
	 * the network being loaded. Users assigned as super admins to the
	 * primary network should also be considered super admins on other
	 * networks.
	 */
	public function set_super_admins() {
		global $super_admins;

		wsuwp_switch_to_network( wsuwp_get_primary_network_id() );
		$super_admins = get_site_option( 'site_admins', array() );
		wsuwp_restore_current_network();

		if ( wsuwp_get_current_network()->id != wsuwp_get_primary_network_id() ) {
			$network_admins = get_site_option( 'site_admins', array() );
			$super_admins = array_unique( array_merge( $super_admins, $network_admins ) );
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
}
new WSU_Network_Users();