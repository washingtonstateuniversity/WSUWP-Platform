<?php
/*
Plugin Name: WSU SSL
Plugin URI: http://web.wsu.edu/
Description: Manage various bits around SSL on the WSUWP Platform
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

class WSU_SSL {

	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'wpmu_new_blog', array( $this, 'determine_new_site_ssl' ), 10, 3 );
		add_filter( 'parent_file', array( $this, 'ssl_admin_menu' ), 11, 1 );
		add_action( 'load-site-new.php', array( $this, 'ssl_sites_display' ), 1 );
	}

	/**
	 * Determine if a new site should be flagged for SSL configuration.
	 *
	 * If this domain has already been added for another site, we'll assume the SSL status
	 * of that configuration and allow it to play out. If this is the first time for this
	 * domain, then we should flag it as SSL disabled.
	 *
	 * @param $blog_id
	 * @param $user_id
	 * @param $domain
	 */
	public function determine_new_site_ssl( $blog_id, $user_id, $domain ) {
		/* @type WPDB $wpdb */
		global $wpdb;

		$domain_exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->blogs WHERE domain = %s LIMIT 1 AND blog_id != %d ", $domain, $blog_id ) );

		if ( ! $domain_exists ) {
			switch_to_blog( 1 );
			update_option( $domain . '_ssl_disabled', 1 );
			restore_current_blog();
		}
	}

	/**
	 * Filter the submenu global to add a 'Manage Site SSL' link.
	 *
	 * @param string $parent_file Parent file of a menu subsection.
	 *
	 * @return string Parent file of a menu subsection.
	 */
	public function ssl_admin_menu( $parent_file ) {
		global $self, $submenu, $submenu_file;

		if ( wsuwp_get_current_network()->id == wsuwp_get_primary_network_id() ) {
			$submenu['sites.php'][15] = array(
				'Manage Site SSL',
				'manage_sites',
				'site-new.php?display=ssl',
			);
		}

		if ( isset( $_GET['display'] ) && 'ssl' === $_GET['display'] ) {
			$self = 'site-new.php?display=ssl';
			$parent_file = 'sites.php';
			$submenu_file = 'site-new.php?display=ssl';
		}

		return $parent_file;
	}

	public function ssl_sites_display() {
		global $title;

		if ( ! isset( $_GET['display'] ) || 'ssl' !== $_GET['display'] ) {
			return;
		}

		$title = __('Manage Site SSL');

		require( ABSPATH . 'wp-admin/admin-header.php' );

		?>

		<div class="wrap">
			<h2 id="add-new-site"><?php _e('Manage Site SSL') ?></h2>
			<p class="description">These sites have been configured on the WSUWP Platform, but do not yet have confirmed SSL configurations.</p>

			<form method="post" action="<?php echo network_admin_url('site-new.php?action=add-network-site'); ?>">
				<?php wp_nonce_field( 'add-network-site', '_wpnonce_add-network-site' ) ?>

			</form>
		</div>
		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}
}
new WSU_SSL();