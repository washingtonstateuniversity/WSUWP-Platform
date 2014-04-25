<?php

class WSU_Admin_Header {

	/**
	 * Add required hooks.
	 */
	public function __construct() {
		add_action( 'admin_init',            array( $this, 'register_admin_color_schemes' ),  10 );
		add_action( 'admin_bar_init',        array( $this, 'set_user_networks'            ),  10 );
		add_action( 'admin_bar_menu',        array( $this, 'my_networks_menu'             ), 210 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts'        ),  10 );
	}

	/**
	 * Register a custom color scheme for the admin interface using WSU crimson.
	 *
	 * @todo See if we can tweak this to be better.
	 */
	public function register_admin_color_schemes() {
		wp_admin_css_color( 'coug', 'Cougars',
			WP_CONTENT_URL . '/mu-plugins/wsu-admin-bar/css/wsu-admin-colors-cougars.css',
			array( '#262b2d', '#981e32', '#0074a2', '#2ea2cc' )
		);
	}

	/**
	 * Add my networks menu to admin bar.
	 *
	 * Add a list of a user's networks to the wp_admin_bar object similar to how
	 * sites are provided under $wp_admin_bar->user->blogs
	 */
	public function set_user_networks() {
		global $wp_admin_bar;

		if ( ! isset( $wp_admin_bar->user->networks ) ) {
			$wp_admin_bar->user->networks = wsuwp_get_user_networks();
		}
	}

	/**
	 * Create a custom version of the WordPress admin bar
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The wp_admin_bar global, no need to return once modified
	 */
	public function my_networks_menu( $wp_admin_bar ) {
		/**
		 * This is really only useful to installations with multiple networks. If it is not
		 * a multi network setup, then we should leave the admin bar alone.
		 */
		if ( ! wsuwp_is_multi_network() ) {
			return;
		}

		$user_sites = wsuwp_get_user_sites( get_current_user_id() );

		/**
		 * If the user is a member of only one site, we can assume they are also a member of
		 * only one network. If this is the case *and* they are not a super admin of this
		 * network, which is properly determined during the page load, then we remove the
		 * My Sites menu entirely and return without creating a My Networks menu.
		 *
		 * This also catches a case in which a user has been added as a member of a network,
		 * but does not have access to any individual site yet. At that point, a blank
		 * admin bar will be displayed.
		 */
		if ( ! is_super_admin() && 1 >= count( $user_sites ) ) {
			$wp_admin_bar->remove_menu( 'my-sites' );
			return;
		}

		$user_networks = wsuwp_get_user_networks( get_current_user_id() );

		/**
		 * If a user is a member of only one network and they are not a super admin of that
		 * network—implied by the current page load, do not remove the My Sites menu or show
		 * the My Networks menu.
		 */
		if ( ! is_super_admin() && 1 >= count( $user_networks ) ) {
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
		$node_edit        = $wp_admin_bar->get_node( 'edit' );
		$node_site_name   = $wp_admin_bar->get_node( 'site-name'   );
		$node_comments    = $wp_admin_bar->get_node( 'comments'    );
		$node_new_content = $wp_admin_bar->get_node( 'new-content' );

		/**
		 * Remove the default menu items that we will be reordering.
		 */
		$wp_admin_bar->remove_menu( 'edit' );
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
			'title' => apply_filters( 'wsu_my_networks_title', 'My Networks' ),
			'href'  => admin_url( 'index.php?page=my-networks' ),
		) );

		/**
		 * Overwrite the previously set network name to remove the 'Network Admin:' text.
		 */
		if ( is_network_admin() ) {
			$node_site_name->title = get_current_site()->site_name;
		}

		/**
		 * Add the original menu items back to the admin bar now that we have our my-networks
		 * item in place.
		 */
		$wp_admin_bar->add_menu( $node_site_name   );
		$wp_admin_bar->add_menu( $node_comments    );
		$wp_admin_bar->add_menu( $node_new_content );

		if ( $node_edit ) {
			$wp_admin_bar->add_menu( $node_edit );
		}

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
			wsuwp_switch_to_network( $network->id );

			$wp_admin_bar->add_menu( array(
				'parent' => 'my-networks-list',
				'id'     => 'network-' . $network->id,
				'title'  => get_site_option( 'site_name' ),
				'href'   => network_admin_url(),
			));

			/**
			 * Only show a link to Network Dashboard if the user has the
			 * correct capabilities for managing this network.
			 */
			if ( current_user_can( 'manage_network', $network->id ) ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'network-' . $network->id,
					'id'     => 'network-' . $network->id . '-admin',
					'title'  => 'Network Dashboard',
					'href'   => network_admin_url(),
				));
			}

			// Add a sub group for the network menu that will contain sites
			$wp_admin_bar->add_group( array(
				'parent' => 'network-' . $network->id,
				'id'     => 'network-' . $network->id . '-list',
				'meta'   => array(
					'class' => current_user_can( 'manage_network', $network->id ) ? 'ab-sub-secondary' : '',
				),
			));

			$sites = wp_get_sites( array( 'network_id' => $network->id ) );

			// Add each of the user's sites from this specific network to the menu
			foreach( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );

				if ( ! is_super_admin() && ! is_user_member_of_blog() ) {
					continue;
				}

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

			wsuwp_restore_current_network();
		}
	}

	/**
	 * Enqueue CSS used in the admin bar to add proper dashicons.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wsu-admin-bar', WP_CONTENT_URL . '/mu-plugins/wsu-admin-bar/css/wsu-admin-bar.css' );
	}
}
new WSU_Admin_Header();