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
	 * Contains a bit of helper language data about our various roles.
	 *
	 * This is pretty ridiculous. :)
	 *
	 * @var array
	 */
	var $role_data = array(
		'Subscriber'    => array( 'a' => 'a'  ),
		'Author'        => array( 'a' => 'an' ),
		'Contributor'   => array( 'a' => 'a'  ),
		'Editor'        => array( 'a' => 'an' ),
		'Administrator' => array( 'a' => 'an' ),
	);

	/**
	 * Setup hooks.
	 */
	public function __construct() {
		add_action( 'admin_print_footer_scripts', array( $this, 'print_footer_scripts' ) );

		add_action( 'admin_action_adduser', array( $this, 'add_existing_user_to_site' ) );
		add_action( 'admin_action_createuser', array( $this, 'add_new_user_to_site' ) );

		add_filter( 'update_welcome_user_email', array( $this, 'network_welcome_user_email' ), 10, 4 );
	}

	/**
	 * Output inline scripts in the footer to help with messaging on user management pages.
	 */
	public function print_footer_scripts() {
		// On the new network user screen, we replace the messaging to indicate no notification will be sent.
		if ( 'user-network' === get_current_screen()->id ) {
			?><script>
				(function($){
					$('.form-table' ).find('td').last().html('<p class="description" style="max-width:640px;">Creating a new user on ' +
						'this page does not send any notification email. Please communicate with the new user as appropriate. If you ' +
						'would like a notification to be generated automatically, create the user at an individual site level.</p>');
				}(jQuery));
			</script><?php
		}

		// On the new site user screen, we replace "E-mail" with "E-mail or username" to aid in adding users.
		if ( 'user' === get_current_screen()->id ) {
			?><script>(function($){ $("label[for='adduser-email']").first().html('E-mail or Username'); }(jQuery));</script><?php
		}
	}

	/**
	 * Replace functionality at a single site level for adding an existing network user to a site.
	 *
	 * The majority of this code is from wp-admin/user-new.php. It has been altered to remove the
	 * handling of any activation keys or confirmation links. Instead, we assume that an existing
	 * user on the network can be added to a site on that network without much concern.
	 */
	public function add_existing_user_to_site() {
		if ( ! is_multisite() ) {
			wp_die( __( 'Multisite support is not enabled.' ) );
		}

		if ( ! current_user_can( 'create_users' ) ) {
			wp_die(__('You do not have sufficient permissions to add users to this network.'));
		}

		check_admin_referer( 'add-user', '_wpnonce_add-user' );

		$user_details = null;
		$blog_id = null;
		$role = null;

		if ( 'site-users-network' === get_current_screen()->id ) {
			if ( isset( $_REQUEST['id'] ) ) {
				$blog_id = absint( $_REQUEST['id'] );
			}

			if ( isset( $_REQUEST['newuser'] ) ) {
				$user_details = get_user_by( 'login', $_REQUEST['newuser'] );
			}

			if ( isset( $_REQUEST['new_role'] ) ) {
				$role = $_REQUEST['new_role'];
			}

			$redirect_fail = add_query_arg( array( 'update' => 'err_add_notfound', 'id' => $blog_id ), 'site-users.php' );
			$redirect_member = add_query_arg( array( 'update' => 'err_add_member', 'id' => $blog_id ), 'site-users.php' );
			$redirect_success = add_query_arg( array( 'update' => 'adduser', 'id' => $blog_id ), 'site-users.php' );
		} else {
			$blog_id = get_current_blog_id();

			if ( isset( $_REQUEST['email'] ) && false !== strpos($_REQUEST['email'], '@') ) {
				$user_details = get_user_by( 'email', $_REQUEST['email'] );
			} elseif ( isset( $_REQUEST['email'] ) ) {
				$user_details = get_user_by( 'login', $_REQUEST['email'] );
			}

			if ( isset( $_REQUEST['role'] ) ) {
				$role = $_REQUEST['role'];
			}

			$redirect_fail = add_query_arg( array( 'update' => 'does_not_exist' ), 'user-new.php' );
			$redirect_member = add_query_arg( array( 'update' => 'addexisting' ), 'user-new.php' );
			$redirect_success = add_query_arg( array( 'update' => 'addnoconfirmation' ), 'user-new.php' );
		}

		if ( ! $user_details ) {
			wp_redirect( $redirect_fail );
			die();
		}

		if ( ! $blog_id ) {
			wp_die( __( 'A site ID was somehow not supplied.' ) );
		}

		if ( ! $role ) {
			wp_die( __( 'A role was somehow not supplied.' ) );
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
			$redirect = $redirect_member;
		} else {
			$this->add_user_to_site( $user_id, $role, $new_user_email, true, $blog_id );
			$redirect = $redirect_success;
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

			if ( $user_id ) {
				// This user already exists, so add them to the site.
				$this->add_user_to_site( $user_id, $_REQUEST['role'], $_REQUEST['email'] );

				$redirect = add_query_arg( array( 'update' => 'addnoconfirmation' ), 'user-new.php' );
				wp_redirect( $redirect );
				die();
			}

			$user_id = wpmu_create_user( $new_user_login, $password, $_REQUEST['email'] );

			// A cautious attempt at handling our inability to add a user?
			if ( ! $user_id ) {
				wp_die( 'Unable to add this user. Please try again.' );
			}

			$this->add_user_to_site( $user_id, $_REQUEST['role'], $_REQUEST['email'], false );

			$meta = array(
				'site_name' => get_option( 'blogname' ),
				'home_url' => home_url(),
				'admin_url' => admin_url(),
			);

			// Send a "welcome to the network" email to the user.
			// @todo allow this to be overridden in network settings
			wpmu_welcome_user_notification( $user_id, $password, $meta );
			/**
			 * Fires immediately after a new user is activated.
			 *
			 * @since MU
			 *
			 * @param int   $user_id  User ID.
			 * @param int   $password User password.
			 * @param array $meta     Signup meta data.
			 */
			do_action( 'wpmu_activate_user', $user_id, $password, $meta );

			$redirect = add_query_arg( array( 'update' => 'addnoconfirmation' ), 'user-new.php' );
			wp_redirect( $redirect );
			die();
		}
	}

	/**
	 * Output "a" or "an" before the role name?
	 *
	 * @param string $role_name Name of the role being output.
	 *
	 * @return string a or an
	 */
	private function get_role_a( $role_name ) {
		if ( isset( $this->role_data[ $role_name ] ) ) {
			return $this->role_data[ $role_name ]['a'];
		} else {
			return 'a';
		}
	}

	/**
	 * Add a user to the current site and send an email if applicable.
	 *
	 * @param int    $user_id        User ID being added to the site.
	 * @param string $requested_role Role for the user on this site.
	 * @param string $user_email     User's email address for notification.
	 * @param bool   $confirmation   Defer to admin request for confirmation email. Default true. False avoids email.
	 * @param int    $blog_id        Site ID to add the user to. Default is 0, which causes current blog.
	 */
	private function add_user_to_site( $user_id, $requested_role, $user_email, $confirmation = true, $blog_id = 0 ) {
		if ( 0 === $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		switch_to_blog( $blog_id );
		add_existing_user_to_blog( array( 'user_id' => $user_id, 'role' => $requested_role ) );
		restore_current_blog();

		if ( ! isset( $_POST['noconfirmation'] ) && true === $confirmation ) {
			// send a welcome email, not a registration email.
			$roles = get_editable_roles();
			$role = $roles[ $requested_role ];

			// 1 = site name, 2 = URL, 3 = role, 4 = login URL, 5 = a vs an
			$message = 'Hello,

You are now %5$s %3$s at %1$s.

Visit this site at %2$s and login with your WSU Network ID at %4$s

Welcome!

- WSUWP Platform (wp.wsu.edu)
';
			$message = sprintf( $message, get_option( 'blogname' ), home_url(), wp_specialchars_decode( translate_user_role( $role['name'] ) ), admin_url(), $this->get_role_a( $role['name'] ) );
			wp_mail( $user_email, sprintf( __( '[%s] Welcome Email' ), wp_specialchars_decode( get_option( 'blogname' ) ) ), $message );
		}
	}

	/**
	 * Provide a default email to send when welcoming a user to a network.
	 *
	 * @param string $welcome_email The network welcome email.
	 * @param int    $user_id       The user's ID. Unused.
	 * @param string $password      The user's password. Unused.
	 * @param array  $meta          Meta information about the new site.
	 *
	 * @return string The modified network welcome email.
	 */
	public function network_welcome_user_email( $welcome_email, $user_id, $password, $meta ) {
		$welcome_email = sprintf( 'Hi,

A new account has been set up for your WSU Network ID (USERNAME) on SITE_NAME.

This account was created when you were added as a member of %1$s, located at %2$s.

You can login to %1$s with your WSU Network ID and password at %3$s

Welcome!

- WSUWP Platform (wp.wsu.edu)', $meta['site_name'], $meta['home_url'], $meta['admin_url'] );

		return $welcome_email;
	}
}
new WSU_User_Management();