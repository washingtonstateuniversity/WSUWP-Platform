<?php
/*
Plugin Name: WSUWP Single Sign On Authentication
Version: 1.6
Plugin URI: http://web.wsu.edu
Description: Manages authentication for Washington State University WordPress installations.
Author: washingtonstateuniversity, jeremyfelt, Nate Owen
Author URI: http://web.wsu.edu
*/

class WSUWP_SSO_Authentication {

	/**
	 * @var string URL of the main WSU login form that users are redirected to for authentication.
	 */
	var $auth_login_url = 'https://secure.wsu.edu/login/fidlogin.aspx';

	/**
	 * @var string URL credentials are validated against.
	 */
	var $auth_validate_url = 'https://secure.wsu.edu/login-server/auth-validate.asp';

	/**
	 * @var string URL to destroy WSU authentication cookies.
	 */
	var $auth_logout_url = 'https://secure.wsu.edu/login-server/logout.asp';

	/**
	 * @var string Version to use for cache breaking scripts and stylesheets.
	 */
	var $script_version = '1.6.0';

	/**
	 * Add hooks required for this plugin.
	 */
	public function __construct() {
		// Basic login and logout actions.
		add_action( 'login_init',        array( $this, 'login'  ), 11 );
		add_action( 'login_form_logout', array( $this, 'logout' )     );

		// Enqueue Javascript and custom stylesheets
		add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Capture and enforce user additions and changes.
		add_action( 'user_profile_update_errors', array( $this, 'user_profile_update_errors' ),  10, 1 );
		add_action( 'user_profile_update_errors', array( $this, 'user_profile_update_role'   ), 999, 3 );

		// Modify the header logo, URL, and link title displayed on wp-login.php
		add_action( 'login_head',        array( $this, 'login_head_css'     ), 10    );
		add_filter( 'login_headerurl',   array( $this, 'login_header_url'   ), 10, 1 );
		add_filter( 'login_headertitle', array( $this, 'login_header_title' ), 10, 1 );

		// Add messaging to direct toward Network ID authentication.
		add_filter( 'login_message',     array( $this, 'login_message'      ), 10, 1 );
	}

	/**
	 * Enqueue the stylesheet and Javascript required for custom login functionality.
	 */
	public function login_enqueue_scripts() {
		wp_enqueue_style(  'wsuwp-login', plugins_url( 'css/wsuwp-login.css', __FILE__ ), array(), $this->script_version );
		wp_enqueue_script( 'wsuwp-login', plugins_url( 'js/wsuwp-login.js', __FILE__ ), array( 'jquery' ), $this->script_version, true );
	}

	/**
	 * Enqueue the Javascript required for custom auth maintenance in the admin area.
	 *
	 * This script is loaded in wp-admin/profile.php, wp-admin/user-edit.php, and wp-admin/user-new.php
	 */
	public function admin_enqueue_scripts() {
		if ( in_array( get_current_screen()->base, array( 'user', 'user-edit', 'profile' ) ) ) {
			wp_enqueue_script( 'wsuwp-auth.js', plugins_url( 'js/wsuwp-auth.js', __FILE__ ), array( 'jquery' ), $this->script_version, true );
		}
	}

	/**
	 * Enforce strong passwords when a user profile is edited.
	 *
	 * @param WP_Error $errors Errors generated during the profile update process.
	 */
	public function user_profile_update_errors( $errors ) {
		if ( '' === $_POST['pass1'] && '' === $_POST['pass2'] ) {
			return;
		}

		if ( ! isset( $_POST['wsuwp_pass_strength'] ) || 'Strong' !== $_POST['wsuwp_pass_strength'] ) {
			$errors->add( 'pass', '<strong>Error</strong>: Passwords not rated <strong>strong</strong> may not be used.' );
		}
	}

	/**
	 * Detect role changes and enforce a random password if a secure role is being set. By default,
	 * this applies to the administrator role. A filter is available to enforce multiple secure roles.
	 *
	 * @param WP_Error $errors Any current errors that have been compiled.
	 * @param bool     $update Indicates if this is an update procedure.
	 * @param Object   $user   Current user data being built for update/add.
	 */
	public function user_profile_update_role( $errors, $update, $user ) {
		// If errors have already been generated, we shouldn't do anything.
		if ( ! empty( $errors->errors ) ) {
			return;
		}

		$ad_auth_roles = apply_filters( 'wsuwp_sso_ad_auth_roles', array( 'administrator' ) );

		// Be forceful about the administrator requirement.
		if ( ! is_array( $ad_auth_roles ) ) {
			$ad_auth_roles = array( 'administrator' );
		} elseif ( ! in_array( 'administrator', $ad_auth_roles ) ) {
			$ad_auth_roles[] = 'administrator';
		}

		// If a user's profile is being updated an their role has been marked as secure, reset the password as it passes through.
		if ( isset( $_POST['role'] ) && isset( $user->role ) && in_array( 'administrator', $ad_auth_roles ) && in_array( $user->role, $ad_auth_roles ) ) {
			$user->user_pass = $this->generate_password();
			unset( $_POST['send_password'] ); // Don't send any password to the user.
		}
	}

