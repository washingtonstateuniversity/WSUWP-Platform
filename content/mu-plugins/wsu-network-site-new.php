<?php

/*
* Plugin Name: WSU New Site Administration
* Plugin URI: http://web.wsu.edu
* Description: Replaces the default site-new.php in WordPress
* Author: washingtonstateuniversity, jeremyfelt
* Author URI: http://web.wsu.edu
* Version: 0.1
* Network: true
*/

function wsu_create_new_site( $site  ) {
	global $wpdb;

	if ( empty( $site['domain'] ) && empty( $site['path'] ) ) {
		wp_die( __( 'Missing site domain or path.' ) );
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

	// These use the standard preg_match from WordPress core as of 3.7.1
	// @todo revisit this standard
	$domain = '';
	if ( preg_match( '|^([a-zA-Z0-9-])+$|', $site['domain'] ) ) {
		$domain = strtolower( $site['domain'] );
	}

	$path = '';
	if ( preg_match( '|^([a-zA-Z0-9-])+$|', $site['path'] ) ) {
		$path = strtolower( $site['path'] );
	}

	// Once the preg_match has been applied, we should error if any changes were made.
	if ( $domain !== $site['domain'] ) {
		wp_die( __( 'Invalid site domain.' ) );
	}

	if ( $path !== $site['path'] ) {
		wp_die( __( 'Invalid site path.' ) );
	}

	/**
	 * A general note that I'm removing some checks for subdomain installs as I
	 * don't yet believe the separation will be necessary for our use case. Once
	 * this is confirmed, we can go back through and add the checks as appropriate.
	 *
	 * It is very possible that the new checks can be filtered so that individual
	 * networks can determine what is supported.
	 */

	$subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed' ) );
	if ( in_array( $site['path'], $subdirectory_reserved_names ) ) {
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

add_action( 'network_admin_notices', 'wsu_new_site' );
function wsu_new_site() {

	// Take over the new site screen in WordPress
	if ( '/wp-admin/network/site-new.php' !== $_SERVER['DOCUMENT_URI'] )
		return;

	if ( isset( $_REQUEST['action'] ) && 'add-network-site' === $_REQUEST['action'] ) {
		check_admin_referer( 'add-network-site', '_wpnonce_add-network-site' );

		if ( ! is_array( $_POST['site'] ) )
			wp_die( __( 'Can&#8217;t create an empty site.' ) );

		wsu_create_new_site( $_POST['site'] );
	}

	if ( isset($_GET['update']) ) {
		$messages = array();
		if ( 'added' == $_GET['update'] )
			$messages[] = sprintf( __( 'Site added. <a href="%1$s">Visit Dashboard</a> or <a href="%2$s">Edit Site</a>' ), esc_url( get_admin_url( absint( $_GET['id'] ) ) ), network_admin_url( 'site-info.php?id=' . absint( $_GET['id'] ) ) );
	}
	?>

	<div class="wrap">
		<?php screen_icon('ms-admin'); ?>
		<h2 id="add-new-site"><?php _e('Add New Site') ?></h2>
		<?php
		if ( ! empty( $messages ) ) {
			foreach ( $messages as $msg )
				echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
		} ?>
		<form method="post" action="<?php echo network_admin_url('site-new.php?action=add-network-site'); ?>">
			<?php wp_nonce_field( 'add-network-site', '_wpnonce_add-network-site' ) ?>
			<table class="form-table" style="max-width: 720px;">
				<tr class="form-field form-required">
					<th scope="row"><?php _e( 'Site Address' ) ?></th>
					<td>
						<table class="form-table">
							<tr class="form-field form-required">
								<th scope="row" style="width: 100px;"><?php _e( 'Site Domain' ); ?></th>
								<td>
									<input name="site[domain]" type="text" class="regular-text" style="width:200px;" title="<?php esc_attr_e( 'Domain' ) ?>" value="" />.<?php echo preg_replace( '|^www\.|', '', get_current_site()->domain ); ?>
								</td>
							</tr>
							<tr class="form-field form-required">
								<th scope="row" style="width: 100px;"><?php _e( 'Site Path' ); ?></th>
								<td>
									<?php
									echo get_current_site()->domain . get_current_site()->path ?><input name="site[path]" class="regular-text" type="text" style="width:200px;" title="<?php esc_attr_e( 'Domain' ) ?>"/>
								</td>
							</tr>
						</table>
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
