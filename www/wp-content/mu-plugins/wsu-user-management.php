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
		add_action( 'admin_action_createuser', array( $this, 'add_new_user_to_site' ) );

		add_filter( 'update_welcome_user_email', array( $this, 'network_welcome_user_email' ) );
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
				$message = sprintf( $message, get_option( 'blogname' ), home_url(), wp_specialchars_decode( translate_user_role( $role['name'] ) ), admin_url() );
				wp_mail( $new_user_email, sprintf( __( '[%s] Welcome Email' ), wp_specialchars_decode( get_option( 'blogname' ) ) ), $message );
			}
		}
		wp_redirect( $redirect );
		die();
	}

	/**
	 * Handle users that are new to the site and the network.
	 *
	 * Incorporates code from wpmu_validate_signup() to help skip some of the standard user
	 * creation process.
	 */
	public function add_new_user_to_site() {
		global $add_user_errors;

		if ( ! is_multisite() ) {
			return;
		}

		if ( ! current_user_can( 'create_users' ) ) {
			wp_die( __( 'You do not have sufficient permissions to add users to this network.' ) );
		}

		check_admin_referer( 'create-user', '_wpnonce_create-user' );

		// Adding a new user to this site
		$user_details = wpmu_validate_user_signup( $_REQUEST[ 'user_login' ], $_REQUEST[ 'email' ] );

		if ( is_wp_error( $user_details[ 'errors' ] ) && !empty( $user_details[ 'errors' ]->errors ) ) {
			$add_user_errors = $user_details[ 'errors' ];
		} else {
			/**
			 * Filter the user_login, also known as the username, before it is added to the site.
			 *
			 * @since 2.0.3
			 *
			 * @param string $user_login The sanitized username.
			 */
			$new_user_login = apply_filters( 'pre_user_login', sanitize_user( wp_unslash( $_REQUEST['user_login'] ), true ) );

			// Disable the standard signup user notification email in ALL cases.
			add_filter( 'wpmu_signup_user_notification', '__return_false' );

			$password = wp_generate_password( 12, false );
			$user_id = username_exists( $new_user_login );

			if ( ! $user_id ) {
				$user_id = wpmu_create_user( $new_user_login, $password, $_REQUEST['email'] );
			} else {
				// This user already exists, so add them to the site.
				add_existing_user_to_blog( array( 'user_id' => $user_id, 'role' => $_REQUEST['role'] ) );
			}

			// A cautious attempt at handling our inability to add a user?
			if ( ! $user_id ) {
				wp_die( 'Unable to add this user. Please try again.' );
			}

			// Send a "welcome to the network" email to the user.
			// @todo allow this to be overridden in network settings
			wpmu_welcome_user_notification( $user_id, $password, array() );
			/**
			 * Fires immediately after a new user is activated.
			 *
			 * @since MU
			 *
			 * @param int   $user_id  User ID.
			 * @param int   $password User password.
			 * @param array $meta     Signup meta data.
			 */
			do_action( 'wpmu_activate_user', $user_id, $password, array() );

			$redirect = add_query_arg( array( 'update' => 'addnoconfirmation' ), 'user-new.php' );
			wp_redirect( $redirect );
			die();
		}
	}

	/**
	 * Provide a default email to send when welcoming a user to a network.
	 *
	 * @param string $welcome_email The network welcome email.
	 *
	 * @return string The modified network welcome email.
	 */
	public function network_welcome_user_email( $welcome_email ) {
		$welcome_email = 'Hi,

A new account has been set up for your WSU Network ID (USERNAME) on SITE_NAME.

You can login at LOGINLINK

Welcome!

- WSUWP Platform (wp.wsu.edu)';

		return $welcome_email;
	}
}
new WSU_User_Management();