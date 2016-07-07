<?php
/*
Plugin Name: WSUWP network/site-info replacement.
Plugin URI: http://web.wsu.edu/
Description: Manages custom portions of the network/site-info.php page.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

/**
 * Manages custom portions of the network/site-info.php page.
 *
 * Class WSU_Network_Site_Info
 */
class WSU_Network_Site_Info {

	/**
	 * Add our hooks.
	 */
	public function __construct() {
		add_action( 'admin_footer', array( $this, 'display_move_site' ), 10 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'admin_action_update-site', array( $this, 'update_site' ), 10 );
	}

	/**
	 * Output the initial options for moving the site.
	 */
	public function display_move_site() {
		global $id;

		if ( 'site-info-network' !== get_current_screen()->id || ! is_super_admin() ) {
			return;
		}

		$site_network_id = get_blog_details( $id )->site_id;
		?>
		<table style="display:none;"><tr id="wsu-move-site" class="form-field form-required">
			<th scope="row"><?php _e( 'Network' ) ?></th>
			<td><select name="wsu_network_id">
		<?php
		$networks = get_networks();
		foreach( $networks as $network ) {
			echo '<option value="' . $network->id . '" ' . selected( $site_network_id, $network->id, false ) . '>' . $network->domain . $network->path . '</option>';
		}
		?>
				</select>
				<p class="description">Select the network for this site. <strong>Note:</strong> When moving a site between networks, any other changes made on this screen will be lost.</p>
			</td>
		</tr></table>
		<?php
	}

	/**
	 * Enqueue a script to reposition the move site DIV on load.
	 */
	public function enqueue_scripts() {
		if ( 'site-info-network' === get_current_screen()->id ) {
			wp_enqueue_script( 'wsu-move-site', plugins_url( '/js/wsu-move-site.js', __FILE__ ), array( 'jquery' ), wsuwp_global_version(), true );
		}
	}

	/**
	 * Move a site to another network when requested.
	 */
	public function update_site() {
		if ( 'site-info-network' !== get_current_screen()->id ) {
			return;
		}

		if ( isset($_REQUEST['action']) && 'update-site' == $_REQUEST['action'] ) {
			check_admin_referer( 'edit-site' );

			// Lookup the current site and clear site bootstrap cache for it.
			$id = absint( $_REQUEST['id'] );
			$current_details = get_blog_details( $id );
			wp_cache_delete( $current_details->domain . $current_details->path, 'wsuwp:site' );

			// Process a request to move a site between networks.
			if ( isset( $_POST['wsu_network_id'] ) ) {
				$network_id = absint( $_POST['wsu_network_id'] );
				if ( 0 === $network_id || 0 === $id ) {
					return;
				}

				if ( $network_id != $current_details->site_id ) {
					// Retrieve all of the user IDs associated with this site.
					$all_site_users_query = new WP_User_Query( array( 'blog_id' => $id, 'fields' => 'id' ) );

					// Retrieve only users IDs of this site that have existing capabilities on the new network.
					$new_network_users_query = new WP_User_Query( array( 'blog_id' => $id, 'meta_key' => 'wsuwp_network_' . $network_id . '_capabilities', 'meta_value' => array(), 'fields' => 'id' ) );

					// Which users do not yet have capabilities on the new network.
					$modify_network_users = array_diff( $all_site_users_query->get_results(), $new_network_users_query->get_results() );

					foreach( $modify_network_users as $user_id ) {
						// Assign capabilities for basic access to the new network.
						update_user_meta( $user_id, 'wsuwp_network_' . $network_id . '_capabilities', array() );

						$user_sites = get_blogs_of_user( $user_id, true );
						$remove_from_network = true;

						// Parse the user's sites for others on this network to see if we can remove this network's capabilities.
						foreach( $user_sites as $user_site_id => $user_site ) {
							if ( $user_site->site_id === $current_details->site_id && $user_site_id !== $id ) {
								$remove_from_network = false;
								continue;
							}
						}

						// Remove capabilities for access to this network if flagged.
						if ( true === $remove_from_network ) {
							delete_user_meta( $user_id, 'wsuwp_network_' . $current_details->site_id . '_capabilities' );
						}
					}

					$current_details->site_id = $network_id;
					update_blog_details( $id, $current_details );

					wp_safe_redirect( network_admin_url( 'sites.php') );
					die();
				}
			}
		} else {
			return;
		}
	}
}
new WSU_Network_Site_Info();
