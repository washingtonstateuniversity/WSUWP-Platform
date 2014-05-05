<?php
/*
Plugin Name: WSU Deployment
Plugin URI: http://web.wsu.edu
Description: Receive deploy requests in WordPress and act accordingly.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.2
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
		global $blog_id, $site_id;

		// Only enable this on the network's primary site.
		if ( 1 != $blog_id || 1 != $site_id ) {
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
			'show_ui'            => true,
			'rewrite'            => array( 'slug' => 'deployment-instance' ),
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', ),
		);
		register_post_type( $this->deploy_instance_slug, $instance_args );

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
	}

	/**
	 * Capture the actual deployment information when notified from
	 * version control. This avoids the complete load of the template.
	 */
	public function template_redirect() {
		if ( ! is_singular( $this->post_type_slug ) ) {
			return;
		}

		// Until we're certain, we should skip POST requests without a payload.
		if ( empty( $_POST['payload'] ) ) {
			die();
		}

		$deployment = get_post( get_the_ID() );

		// Capture actual deployment and then kill the page load.
		$title = time() . ' | ' . esc_html( $deployment->post_title );
		$args = array(
			'post_type' => $this->deploy_instance_slug,
			'post_title' => $title,
		);
		$instance_id = wp_insert_post( $args );

		// This is temporary until I can figure out the structure.
		$payload = wp_unslash( $_POST['payload'] );
		$payload = sanitize_meta( '_deploy_data', $payload, 'post' );
		$payload = maybe_serialize( $payload );
		$payload = maybe_unserialize( $payload );
		$payload = json_decode( $payload );

		$deployment_data = array(
			'tag' => false,
			'ref_type' => false,
			'sender' => false,
			'avatar_url' => false,
		);

		if ( isset( $payload->ref ) ) {
			$deployment_data['tag'] = $payload->ref;
		}

		if ( isset( $payload->ref_type ) ) {
			$deployment_data['ref_type'] = $payload->ref_type;
		}

		if ( isset( $payload->sender ) ) {
			$deployment_data['sender'] = $payload->sender->login;
			$deployment_data['avatar_url'] = $payload->sender->avatar_url;
		}

		add_post_meta( $instance_id, '_deploy_data', $deployment_data, true );

		$deployments = get_post_meta( get_the_ID(), '_deploy_instances', true );
		if ( ! is_array( $deployments ) ) {
			$deployments = array();
		}
		$deployments[ time() ] = absint( $instance_id );
		update_post_meta( get_the_ID(), '_deploy_instances', $deployments );
		die();
	}

	/**
	 * Add the meta boxes used by our deployment post types.
	 *
	 * @param $post_type
	 * @param $post
	 */
	public function add_meta_boxes( $post_type, $post ) {
		if ( $this->deploy_instance_slug !== $post_type && $this->post_type_slug !== $post_type ) {
			return;
		}

		add_meta_box( 'wsuwp_deploy_instances', 'Deploy Instances', array( $this, 'display_deploy_instances' ), $this->post_type_slug, 'normal' );
		add_meta_box( 'wsuwp_deploy_instance_data', 'Deploy Payload', array( $this, 'display_instance_payload' ), $this->deploy_instance_slug, 'normal' );
	}

	/**
	 * Display the deployment instances that have occurred on this
	 * deployment configuration.
	 *
	 * @param $post
	 */
	public function display_deploy_instances( $post ) {
		if ( $this->post_type_slug !== $post->post_type ) {
			return;
		}

		$deployments = get_post_meta( get_the_ID(), '_deploy_instances', true );
		if ( ! empty( $deployments ) ) {
			echo '<ul>';
			foreach ( $deployments as $time => $instance_id ) {
				$deploy_data = get_post_meta( $instance_id, '_deploy_data', true );
				if ( ! $deploy_data ) {
					$deploy_tag = 'View';
				} else {
					$deploy_tag = $deploy_data['tag'];
				}

				echo '<li>' . date( 'Y-m-d H:i:s', $time ) . ' | <a href="' . esc_html( admin_url( 'post.php?post=' . absint( $instance_id ) . '&action=edit') ) . '">' . esc_html( $deploy_tag ) . '</a></li>';
			}
			echo '<ul>';
		}
	}

	/**
	 * Display the payload data from a deployment in the instance meta box.
	 * @param $post
	 */
	public function display_instance_payload( $post ) {
		$deploy_data = get_post_meta( $post->ID, '_deploy_data', true );

		if ( isset( $deploy_data['tag'] ) ) {
			echo 'Tag: ' . esc_html( $deploy_data['tag'] ) . '<br />';
		}

		if ( isset( $deploy_data['ref_type'] ) ) {
			echo 'Ref Type: ' . esc_html( $deploy_data['ref_type'] ) . '<br />';
		}

		if ( isset( $deploy_data['sender'] ) ) {
			echo 'Author: ' . esc_html( $deploy_data['sender'] ) . '<br />';
			echo '<img src="' . esc_url( $deploy_data['avatar_url'] ) . '" style="height:50px; width: auto;">';
		}
	}
}
new WSU_Deployment();