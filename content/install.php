<?php
/*
 * Plugin Name: WSUWP Install Drop-In
 * Plugin URI: http://web.wsu.edu
 * Description: install.php drop-in to provide replacement functions for various install and upgrade features.
 * Author: washingtonstateuniversity, jeremyfelt
 * Author URI: http://web.wsu.edu
 * Version: 0.1
 */

/**
 * Modify the installation defaults when new sites are added to networks.
 *
 * This is a drop in replacement to the default wp_install_defaults() in WordPress.
 *
 * @see wp_install
 * @see wpmu_create_blog
 *
 * @param int $user_id User ID.
 */
function wp_install_defaults( $user_id ) {
	global $wpdb, $wp_rewrite, $table_prefix;

	// Default category
	$cat_name = __( 'Uncategorized' );
	/* translators: Default category slug */
	$cat_slug = sanitize_title( _x( 'Uncategorized', 'Default category slug' ) );

	$cat_id = 1;

	$wpdb->insert( $wpdb->terms, array( 'term_id' => $cat_id, 'name' => $cat_name, 'slug' => $cat_slug, 'term_group' => 0 ) );
	$wpdb->insert( $wpdb->term_taxonomy, array( 'term_id' => $cat_id, 'taxonomy' => 'category', 'description' => '', 'parent' => 0, 'count' => 1 ) );

	$cat_tt_id = $wpdb->insert_id;

	// First post
	$now = date( 'Y-m-d H:i:s' );
	$now_gmt = gmdate( 'Y-m-d H:i:s' );
	$first_post_guid = get_option( 'home' ) . '/?p=1';

	$post_content = 'Use the <code>wsuwp_first_post_content</code> filter to modify this content.';
	$post_content = apply_filters( 'wsuwp_first_post_content', $post_content );

	$post_title = apply_filters( 'wsuwp_first_title', 'First Post' );

	$wpdb->insert( $wpdb->posts, array(
		'post_author'           => $user_id,
		'post_date'             => $now,
		'post_date_gmt'         => $now_gmt,
		'post_content'          => $post_content,
		'post_excerpt'          => '',
		'post_title'            => $post_title,
		'post_name'             => sanitize_title( $post_title ),
		'post_modified'         => $now,
		'post_modified_gmt'     => $now_gmt,
		'guid'                  => $first_post_guid,
		'comment_count'         => 1,
		'to_ping'               => '',
		'pinged'                => '',
		'post_content_filtered' => ''
	));
	$wpdb->insert( $wpdb->term_relationships, array( 'term_taxonomy_id' => $cat_tt_id, 'object_id' => 1 ) );

	$page_content = 'Use the <code>wsuwp_first_page_content</code> filter to modify this content.';
	$page_content = apply_filters( 'wsuwp_first_page_content', $page_content );

	$page_title = apply_filters( 'wsuwp_first_page_title', 'First Page' );

	$first_post_guid = get_option('home') . '/?page_id=2';

	$wpdb->insert( $wpdb->posts, array(
		'post_author'           => $user_id,
		'post_date'             => $now,
		'post_date_gmt'         => $now_gmt,
		'post_content'          => $page_content,
		'post_excerpt'          => '',
		'post_title'            => $page_title,
		'post_name'             => sanitize_title( $page_title ),
		'post_modified'         => $now,
		'post_modified_gmt'     => $now_gmt,
		'guid'                  => $first_post_guid,
		'post_type'             => 'page',
		'comment_status'        => 'closed',
		'ping_status'           => 'closed',
		'to_ping'               => '',
		'pinged'                => '',
		'post_content_filtered' => '',
	));
	$wpdb->insert( $wpdb->postmeta, array( 'post_id' => 2, 'meta_key' => '_wp_page_template', 'meta_value' => 'default' ) );

	// Set up default widgets for default theme.
	update_option( 'widget_search',          array ( 2 => array ( 'title' => '' ), '_multiwidget' => 1 ) );
	update_option( 'widget_recent-posts',    array ( 2 => array ( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ) );
	update_option( 'widget_recent-comments', array ( 2 => array ( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ) );
	update_option( 'widget_archives',        array ( 2 => array ( 'title' => '', 'count' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
	update_option( 'widget_categories',      array ( 2 => array ( 'title' => '', 'count' => 0, 'hierarchical' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
	update_option( 'widget_meta',            array ( 2 => array ( 'title' => '' ), '_multiwidget' => 1 ) );
	update_option( 'sidebars_widgets',       array ( 'wp_inactive_widgets' => array (), 'sidebar-1' => array ( 0 => 'search-2', 1 => 'recent-posts-2', 2 => 'recent-comments-2', 3 => 'archives-2', 4 => 'categories-2', 5 => 'meta-2', ), 'sidebar-2' => array (), 'sidebar-3' => array (), 'array_version' => 3 ) );

	if ( ! is_super_admin( $user_id ) && ! metadata_exists( 'user', $user_id, 'show_welcome_panel' ) ) {
		update_user_meta( $user_id, 'show_welcome_panel', 2 );
	}

	// Flush rules to pick up the new page.
	$wp_rewrite->init();
	$wp_rewrite->flush_rules();

	$user = new WP_User($user_id);
	$wpdb->update( $wpdb->options, array('option_value' => $user->user_email), array('option_name' => 'admin_email') );

	// Remove all perms except for the login user.
	$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'user_level') );
	$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'capabilities') );

	// Delete any caps that snuck into the previously active blog. (Hardcoded to blog 1 for now.) TODO: Get previous_blog_id.
	if ( !is_super_admin( $user_id ) && $user_id != 1 ) {
		$wpdb->delete( $wpdb->usermeta, array( 'user_id' => $user_id , 'meta_key' => $wpdb->base_prefix.'1_capabilities' ) );
	}
}