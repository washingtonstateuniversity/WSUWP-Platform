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
		add_action( 'admin_head', array( $this, 'admin_bar_css' ), 10 );
		add_action( 'wp_head', array( $this, 'admin_bar_css' ), 10 );
		add_action( 'admin_bar_init',        array( $this, 'set_user_networks'            ),  10 );
		add_action( 'admin_bar_menu',        array( $this, 'my_networks_menu'             ), 210 );
	}

	/**
	 * Output custom CSS for the admin bar whenever it is displayed.
	 */
	public function admin_bar_css() {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		?>
		<style type="text/css">
			#wpadminbar #wp-admin-bar-my-networks > .ab-item:before {
				top: 2px;
				content: '\f319';
			}
		</style>
		<?php
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
		 * only one network. If this is the case *and* they are not an admin of this network,
		 * then we remove the My Sites menu entirely and return without creating a My Networks menu.
		 *
		 * This also catches a case in which a user has been added as a member of a network,
		 * but does not have access to any individual site yet. At that point, a blank
		 * admin bar will be displayed.
		 */
		if ( ! wsuwp_is_network_admin( wp_get_current_user()->user_login ) && 1 >= count( $user_sites ) ) {
			$wp_admin_bar->remove_menu( 'my-sites' );
			return;
		}

		$user_networks = wsuwp_get_user_networks( get_current_user_id() );

		/**
		 * If a user is a member of only one network and they are not a super admin of that
		 * network—implied by the current page load, do not remove the My Sites menu or show
		 * the My Networks menu.
		 */
		if ( ! wsuwp_is_network_admin( wp_get_current_user()->user_login ) && 1 >= count( $user_networks ) ) {
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
		$node_view        = $wp_admin_bar->get_node( 'view' );
		$node_site_name   = $wp_admin_bar->get_node( 'site-name'   );

		// Children of the site-name node. Null if not is_admin()
		$node_view_site   = $wp_admin_bar->get_node( 'view-site' );
		$node_edit_site   = $wp_admin_bar->get_node( 'edit-site' );

		// Children of the site-name node. Null if is_admin()
		$node_dashboard  = $wp_admin_bar->get_node( 'dashboard' );
		$node_appearance = $wp_admin_bar->get_node( 'appearance' );
		$node_themes     = $wp_admin_bar->get_node( 'themes' );
		$node_customize  = $wp_admin_bar->get_node( 'customize' );
		$node_widgets    = $wp_admin_bar->get_node( 'widgets' );
		$node_menus      = $wp_admin_bar->get_node( 'menus' );

		$node_comments    = $wp_admin_bar->get_node( 'comments'    );
		$node_new_content = $wp_admin_bar->get_node( 'new-content' );

		/**
		 * Remove the default menu items that we will be reordering.
		 */
		$wp_admin_bar->remove_menu( 'edit' );
		$wp_admin_bar->remove_menu( 'view' );
		$wp_admin_bar->remove_menu( 'site-name'   );

		// Remove children of the site-name node.
		if ( is_admin() ) {
			$wp_admin_bar->remove_menu( 'view-site' );
			$wp_admin_bar->remove_menu( 'edit-site' );
		} else {
			$wp_admin_bar->remove_menu( 'dashboard' );
			$wp_admin_bar->remove_menu( 'appearance' );
			$wp_admin_bar->remove_menu( 'themes' );
			$wp_admin_bar->remove_menu( 'customize' );
			$wp_admin_bar->remove_menu( 'widgets' );
			$wp_admin_bar->remove_menu( 'menus' );
		}

		$wp_admin_bar->remove_menu( 'comments'    );
		$wp_admin_bar->remove_menu( 'new-content' );

		/**
		 * Insert a new menu item 'My {$name} Networks' on the left of the admin bar that will
		 * provide access to each of the user's networks. This title can be altered through the
		 * use of the `wsu_my_network_title` filter. By default it is 'My WSU Networks'
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

		// If the site name node is null, normally because we do not have access, rebuild the pieces.
		if ( null === $node_site_name && wsuwp_is_network_admin( wp_get_current_user()->user_login ) ) {
			$node_site_name = array(
				'id' => 'site-name',
				'title' => get_option( 'blogname' ),
				'href' => home_url(),
			);
			$node_site_name = $this->_set_node( $node_site_name );

			if ( is_admin() ) {
				$node_view_site = array(
					'id' => 'view-site',
					'title' => __( 'Visit Site' ),
					'parent' => 'site-name',
					'href' => home_url(),
				);
				$node_view_site = $this->_set_node( $node_view_site );

				$node_edit_site = array(
					'id' => 'edit-site',
					'title' => __( 'Edit Site' ),
					'parent' => 'site-name',
					'href' => network_admin_url( 'site-info.php?id=' . get_current_blog_id() ),
				);
				$node_edit_site = $this->_set_node( $node_edit_site );
			} else {
				$node_dashboard = array(
					'id' => 'dashboard',
					'title' => __( 'Dashboard' ),
					'parent' => 'site-name',
					'href' => admin_url(),
				);
				$node_dashboard = $this->_set_node( $node_dashboard );

				$node_appearance = array(
					'id' => 'appearance',
					'parent' => 'site-name',
					'group' => true,
				);
				$node_appearance = $this->_set_node( $node_appearance );

				$node_themes = array(
					'id' => 'themes',
					'title' => __( 'Themes' ),
					'parent' => 'appearance',
					'href' => admin_url( 'themes.php' ),
				);
				$node_themes = $this->_set_node( $node_themes );

				$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$node_customize = array(
					'id' => 'customize',
					'title' => __( 'Customize' ),
					'parent' => 'appearance',
					'href' =>  add_query_arg( 'url', urlencode( $current_url ), wp_customize_url() ),
				);
				$node_customize = $this->_set_node( $node_customize );

				$node_widgets = array(
					'id'     => 'widgets',
					'title'  => __( 'Widgets' ),
					'parent' => 'appearance',
					'href'   => admin_url( 'widgets.php' ),
				);
				$node_widgets = $this->_set_node( $node_widgets );

				$node_menus = array(
					'id' => 'menus',
					'title' => __( 'Menus' ),
					'parent' => 'appearance',
					'href' => admin_url( 'nav-menus.php' ),
				);
				$node_menus = $this->_set_node( $node_menus );
			}
		}

		/**
		 * Add the original menu items back to the admin bar now that we have our my-networks
		 * item in place.
		 */
		$wp_admin_bar->add_menu( $node_site_name   );

		// Add the children of the site-name menu.
		if ( is_admin() ) {
			$wp_admin_bar->add_menu( $node_view_site );
			$wp_admin_bar->add_menu( $node_edit_site );
		} else {
			$wp_admin_bar->add_menu( $node_dashboard );
			$wp_admin_bar->add_menu( $node_appearance );
			$wp_admin_bar->add_menu( $node_themes );
			$wp_admin_bar->add_menu( $node_customize );
			$wp_admin_bar->add_menu( $node_widgets );
			$wp_admin_bar->add_menu( $node_menus );
		}

		$wp_admin_bar->add_menu( $node_comments    );
		$wp_admin_bar->add_menu( $node_new_content );

		if ( $node_edit ) {
			$wp_admin_bar->add_menu( $node_edit );
		}

		if ( $node_view ) {
			$wp_admin_bar->add_menu( $node_view );
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
			$network_sites_added = 0;
			// Add each of the user's sites from this specific network to the menu
			foreach( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );

				if ( ! current_user_can( 'manage_network', $network->id ) && ! is_user_member_of_blog() ) {
					restore_current_blog();
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
				$network_sites_added++;
			}

			// If a user is a member of the network (likely the primary), but not a member
			// of any sites, we should remove that network menu entirely.
			if ( 0 === $network_sites_added ) {
				$wp_admin_bar->remove_menu( 'network-' . $network->id );
			}

			wsuwp_restore_current_network();
		}
	}

	/**
	 * Create an admin bar node.
	 *
	 * @param array $args List of arguments the node relies on.
	 *
	 * @return object Arguments in object form.
	 */
	private function _set_node( $args ) {
		$defaults = array(
			'id'     => false,
			'title'  => false,
			'parent' => false,
			'href'   => false,
			'group'  => false,
			'meta'   => array(),
		);
		$args = wp_parse_args( $args,  $defaults );

		return (object) $args;
	}
}
new WSU_Admin_Header();