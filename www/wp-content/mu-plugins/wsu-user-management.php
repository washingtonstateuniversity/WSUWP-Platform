<?php
/*
 * Plugin Name: WSUWP User Management
 * Plugin URI: http://web.wsu.edu
 * Description: Aids in making user management a better experience in WordPress multisite.
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: http://web.wsu.edu
 * Version: 0.1
 * Network: true
 */

/**
 * Class WSU_User_Management
 */
class WSU_User_Management {

	/**
	 * Setup hooks.
	 */
	public function __construct() {
		add_action( 'admin_action_adduser', array( $this, 'add_existing_user_to_site' ) );
	}

	/**
	 * Replace functionality at a single site level for adding an existing network user to a site.
	 *
	 * The majority of this code is from wp-admin/user-new.php. It has been altered to remove the
	 * handling of any activation keys or confirmation links. Instead, we assume that an existing
	 * user on the network can be added to a site on that network without much concern.
	 */
	public function add_existing_user_to_site() {
		global $blog_id;

		if ( ! is_multisite() ) {
			wp_die( __( 'Multisite support is not enabled.' ) );
		}

		if ( ! current_user_can( 'create_users' ) ) {
			wp_die(__('You do not have sufficient permissions to add users to this network.'));
		}

		check_admin_referer( 'add-user', '_wpnonce_add-user' );

		$user_details = null;

		if ( false !== strpos($_REQUEST['email'], '@') ) {
			$user_details = get_user_by( 'email', $_REQUEST['email'] );
		} else {
			$user_details = get_user_by( 'login', $_REQUEST['email'] );
		}

		if ( ! $user_details ) {
			wp_redirect( add_query_arg( array( 'update' => 'does_not_exist' ), 'user-new.php' ) );
			die();
		}

		if ( ! current_user_can( 'promote_user', $user_details->ID ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Adding an existing user to this blog
		$new_user_email = $user_details->user_email;
		$username = $user_details->user_login;
		$user_id = $user_details->ID;

		if ( ( $username != null && ! is_super_admin( $user_id ) ) && ( array_key_exists( $blog_id, get_blogs_of_user( $user_id ) ) ) ) {
			// "That user is already a member of this site."
			$redirect = add_query_arg( array('update' => 'addexisting'), 'user-new.php' );
		} else {
			add_existing_user_to_blog( array( 'user_id' => $user_id, 'role' => $_REQUEST['role'] ) );
			$redirect = add_query_arg( array('update' => 'addnoconfirmation'), 'user-new.php' );

			if ( ! isset( $_POST['noconfirmation'] ) ) {
				// send a welcome email, not a registration email.
				$roles = get_editable_roles();
				$role = $roles[ $_REQUEST['role'] ];
				// 1 = site name, 2 = URL, 3 = role
				$message = __( 'Hi,

You have been added to \'%1$s\' with the role of %3$s.

Visit the site at %2$s and login with your WSU Network ID at %4$s

Welcome!

- WSUWP Platform (wp.wsu.edu)
' );
				wp_mail( $new_user_email, sprintf( __( '[%s] Welcome Email' ), wp_specialchars_decode( get_option( 'blogname' ) ) ), sprintf( $message, get_option( 'blogname' ), home_url(), wp_specialchars_decode( translate_user_role( $role['name'] ) ), admin_url() ) );
			}
		}
		wp_redirect( $redirect );
		die();
	}
}
new WSU_User_Management();