	/**
	 * Generate a random junk password that will never be used based on a series of allowed characters.
	 *
	 * The generated password will be 32 characters long and contain no less than 24 unique characters.
	 *
	 * @return string Generated password.
	 */
	private function generate_password() {
		$junk_password_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 !"?$%^&).';
		$pass_count = 0;
		$junk_password = '';

		while ( $pass_count < 24 ) {
			$junk_password = '';
			for ( $i = 0; $i < 32; $i++ ) {
				$junk_password .= $junk_password_chars[ rand( 0, strlen( $junk_password_chars ) - 1 ) ];
			}
			$pass_count = strlen( count_chars( $junk_password, 3 ) );
		}

		return $junk_password;
	}

	/**
	 * Provide a link to authenticate through WSU SSO Authentication.
	 *
	 * @return string HTML output for authentication options.
	 */
	public function login_message() {
		if ( isset( $_GET['redirect_to'] ) ) {
			$redirect_to = urldecode( $_GET['redirect_to'] );
		} else {
			$redirect_to = '';
		}

		if ( isset( $_GET['reauth'] ) && '1' == $_GET['reauth'] ) {
			$reauth = true;
		} else {
			$reauth = false;
		}

		$output  = '<div class="wsu-login-choice"><a class="button-primary" rel="nofollow" href="' . esc_url( add_query_arg( 'wsu_sso_auth', '1', wp_login_url( $redirect_to, $reauth ) ) ) . '">Authenticate with WSU Network ID</a>';

		if ( true === apply_filters( 'wsuwp_sso_allow_wp_auth', false ) ) {
			$output .= '<span id="wsuwp-auth" class="button-primary">Authenticate with WordPress</span></div>';
		}

		return $output;
	}

	/**
	 * Add inline CSS to alter the display of the login header image.
	 */
	public function login_head_css() {
		?><style>.login h1 a { background-image: url('<?php echo esc_url( plugins_url( 'images/wsu-shield-140x140.png', __FILE__ ) ); ?>'); }</style><?php
	}

	/**
	 * Replace the default URL of wordpress.org with a WSU specific URL.
	 *
	 * @return string URL linked to in the logo.
	 */
	public function login_header_url() {
		return 'http://wsu.edu';
	}

	/**
	 * Replace the default wordpress.org text with Washington State University.
	 *
	 * @return string Text added to the link title.
	 */
	public function login_header_title() {
		return 'Washington State University';
	}

	/**
	 * Perform login operations when wp-login.php is loaded.
	 */
	public function login() {

		// Allow authentication with WordPress if filtered for `true`.
		if ( true === apply_filters( 'wsuwp_sso_allow_wp_auth', false ) ) {
			// Look for actual login attempts and process accordingly.
			if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) {
				if ( ! isset( $_POST['wp-submit'] ) || ! isset( $_POST['redirect_to'] ) ) {
					die(); // Intentional silence.
				} else {
					return;
				}
			}
		}

		// Allow a logout request to process properly.
		if ( isset( $_REQUEST['action'] ) && 'logout' === $_REQUEST['action'] ) {
			return;
		}

		// Check to see if a user is already logged in.
		$user = wp_get_current_user();

		// There should be no reason a user visits with a valid user ID, but it *could* be possible.
		if ( 0 != $user->ID ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		// This is a return from secure.wsu.edu and should be processed accordingly.
		if ( isset( $_GET['wsu_sso_auth'] ) ) {
			$this->process_sso_authentication();
		}
	}

	/**
	 * Process the steps required for SSO authentication to be successful.
	 */
	private function process_sso_authentication() {
		/* @var WP_Roles $wp_roles */
		global $wp_roles;

		// We can expect a valid username to be returned here.
		$ad_username = sanitize_user( $this->validate_authentication() );

		// If an empty Network Id is returned from the API request, we're in trouble.
		if ( '' == $ad_username ) {
			wp_die( "Authentication was successful, but an empty user name was returned. Please report this error to University Web Communication." );
		}

		// Determine if a user already exists with this Network ID as a username.
		$user = new WP_User( 0, $ad_username );

		if ( 0 < $user->ID ) {
			// A user exists, so cookies can be properly set.
			$this->set_authentication( $user->ID, $ad_username );
		} else {
			// A user does not exist and a decision to add the user as a subscriber should be made. This decision
			// can be made by individual sites through the use of a filter. By default, new users are not created.
			if ( apply_filters( 'wsuwp_sso_create_new_user', false ) ) {

				$junk_password = $this->generate_password();

				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}

				$current_roles = $wp_roles->get_names();
				$new_user_role = apply_filters( 'wsuwp_sso_new_user_role', 'subscriber' );

				// If a filtered role for new users does not exist or is an Administrator, reset to Subscriber.
				if ( ! in_array( $new_user_role, $current_roles ) || 'administrator' === $new_user_role ) {
					$new_user_role = 'subscriber';
				}

				$new_user_data = array(
					'user_pass'  => $junk_password,
					'user_login' => $ad_username,
					'role'       => $new_user_role,
				);
				$new_user_id = wp_insert_user( $new_user_data );

				if ( is_wp_error( $new_user_id ) ) {
					wp_die( "We tried to create a new user for you but the attempt was not successful. Please report this error to University Web Communication." );
				}

				do_action( 'wsuwp_sso_user_created', $new_user_id );

				$this->set_authentication( $new_user_id, $ad_username );
			}
		}

