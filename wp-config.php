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

/**
 * Having a local configuration file allows us to specify some constants that
 * are specific to our development environment and should never make their way
 * to production.
 *
 * In the same vein, production information should never make its way into the
 * repository. The process of filling in the required information will occur
 * during deployment.
 */
if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
	include( dirname( __FILE__ ) . '/local-config.php' );
} else {
	include( dirname( __FILE__ ) . '/remote-config.php' );
}

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/* Multisite */
#define( 'WP_ALLOW_MULTISITE',   true         );
#define( 'MULTISITE',            true         );
#define( 'SUBDOMAIN_INSTALL',    true         );
#define( 'DOMAIN_CURRENT_SITE',  'wp.wsu.edu' );
#define( 'PATH_CURRENT_SITE',    '/'          );
#define( 'SITE_ID_CURRENT_SITE', 1            );
#define( 'BLOG_ID_CURRENT_SITE', 1            );

/* Sunruse */
#define( 'SUNRISE', 'on' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'H-r?_c%0|#4 Off9FKnWyyKo3|/7ui+TzzBjw21|Kn[VD@lx7AITmfN/:~I^AEi/');
define('SECURE_AUTH_KEY',  '3eYsq@HF!W-U?ETQS@TLz1];|y%H}!`]<.$xD:O,h4f-kghnJ0O2([>lI?=%P.7+');
define('LOGGED_IN_KEY',    'k99.yH(wxIs-W%/$/<|a`J| Gw/ktV2tL|f8dNu[xBf/gM;>Lfq!Od$7+-nqdA+N');
define('NONCE_KEY',        'v9+m]AtF$)8NibA/UDW&%FnN r&9PIeZ~%OJcl-5>}|o$gOoco&iw.jF?~#z4)>s');
define('AUTH_SALT',        's`NZa/|+ec$_s&+[-hV0r>m`hsjH<[nDkM+E{YRA~bF4R-sf]FBJFVvokq=@^cU`');
define('SECURE_AUTH_SALT', '3?v3@_69S_@<5xs?Q,XdxUdUfHeabID-24N~6xW=59:A6Xi0{w:Oca rS1qIX>xV');
define('LOGGED_IN_SALT',   ')|?8xhLTO1XVMkA1TwE%^n5ZH&F?R;Rh 6~[5-&-*&95~/K(Q<EZC+q_Ix~!lhzP');
define('NONCE_SALT',       '+1!jXxpop{7wm&Plh9|8>@eE3~V<XkxxT7Fsb-Gs6y2MPe]<}mGOXmu]y_)&>!Pn');

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

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/wordpress/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
