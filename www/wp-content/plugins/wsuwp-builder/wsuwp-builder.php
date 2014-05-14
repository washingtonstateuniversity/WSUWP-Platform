<?php
/*
Plugin Name: Page Builder Template
Version: 0.0.1
Plugin URI: http://web.wsu.edu
Description: Enables the page builder template in the spine parent theme.
Author: washingtonstateuniversity, jeremyfelt
Author URI: http://web.wsu.edu
*/

// Enable the spine parent theme's builder templates.
add_filter( 'spine_enable_builder_module', '__return_true' );