		wp_die( "Please contact your administrator to add you as a user to this WordPress installation." );
	}

	/**
	 * Set the current user and auth cookies before redirecting.
	 *
	 * @param int    $user_id  User ID of the user being authenticated.
	 * @param string $username Username of the user being authenticated.
	 */
	private function set_authentication( $user_id, $username ) {
		wp_set_current_user( $user_id, $username );
		wp_set_auth_cookie( $user_id );
		do_action( 'wp_login', $username );

		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		} else {
			$redirect_to = admin_url();
		}
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Validate authentication based on the user's session cookie.
	 *
	 * The data we receive back from a successful authentication request is
	 * the following array:
	 * 		[0]=> string(17) "Valid credentials"
	 * 		[1]=> string(11) "WSU ID: 111"
	 * 		[2]=> string(23) "Network ID: user.name"
	 * 		[3]=> string(30) "Created: 10/11/2013 4:50:38 PM"
	 * 		[4]=> string(31) "Accessed: 10/11/2013 4:51:59 PM"
	 * 		[5]=> string(0) ""
	 *
	 * We only use Network ID at this time, but this comment serves to track the structure and
	 * possible changes over time.
	 *
	 * @return string|void User's network ID if the session cookie valid. Redirects otherwise.
	 */
	private function validate_authentication() {
		$destination_url = add_query_arg( 'wsu_sso_auth', '1', network_site_url( $_SERVER['REQUEST_URI'] ) );
		$login_url = $this->auth_login_url . "?dest=" . urlencode( $destination_url );

		// If an AD cookie is not set, redirect to the active directory login site.
		if ( ! isset( $_COOKIE['pasessionid'] ) || '' == $_COOKIE['pasessionid'] ) {
			wp_redirect( $login_url );
			exit(0);
		}

		$authentication_url = $this->auth_validate_url . '?session_id=' . urlencode( $_COOKIE['pasessionid'] ) . '&client_address=' . urlencode( $_SERVER['REMOTE_ADDR'] );

		$output = wp_remote_get( $authentication_url );
		$output = wp_remote_retrieve_body( $output );

		// If the HTTP request is not successful, we're forced to go back to the login url.
		if ( is_wp_error( $output ) ) {
			wp_redirect( $login_url );
			exit(0);
		}

		// Even if output is only one line, this explode will ensure we're dealing with an array.
		$output = explode( PHP_EOL, $output );
		$output = array_map( 'trim', $output );

		if ( 'Valid credentials' == $output[0] ) {
			$key = 'Network ID: ';

			// Try to return a valid network ID.
			foreach ( $output as $line ) {
				if ( 0 == strncmp( $key, $line, strlen( $key ) ) ) {
					return substr( $line, strlen( $key ) );
				}
			}
		}

		// No key was found, redirect to the login URL.
		wp_redirect( $login_url );
		exit(0);
	}

	/**
	 * Log the user out of the session by calling the WSU AD API and by destroying the built in
	 * cookies provided by WordPress through wp_logout(). Once logged out, redirect to the home
	 * page of this site to avoid confusion.
	 */
	public function logout() {
		check_admin_referer('log-out');

		// If auth was completed via WSU Network ID, destroy that session as well.
		if ( isset( $_COOKIE['pasessionid'] ) ) {
			wp_remote_get( $this->auth_logout_url . '?session_id=' . urlencode( $_COOKIE['pasessionid'] ) . '&client_address=' . urlencode( $_SERVER['REMOTE_ADDR'] ) );
		}

		wp_logout();

		wp_safe_redirect( home_url() );
		exit;
	}

}
new WSUWP_SSO_Authentication();

/**
 * Determine if a user is authenticated with WordPress using their WSU Network ID.
 *
 * @return bool
 */
function wsuwp_is_user_logged_in() {
	// Check for a valid WordPress authentication first.
	if ( false === is_user_logged_in() ) {
		return false;
	}

	// Our best way of determining a current authentication is via $_COOKIE
	if ( ! isset( $_COOKIE['pasessionid'] ) || empty( $_COOKIE['pasessionid'] ) ) {
		return false;
	}

	return true;
}

/**
 * Return information about a user who has authenticated with their WSU Network ID.
 *
 * In the future, additional data can be attached to this user object. For now, it is
 * a WP_User object.
 *
 * @return bool|WP_User False if the user is not authenticated. The user's object if authenticated.
 */
function wsuwp_get_current_user() {
	if ( false === wsuwp_is_user_logged_in() ) {
		return false;
	}

	return wp_get_current_user();
}