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

function wsu_create_new_site( $site ) {
	// Create a site with posted data.
}

add_action( 'network_admin_notices', 'wsu_new_site' );
function wsu_new_site() {

	// Take over the new site screen in WordPress
	if ( '/wp-admin/network/site-new.php' !== $_SERVER['DOCUMENT_URI'] )
		return;

	if ( isset( $_REQUEST['action'] ) && 'add-network-site' === $_REQUEST['action'] ) {
		check_admin_referer( 'add-network-site', '_wpnonce_add-network-site' );

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
