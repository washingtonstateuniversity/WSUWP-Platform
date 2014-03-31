<?php
/*
* Plugin Name: WSUWP New Site Administration
* Plugin URI: http://web.wsu.edu
* Description: Replaces the default site-new.php in WordPress
* Author: washingtonstateuniversity, jeremyfelt
* Author URI: http://web.wsu.edu
* Version: 0.1
* Network: true
*/

class WSUWP_New_Site_Administration {

	/**
	 * Fire up our hooks.
	 */
	public function __construct() {
		add_action( 'load-site-new.php',        array( $this, 'site_new_php' ) );

		add_filter( 'wsuwp_first_post_content', array( $this, 'first_post_content' ), 10, 1 );
		add_filter( 'wsuwp_first_post_title',   array( $this, 'first_post_title'   ), 10, 1 );
		add_filter( 'wsuwp_first_page_content', array( $this, 'first_page_content' ), 10, 1 );
		add_filter( 'wsuwp_first_page_title',   array( $this, 'first_page_title'   ), 10, 1 );
	}

	/**
	 * Create a new site on the network based on the information passed.
	 *
	 * @param array $site POST site information. Contains domain, path, email, title.
	 */
	private function _create_new_site( $site  ) {
		global $wpdb;

		if ( empty( $site['address'] ) ) {
			wp_die( __( 'Missing site address.' ) );
		}

		if ( empty( $site['email'] ) ) {
			wp_die( __( 'Missing site administrator email.' ) );
		}

		if ( empty( $site['title'] ) ) {
			wp_die( __( 'Missing site title.' ) );
		}

		$email = sanitize_email( $site['email'] );

		if ( ! is_email( $email ) ) {
			wp_die( __( 'Invalid email address.' ) );
		}

		$address = explode( '//', $site['address'] );

		// Ensure a consistent scheme.
		if ( 1 == count( $address ) ) {
			$address = '//' . $address[0];
		} elseif ( 2 == count( $address ) ) {
			$address = '//' . $address[1];
		} else {
			wp_die( __( 'Invalid site address. More than one use of // was found' ) );
		}

		$address = parse_url( $address );

		// Add basic validation to the host. Something like localhost is not allowed.
		if ( 0 === substr_count( $address['host'], '.' ) ) {
			wp_die( __( 'Invalid site address. A domain should have at least one . character.' ) );
		} else {
			$site_domain = $address['host'];
		}

		// Paths should have trailing slashes and up to 2 segments.
		if ( empty( $address['path'] ) || '/' === $address['path'] ) {
			$site_path = '/';
		} else {
			$site_path = explode( '/', trim( $address['path'], '/' ) );

			if ( 3 <= count( $site_path ) ) {
				wp_die( __( 'Invalid site address. There should be no more than 2 segments in a path.' ) );
			}

			$site_path = trailingslashit( implode( '/', $site_path ) );
		}

		// Domains can have a-z, A-Z, 0-9, -, and .
		$domain = '';
		if ( preg_match( '|^([a-zA-Z0-9-.])+$|', $site_domain ) ) {
			$domain = strtolower( $site_domain );
		}

		// Paths can have a-z, A-Z, 0-9, -, and /
		$path = '';
		if ( preg_match( '|^([a-zA-Z0-9-/])+$|', $site_path ) ) {
			$path = strtolower( $site_path );
		}

		// Once the preg_match has been applied, we should error if any changes were made.
		if ( $domain !== $site_domain ) {
			wp_die( __( 'Invalid site address. Non standard characters were found in the domain name.' ) );
		}

		if ( $path !== $site_path ) {
			wp_die( __( 'Invalid site path. Non standard characters were found in the path name.' ) );
		}

		/**
		 * A general note that I'm removing some checks for subdomain installs as I
		 * don't yet believe the separation will be necessary for our use case. Once
		 * this is confirmed, we can go back through and add the checks as appropriate.
		 *
		 * It is very possible that the new checks can be filtered so that individual
		 * networks can determine what is supported.
		 */

		$subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed', 'wsu' ) );
		if ( in_array( $site_path, $subdirectory_reserved_names ) ) {
			wp_die( sprintf( __('The following words are reserved for use by WordPress functions and cannot be used as blog names: <code>%s</code>' ), implode( '</code>, <code>', $subdirectory_reserved_names ) ) );
		}

		// Build the desired domain
		if ( '' === $domain ) {
			$new_domain = get_current_site()->domain;
		} else {
			$new_domain = $domain . '.' . preg_replace( '|^www\.|', '', get_current_site()->domain );
		}

		if ( '' === $path ) {
			$new_path = get_current_site()->path;
		} else {
			$new_path = get_current_site()->path . $path . '/';
		}

		$password = 'N/A';
		$user_id = email_exists( $email );

		if ( ! $user_id ) { // Create a new user with a random password
			$password = wp_generate_password( 12, false );
			$user_id = wpmu_create_user( $domain, $password, $email );
			if ( false == $user_id )
				wp_die( __( 'There was an error creating the user.' ) );
			else
				wp_new_user_notification( $user_id, $password );
		}

		$wpdb->hide_errors();
		$id = wpmu_create_blog( $new_domain, $new_path, $site['title'], $user_id , array( 'public' => 1 ), get_current_site()->id );
		$wpdb->show_errors();

		if ( is_wp_error( $id ) ) {
			wp_die( $id->get_error_message() );
		}

		if ( ! is_super_admin( $user_id ) && ! get_user_option( 'primary_blog', $user_id ) ) {
			update_user_option( $user_id, 'primary_blog', $id, true );
		}

		$content_mail = sprintf( __( 'New site created by %1$s

Address: %2$s
Name: %3$s' ), wp_get_current_user()->user_login , get_site_url( $id ), wp_unslash( $site['title'] ) );
		wp_mail( get_site_option('admin_email'), sprintf( __( '[%s] New Site Created' ), get_current_site()->site_name ), $content_mail, 'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>' );
		wpmu_welcome_notification( $id, $user_id, $password, $site['title'], array( 'public' => 1 ) );
		wp_redirect( add_query_arg( array( 'update' => 'added', 'id' => $id ), 'site-new.php' ) );

		exit;
	}

	/**
	 * A direct replacement for /wp-admin/network/site-new.php.
	 *
	 * By hooking in early enough, we are able to provide a complete replacement for the
	 * existing site-new.php. This allows us to modify the new site entry form to allow
	 * for both subdomain and subdirectory configuration for new sites.
	 */
	public function site_new_php() {
		global $title, $parent_file;

		if ( isset( $_GET['display'] ) && 'network' === $_GET['display'] ) {
			return;
		}

		if ( isset( $_REQUEST['action'] ) && 'add-network-site' === $_REQUEST['action'] ) {
			check_admin_referer( 'add-network-site', '_wpnonce_add-network-site' );

			if ( ! is_array( $_POST['site'] ) ) {
				wp_die( __( 'Can&#8217;t create an empty site.' ) );
			}

			$this->_create_new_site( $_POST['site'] );
		}

		if ( isset($_GET['update']) ) {
			$messages = array();
			if ( 'added' == $_GET['update'] ) {
				$messages[] = sprintf( __( 'Site added. <a href="%1$s">Visit Dashboard</a> or <a href="%2$s">Edit Site</a>' ), esc_url( get_admin_url( absint( $_GET['id'] ) ) ), network_admin_url( 'site-info.php?id=' . absint( $_GET['id'] ) ) );
			}
		}

		$title = __('Add New Site');
		$parent_file = 'sites.php';

		require( ABSPATH . 'wp-admin/admin-header.php' );

		?>

		<div class="wrap">
			<h2 id="add-new-site"><?php _e('Add New Site') ?></h2>
			<?php
			if ( ! empty( $messages ) ) {
				foreach ( $messages as $msg ) {
					echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
				}
			} ?>
			<form method="post" action="<?php echo network_admin_url('site-new.php?action=add-network-site'); ?>">
				<?php wp_nonce_field( 'add-network-site', '_wpnonce_add-network-site' ) ?>
				<table class="form-table" style="max-width: 720px;">
					<tr class="form-field form-required">
						<th scope="row"><?php _e( 'Site Address' ) ?></th>
						<td>
							<input name="site[address]" type="text" class="regular-text" style="width:470px;" title="<?php esc_attr_e( 'Address' ) ?>" value="" />
							<p class="description">This is some explanatory text about what can be put in the above area.</p>
						</td>
					</tr>
					<tr class="form-field form-required">
						<th scope="row"><?php _e( 'Site Title' ) ?></th>
						<td><input name="site[title]" type="text" class="regular-text" style="width: 470px;" title="<?php esc_attr_e( 'Title' ) ?>"/></td>
					</tr>
					<tr class="form-field form-required">
						<th scope="row"><?php _e( 'Admin Email' ) ?></th>
						<td><input name="site[email]" type="text" class="regular-text" style="width: 470px;" title="<?php esc_attr_e( 'Email' ) ?>"/></td>
					</tr>
				</table>
				<?php submit_button( __('Add Site'), 'primary', 'add-network-site' ); ?>
			</form>
		</div>
		<?php
		require( ABSPATH . 'wp-admin/admin-footer.php' );
		die();
	}

	/**
	 * Filter the content as a new site's first post is created.
	 *
	 * @return string Content to appear in site's first post.
	 */
	public function first_post_content() {
		$post_content = <<<HTML
This is the content for this site's first post.
HTML;

		return $post_content;
	}

	/**
	 * Filter the title of a new site's first post as it is created.
	 *
	 * @return string Title to appear on site's first post.
	 */
	public function first_post_title() {
		return 'First News Item';
	}

	/**
	 * Filter the content as a new site's first page is created.
	 *
	 * This page will be set as the static home page for the new site.
	 *
	 * @return string Content to appear on site's home page.
	 */
	public function first_page_content() {
		$page_content = <<<HTML
This is the content for this site's first page, which should become the home page.
HTML;

		return $page_content;
	}

	/**
	 * Filter the title of a new site's first page as it is created.
	 *
	 * @return string Title to appear on site's first page.
	 */
	public function first_page_title() {
		return 'Home Page';
	}
}
$wsuwp_new_site_administration = new WSUWP_New_Site_Administration();
