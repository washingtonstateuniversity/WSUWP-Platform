<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

define( 'WP_MEMORY_LIMIT', '128M' );

// Database
define( 'DB_NAME',     'wsuwp'     );
define( 'DB_USER',     'wp'        );
define( 'DB_PASSWORD', 'wp'        );
define( 'DB_HOST',     '127.0.0.1' );

// URLs
define( 'WP_HOME',        'http://wp.wsu.dev'           );
define( 'WP_SITEURL',     'http://wp.wsu.dev'           );
define( 'WP_CONTENT_URL', 'http://wp.wsu.dev/wp-content'           );

// Load wp-content from parent direc
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/wp-content' );

$batcache = false;

if ( isset( $_SERVER['WP_CLI_PHP_USED'] ) && ! isset( $_SERVER['HTTP_HOST'] ) )
	$_SERVER['HTTP_HOST'] = 'wp.wsu.dev';

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'w');
define('SECURE_AUTH_KEY',  'w');
define('LOGGED_IN_KEY',    'w');
define('NONCE_KEY',        'w');
define('AUTH_SALT',        'w');
define('SECURE_AUTH_SALT', 'w');
define('LOGGED_IN_SALT',   'w');
define('NONCE_SALT',       'w');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
if ( !defined( 'WP_DEBUG' ) )
	define('WP_DEBUG', false);

if ( !defined( 'SAVEQUERIES' ) )
	define('SAVEQUERIES', false);

/* That's all, stop editing! Happy blogging. */

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
