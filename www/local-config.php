<?php

// Database
define( 'DB_NAME',     'wsuwp'     );
define( 'DB_USER',     'wp'        );
define( 'DB_PASSWORD', 'wp'        );
define( 'DB_HOST',     '127.0.0.1' );

// Development environment specific
define( 'WP_DEBUG',    true );
define( 'SAVEQUERIES', true );

define( 'WSU_DISABLE_STRICT', true );
define( 'WSU_LOCAL_CONFIG'  , true );

// Caching
define( 'WP_CACHE',          false    );
define( 'WP_CACHE_KEY_SALT', 'wpwsu'  );

// URLs
define( 'WP_HOME',        'http://wp.wsu.edu'           );
define( 'WP_SITEURL',     'http://wp.wsu.edu'           );
define( 'WP_CONTENT_URL', 'http://wp.wsu.edu/wp-content'           );

// Load wp-content from parent direc
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/wp-content' );

// Force a default theme.
define( 'WP_DEFAULT_THEME', 'twentythirteen' );

$batcache = false;

if ( isset( $_SERVER['WP_CLI_PHP_USED'] ) && ! isset( $_SERVER['HTTP_HOST'] ) )
	$_SERVER['HTTP_HOST'] = 'wp.wsu.edu';
