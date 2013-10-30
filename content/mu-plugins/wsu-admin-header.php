<?php
/*
Plugin Name: WSU Admin Bar
Plugin URI: http://web.wsu.edu/
Description: Modifies the WordPress admin bar.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

class WSU_Admin_Header {

	/**
	 * Add required hooks.
	 */
	public function __construct() {
		add_action( 'admin_bar_init', array( $this, 'set_user_networks' ),  10 );
		add_action( 'admin_bar_menu', array( $this, 'my_networks_menu'  ), 210 );
	}

	/**
	 * Add my networks menu to admin bar.
	 *
	 * Add a list of a user's networks to the wp_admin_bar object similar to how
	 * sites are provided under $wp_admin_bar->user->blogs
	 */
	public function set_user_networks() {
		global $wp_admin_bar;

		if ( ! isset( $wp_admin_bar->user->networks ) )
			$wp_admin_bar->user->networks = wp_get_user_networks();
	}

	/**
	 * Create a custom version of the WordPress admin bar
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The wp_admin_bar global, no need to return once modified
	 */
	public function my_networks_menu( $wp_admin_bar ) {
		global $current_site;

		/**
		 * This is really only useful to installations with multiple networks. If it is not
		 * a multi network setup, then we should leave the admin bar alone.
		 */
		if ( ! is_multi_network() )
			return;

		$user_sites = wp_get_user_sites( get_current_user_id() );

		/**
		 * The user is not a super admin and they only belong to one network. At this point
		 * it's most likely that we should not give them access to a networks menu. If they
		 * only belong to one site as well, then we should remove the My Sites menu item to
		 * avoid confusion.
		 *
		 * @todo - determine real capabilities here rather than the blanket super admin role
		 */
		if ( ! is_super_admin() && 1 === count( $user_sites ) ) {
			$wp_admin_bar->remove_menu( 'my-sites' );
			return;
		} elseif ( ! is_super_admin() ) {
			return;
		}

		/**
		 * Remove the default My Sites menu, as we will be grouping sites under networks
		 * in a custom menu.
		 */
		$wp_admin_bar->remove_menu( 'my-sites' );

		/**
		 * Cache each of the existing nodes that represent the current default admin
		 * menu items so that we can use them when reordering.
		 */
		$node_site_name   = $wp_admin_bar->get_node( 'site-name'   );
		$node_comments    = $wp_admin_bar->get_node( 'comments'    );
		$node_new_content = $wp_admin_bar->get_node( 'new-content' );

		/**
		 * Remove the default menu items that we will be reordering.
		 */
		$wp_admin_bar->remove_menu( 'site-name'   );
		$wp_admin_bar->remove_menu( 'comments'    );
		$wp_admin_bar->remove_menu( 'new-content' );

		/**
		 * Insert a new menu item 'My {$name} Networks' on the left of the admin bar that will
		 * provide access to each of the user's networks. This title can be altered through the
		 * use of the `wsu_my_network_title` filter. By default it is 'My WSU Networks'
		 *
		 * @todo - insert logic that ignores this if the user is only a member of one network (or site)
		 */
		$wp_admin_bar->add_menu( array(
			'id'    => 'my-networks',
			'title' => apply_filters( 'wsu_my_networks_title', 'My WSU Networks' ),
			'href'  => admin_url( 'index.php?page=my-wsu-networks' ),
		) );

		/**
		 * Add the original menu items back to the admin bar now that we have our my-networks
		 * item in place.
		 */
		$wp_admin_bar->add_menu( $node_site_name   );
		$wp_admin_bar->add_menu( $node_comments    );
		$wp_admin_bar->add_menu( $node_new_content );

		/**
		 * Now that we have a My Networks menu, we should generate a list of networks to output
		 * under that menu. The existing logic displays all blogs that the user is a member of.
		 * We'll need to alter this to show sites (networks) instead, and then list the blogs
		 * as sub menus of those.
		 */
		$wp_admin_bar->add_group( array(
			'parent' => 'my-networks',
			'id'     => 'my-networks-list',
			'meta'   => array(),
		));

		// Add each of the user's networks as a menu item
		foreach( (array) $wp_admin_bar->user->networks as $network ) {
			switch_to_network( $network->id );

			$wp_admin_bar->add_menu( array(
				'parent' => 'my-networks-list',
				'id'     => 'network-' . $network->id,
				'title'  => $current_site->site_name,
				'href'   => network_admin_url(),
			));

			$wp_admin_bar->add_menu( array(
				'parent' => 'network-' . $network->id,
				'id'     => 'network-' . $network->id . '-admin',
				'title'  => 'Network Dashboard',
				'href'   => network_admin_url(),
			));

			// Add a sub group for the network menu that will contain sites
			/** @todo something different than is_super_admin here */
			$wp_admin_bar->add_group( array(
				'parent' => 'network-' . $network->id,
				'id'     => 'network-' . $network->id . '-list',
				'meta'   => array(
					'class' => is_super_admin() ? 'ab-sub-secondary' : '',
				),
			));

			$sites = wp_get_sites( array( 'network_id' => $network->id ) );

			// Add each of the user's sites from this specific network to the menu
			foreach( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );
				$site_details = get_blog_details();

				$blavatar = '<div class="blavatar"></div>';

				$menu_id  = 'site-' . $site['blog_id'];

				$wp_admin_bar->add_menu( array(
					'parent'    => 'network-' . $network->id . '-list',
					'id'        => $menu_id,
					'title'     => $blavatar . $site_details->blogname,
					'href'      => admin_url(),
				) );

				$wp_admin_bar->add_menu( array(
					'parent' => $menu_id,
					'id'     => $menu_id . '-d',
					'title'  => __( 'Dashboard' ),
					'href'   => admin_url(),
				) );

				if ( current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
					$wp_admin_bar->add_menu( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-n',
						'title'  => __( 'New Post' ),
						'href'   => admin_url( 'post-new.php' ),
					) );
				}

				if ( current_user_can( 'edit_posts' ) ) {
					$wp_admin_bar->add_menu( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-c',
						'title'  => __( 'Manage Comments' ),
						'href'   => admin_url( 'edit-comments.php' ),
					) );
				}

				$wp_admin_bar->add_menu( array(
					'parent' => $menu_id,
					'id'     => $menu_id . '-v',
					'title'  => __( 'Visit Site' ),
					'href'   => home_url( '/' ),
				) );

				restore_current_blog();
			}

			restore_current_network();
		}
	}
}
new WSU_Admin_Header();
