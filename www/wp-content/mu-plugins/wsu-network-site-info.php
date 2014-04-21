<?php

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
		add_action( 'admin_action_update-site', array( $this, 'update_network' ), 10 );
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
		$networks = wsuwp_get_networks();
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
	public function update_network() {
		if ( 'site-info-network' !== get_current_screen()->id ) {
			return;
		}

		if ( isset($_REQUEST['action']) && 'update-site' == $_REQUEST['action'] ) {
			check_admin_referer( 'edit-site' );

			if ( isset( $_POST['wsu_network_id'] ) ) {
				$network_id = absint( $_POST['wsu_network_id'] );
				$id = absint( $_REQUEST['id'] );
				if ( 0 === $network_id || 0 === $id ) {
					return;
				}
				$current_details = get_blog_details( $id );
				$current_details->site_id = $network_id;
				update_blog_details( $id, $current_details );
				wp_safe_redirect( network_admin_url( 'sites.php') );
				die();
			}
		} else {
			return;
		}
	}
}
new WSU_Network_Site_Info();