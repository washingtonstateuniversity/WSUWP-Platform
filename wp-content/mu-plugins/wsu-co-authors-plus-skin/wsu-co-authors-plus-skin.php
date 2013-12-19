<?php
/*
 * Plugin Name: WSU Co Authors Plus Skin
 * Plugin URI: http://web.wsu.edu
 * Description: Loads a modified CSS file to better display Co-Authors Plus
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: http://web.wsu.edu
 * Version: 0.1
 * Network: true
 */

add_action( 'admin_enqueue_scripts', 'wsu_enqueue_scripts', 11 );
function wsu_enqueue_scripts() {
	if ( is_plugin_active( 'co-authors-plus/co-authors-plus.php' ) ) {
		wp_dequeue_style( 'co-authors-plus-css' );
		wp_enqueue_style( 'wsu-coauthors-plus',  plugins_url( '/css/wsu-coauthors-plus.css', __FILE__ ), array(), false, 'all' );
	}
}