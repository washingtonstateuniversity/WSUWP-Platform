<?php
/*
Plugin Name: WSU Deployment
Plugin URI: http://web.wsu.edu
Description: Receive deploy requests and act accordingly.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

class WSU_Deployment {

	/**
	 * @var string Slug to track the deployment post type.
	 */
	var $post_type_slug = 'wsuwp_deployment';

	/**
	 * @var string Slug to track deployment instances in a post type.
	 */
	var $deploy_instance_slug = 'wsuwp_depinstance';

	/**
	 * Add hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register the deployment and deployment instance post types to track
	 * the deployments that have been created and then initiated.
	 */
	public function register_post_type() {
		// Only enable this on the network's primary site.
		if ( ! is_main_site() || ! is_main_network() ) {
			return;
		}

		$labels = array(
			'name' => 'Deployments',
			'singular_name' => 'Deployment',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Deployment',
			'edit_item' => 'Edit Deployment',
			'new_item' => 'New Deployment',
			'all_items' => 'All Deployments',
			'view_item' => 'View Deployments',
			'search_items' => 'Search Deployments',
			'not_found' => 'No deployments found',
			'not_found_in_trash' => 'No deployments found in Trash',
			'menu_name' => 'Deployments',
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'deployment' ),
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', ),
		);
		register_post_type( $this->post_type_slug, $args );

		$instance_labels = array(
			'name' => 'Deployment Instances',
			'singular_name' => 'Deployment Instance',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Deployment Instance',
			'edit_item' => 'Edit Deployment Instance',
			'new_item' => 'New Deployment Instance',
			'all_items' => 'All Deployment Instances',
			'view_item' => 'View Deployment Instances',
			'search_items' => 'Search Deployment Instances',
			'not_found' => 'No deployment instances found',
			'not_found_in_trash' => 'No deployment instances found in Trash',
			'menu_name' => 'Deployment Instances',
		);

		$instance_args = array(
			'labels'             => $instance_labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'rewrite'            => array( 'slug' => 'deployment-instance' ),
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', ),
		);
		register_post_type( $this->deploy_instance_slug, $instance_args );

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	/**
	 * Capture the actual deployment information when notified from
	 * version control. This avoids the complete load of the template.
	 */
	public function template_redirect() {
		if ( ! is_singular( $this->post_type_slug ) ) {
			return;
		}

		// Capture actual deployment and then kill the page load.
		die();
	}
}
new WSU_Deployment();