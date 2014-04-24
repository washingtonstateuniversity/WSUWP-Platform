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
	}

	/**
	 * Add capabilities for the current network to a user.
	 *
	 * @param int $user_id User ID of a new user added to a network.
	 */
	public function add_user_to_network( $user_id ) {
		$network_id = wsuwp_get_current_network()->id;
		update_user_meta( $user_id, 'wsuwp_network_' . $network_id . '_capabilities', array() );
	}
}
new WSU_Network_Users();