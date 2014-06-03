<?php
/*
 * Plugin Name: WSUWP Taxonomy Management
 * Plugin URI: http://web.wsu.edu
 * Description: Connects dots on various taxonomy tasks.
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: http://web.wsu.edu
 * Version: 0.1
 * Network: true
 */

/**
 * Class WSUWP_Taxonomy
 */
class WSUWP_Taxonomy {

	/**
	 * Add our hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_taxonomies_to_pages' ) );
		add_action( 'init', array( $this, 'add_taxonomies_to_media' ) );
	}

	/**
	 * Register built in taxonomies - Categories and Tags - to pages.
	 */
	public function add_taxonomies_to_pages() {
		register_taxonomy_for_object_type( 'category', 'page' );
		register_taxonomy_for_object_type( 'post_tag', 'page' );
	}

	/**
	 * Register built in taxonomies - Categories and Tags - to media.
	 */
	public function add_taxonomies_to_media() {
		register_taxonomy_for_object_type( 'category', 'attachment' );
		register_taxonomy_for_object_type( 'post_tag', 'attachment' );
	}
}
new WSUWP_Taxonomy();