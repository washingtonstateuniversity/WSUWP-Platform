<?php
/**
 * Class WSU_Network_Users
 */
class WSU_Network_Users {
	/**
	 * Add hooks and filters for managing network users.
	 */
	public function __construct() {
		add_action( 'wpmu_new_user', array( $this, 'add_user_to_network' ) );
		add_action( 'wpmu_new_user', array( $this, 'add_user_to_global' ) );
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