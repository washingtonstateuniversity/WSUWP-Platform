<?php

// Database
define( 'DB_NAME',     'wsuwp'     );
define( 'DB_USER',     'wp'        );
define( 'DB_PASSWORD', 'wp'        );
define( 'DB_HOST',     'localhost' );

// Development environment specific
define( 'WP_DEBUG',    true );
define( 'SAVEQUERIES', true );

define( 'WSU_DISABLE_STRICT', true );
define( 'WSU_LOCAL_CONFIG'  , true );

// Caching
define( 'WP_CACHE',          false    );
define( 'WP_CACHE_KEY_SALT', 'wpwsu' );

// URLs
define( 'WP_HOME',        'http://wp.wsu.edu'           );
define( 'WP_SITEURL',     'http://wp.wsu.edu/wordpress' );
define( 'WP_CONTENT_URL', 'http://content.wp.wsu.edu'   );

// Load wp-content from parent direc
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/content' );

$batcache = false;