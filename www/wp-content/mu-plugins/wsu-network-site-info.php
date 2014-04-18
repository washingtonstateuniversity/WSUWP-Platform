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
	}

	/**
	 * Output the initial options for moving the site.
	 */
	public function display_move_site() {
		if ( 'site-info-network' !== get_current_screen()->id ) {
			return;
		}
		?>
		<div id="wsu-move-site" style="display:none;">
			Site Move.
		</div>
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

}
new WSU_Network_Site_Info();