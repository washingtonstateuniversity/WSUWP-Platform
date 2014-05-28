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
		add_action( 'wp_ajax_confirm_ssl', array( $this, 'confirm_ssl_ajax' ), 10 );
		add_action( 'wp_ajax_unconfirm_ssl', array( $this, 'unconfirm_ssl_ajax' ), 10 );
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

	/**
	 * Retrieve a list of domains that have not yet been confirmed as SSL ready.
	 *
	 * @return array List of domains waiting for SSL confirmation.
	 */
	public function get_ssl_disabled_domains() {
		/* @type WPDB $wpdb */
		global $wpdb;

		$query_string = like_escape( '_ssl_disabled' );

		$domains = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%{$query_string}'" );
		$domains = wp_list_pluck( $domains, 'option_name' );
		$domains = array_map( array( $this, 'strip_domain' ), $domains );

		return $domains;
	}

	/**
	 * Strip the _ssl_disabled suffix from the option name.
	 *
	 * @param string $option_name Original option name to be stripped.
	 *
	 * @return string Modified domain name.
	 */
	public function strip_domain( $option_name ) {
		$domain = str_replace( '_ssl_disabled', '',  $option_name );

		return $domain;
	}

	/**
	 * Provide a page to display domains that have not yet been confirmed as SSL ready.
	 */
	public function ssl_sites_display() {
		global $title;

		if ( ! isset( $_GET['display'] ) || 'ssl' !== $_GET['display'] ) {
			return;
		}

		$title = __('Manage Site SSL');

		wp_enqueue_script( 'wsu-ssl', plugins_url( '/js/wsu-ssl-site.js', __FILE__ ), array( 'jquery' ), wsuwp_global_version(), true );

		require( ABSPATH . 'wp-admin/admin-header.php' );

		?>
		<style>
			.confirm_ssl {
				text-decoration: underline;
				color: blue;
				cursor: pointer;
			}
		</style>
		<div class="wrap">
			<h2 id="add-new-site"><?php _e('Manage Site SSL') ?></h2>
			<p class="description">These sites have been configured on the WSUWP Platform, but do not yet have confirmed SSL configurations.</p>
			<input id="ssl_ajax_nonce" type="hidden" value="<?php echo esc_attr( wp_create_nonce( 'confirm-ssl' ) ); ?>" />
			<table class="form-table">
				<?php
				foreach( $this->get_ssl_disabled_domains() as $domain ) {
					?><tr><td><span id="<?php echo md5( $domain ); ?>" data-domain="<?php echo esc_attr( $domain ); ?>" class="confirm_ssl">Confirm</span></td><td><?php echo esc_html( $domain ); ?></td></tr><?php
				}
				?>
				<tr><td><label for="add_domain">Add Unconfirmed SSL Domain:</label></td><td>
						<input name="add_domain" id="add-domain" class="regular-text" value="" />
						<input type="button" id="submit-add-domain" class="button button-primary" value="Add Domain" />
				</td></tr>
			</table>
		</div>

		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}

	/**
	 * Handle an AJAX request to mark a domain as confirmed for SSL.
	 */
	public function confirm_ssl_ajax() {
		/* @type WPDB $wpdb */
		global $wpdb;

		check_ajax_referer( 'confirm-ssl', 'ajax_nonce' );

		if ( true === wsuwp_validate_domain( $_POST['domain'] ) ) {
			$domain_option = $_POST['domain'] . '_ssl_disabled';
			$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", $domain_option ) );
			if ( $result ) {
				$response = json_encode( array( 'success' => $_POST['domain'] ) );
			} else {
				$response = json_encode( array( 'error' => 'The domain passed was valid, but confirmation was not successful.' ) );
			}
		} else {
			$response = json_encode( array( 'error' => 'The domain passed for confirmation is not valid.' ) );
		}

		echo $response;
		die();
	}

	/**
	 * Handle an AJAX request to mark a domain as unconfirmed for SSL.
	 */
	public function unconfirm_ssl_ajax() {
		check_ajax_referer( 'confirm-ssl', 'ajax_nonce' );

		if ( true === wsuwp_validate_domain( trim( $_POST['domain'] ) ) ) {
			$option_name = trim( $_POST['domain'] ) . '_ssl_disabled';
			switch_to_blog( 1 );
			update_option( $option_name, '1' );
			restore_current_blog();
			$response = json_encode( array( 'success' => trim( $_POST['domain'] ) ) );
		} else {
			$response = json_encode( array( 'error' => 'Invalid domain.' ) );
		}

		echo $response;
		die();
	}
}
new WSU_SSL();