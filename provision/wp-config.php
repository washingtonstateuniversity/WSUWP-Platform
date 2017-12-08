<?php
/**
 * The generated WordPress configuration for the WSUWP Platform
 */

// If this is a wp-cli request, manually set the HTTP host
if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = 'wp.wsu.test';
}

// The database name, username, password, and host for MySQL
// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wsuwp' );

/** MySQL database username */
define( 'DB_USER', 'wp' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wp' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

define( 'WP_DEBUG',     true  );
define( 'SCRIPT_DEBUG', true  );
define( 'WSU_LOCAL_CONFIG', true );

if ( ! defined( 'SAVEQUERIES' ) ) {
	define( 'SAVEQUERIES',  true  );
}

// Disable strict error reporting in PHP.
define( 'WSU_DISABLE_STRICT', true );

// Tell WSUWP Platform to use the root domain for cookies.
define( 'WSUWP_COOKIE_DOMAIN', 'auto' );

/**
 * Authentication Unique Keys and Salts. Changing these invalidates
 * existing cookies and forces reauthentication.
 *
 * Generated through https://api.wordpress.org/secret-key/1.1/salt/
 */
 define('AUTH_KEY',         'Xs|w>%Xy2ihO.;F/d=!pLij;YMw>B~_;td#x13z-`-8_Mmo hBfz,!cKf77fg6<k');
 define('SECURE_AUTH_KEY',  '<rzL3}[GrJdEHOB[kQX.j2fF,/c%~pU4u%j8b!}H1ncow>ikSr;N4ltpusq2WUcW');
 define('LOGGED_IN_KEY',    '9;<BxYyV[q+A-xX6o<j&u*V94p9_j.W`Zy3e7}<P_pJpN^8R^i):U8AH+!L&VC[=');
 define('NONCE_KEY',        'fC6v1.nxPbR^|~^tOY^k@.p&6HTx{}njb%eu_8d`>P3KIw: y_P0v@Alz}I-Auzz');
 define('AUTH_SALT',        'ACb;S-$zPv&8v$++~1Y-g@?$Y{F*#L/w5GnVCBSPi,K;7=`SuU1a&kNp/V8||h_#');
 define('SECURE_AUTH_SALT', '$ZnM{bh~;Y0{!_+WKK.o/B3R!>F*|S?Z[5ym{8 p<u`|UX*QE,Ziw{4qM5@4M0d|');
 define('LOGGED_IN_SALT',   'c#${xgu$!<3g#%>00c,]D>eiT5A~+*3a<+e|(s)ekB]t/B6 -Xf!~)4}W:BxAdl4');
 define('NONCE_SALT',       's1utQ<)h=lq6g73abFjG5R:-nt0xU1SS&D7r(alr=|pI(h[8u$Vz/3|4`%pMWtFO');

// URLs
define( 'WP_HOME',        'wp.wsu.test' );
define( 'WP_SITEURL',     'wp.wsu.test' );

// Load wp-content from parent directory
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/wp-content' );

// Define a salt for cache keys to avoid collisions and errors.
define( 'WP_CACHE_KEY_SALT', 'wsuwp-dev' );

// @todo add batcache config

// Capture the real client IP for requests through the load balancer.
if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// @todo add default theme config
//define( 'WP_DEFAULT_THEME', '{{ pillar['wsuwp-config']['default_theme'] }}' );

// @todo add local s3 uploads config

// Multisite / Multinetwork related config
define( 'MULTISITE',         true );
define( 'SUNRISE',           'on' );
define( 'WP_MULTI_NETWORK',  true );

// Disable theme and plugin editors in the dashboard.
define( 'DISALLOW_FILE_EDIT', true  );
define( 'DISALLOW_FILE_MODS', true );

$table_prefix  = 'wp_';

define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', 'utf8mb4_unicode_ci' );
define( 'WPLANG', '' );

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
