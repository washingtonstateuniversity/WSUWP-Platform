<?php

/**
 * Maintains the customized layout for the MS Sites List Table in WordPress.
 *
 * Class WSU_Network_Sites_List
 */
class WSU_Network_Sites_List {

	/**
	 * Add our hooks.
	 */
	public function __construct() {
		add_filter( 'wpmu_blogs_columns', array( $this, 'site_columns' ) );
		add_filter( 'manage_sites_action_links', array( $this, 'manage_sites_action_links' ), 10, 3 );
		add_filter( 'bulk_actions-sites-network', array( $this, 'manage_bulk_actions' ), 10, 1 );
		add_action( 'manage_sites_custom_column', array( $this, 'manage_sites_custom_column' ), 10, 2 );
	}

	/**
	 * Modify the default list of columns in the list table. We remove users
	 * and add it again only because it's a cheap and easy way to reorder
	 * the array as it is created.
	 *
	 * @param array $site_columns Columns used for displaying the table.
	 *
	 * @return array Modified list of columns.
	 */
	public function site_columns( $site_columns ) {
		unset( $site_columns['cb'] );
		unset( $site_columns['blogname'] );
		unset( $site_columns['lastupdated'] );
		unset( $site_columns['registered'] );
		unset( $site_columns['users'] );

		$site_columns['site_name'] = 'Site Name';
		$site_columns['site_url'] = 'URL';
		$site_columns['site_created'] = 'Created';
		$site_columns['site_users'] = 'Users';

		return $site_columns;
	}

	/**
	 * Display row data for our custom columns.
	 *
	 * @param string $column  The key of the column being displayed.
	 * @param int    $site_id The ID of the row's site.
	 */
	public function manage_sites_custom_column( $column, $site_id ) {
		if ( 'site_name' === $column ) {
			$this->display_site_name( $site_id );
		} elseif ( 'site_url' === $column ) {
			$this->display_site_url( $site_id );
		} elseif ( 'site_created' === $column ) {
			$this->display_site_created( $site_id );
		} elseif ( 'site_users' === $column ) {
			$this->display_site_users( $site_id );
		}
	}

	/**
	 * Remove all of the bulk actions displayed on the MS Sites List Table.
	 *
	 * @param array $actions Current bulk actions.
	 *
	 * @return array Modified bulk actions.
	 */
	public function manage_bulk_actions( $actions ) {
		return array();
	}

	/**
	 * Display the site name column for the row.
	 *
	 * This is copied almost directly from WordPress core to repurpose for
	 * our custom display.
	 *
	 * @param int $site_id ID of the row's site.
	 */
	private function display_site_name( $site_id ) {
		$site_name = esc_html( get_blog_option( $site_id, 'blogname' ) );

		?><a href="<?php echo esc_url( network_admin_url( 'site-info.php?id=' . absint( $site_id ) ) ); ?>" class="edit"><?php echo $site_name; ?></a><?php

		$actions = array();

		$actions['edit']	= '<span class="edit"><a href="' . esc_url( network_admin_url( 'site-info.php?id=' . $site_id ) ) . '">' . __( 'Edit' ) . '</a></span>';
		$actions['backend']	= "<span class='backend'><a href='" . esc_url( get_admin_url( $site_id ) ) . "' class='edit'>" . __( 'Dashboard' ) . '</a></span>';

		if ( get_current_site()->blog_id != $site_id ) {
			if ( current_user_can( 'delete_site', $site_id ) ) {
				$actions['delete']	= '<span class="delete"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=deleteblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to delete the site %s.' ), $site_name ) ) ), 'confirm') ) . '">' . __( 'Delete' ) . '</a></span>';
			}
		}

		$actions['visit']	= "<span class='view'><a href='" . esc_url( get_home_url( $site_id, '/' ) ) . "' rel='permalink'>" . __( 'Visit' ) . '</a></span>';

		echo $this->row_actions( $actions );
	}

	/**
	 * Display the home URL of the site.
	 *
	 * @param int $site_id ID of the row's site.
	 */
	private function display_site_url( $site_id ) {
		?><span style="color: #5e6a71; "><?php echo esc_url( trailingslashit( get_home_url( $site_id ) ) ); ?></span><?php
	}

	/**
	 * Display the date the site was created.
	 *
	 * @param int $site_id ID of the row's site.
	 */
	private function display_site_created( $site_id ) {
		switch_to_blog( $site_id );
		$site_details = get_blog_details();
		restore_current_blog();

		if ( isset( $site_details->registered ) ) {
			echo $site_details->registered;
		}
	}

	/**
	 * Display the count of site users.
	 *
	 * This count is cached for 12 hours in an attempt to avoid frequent large
	 * queries.
	 *
	 * @param int $site_id ID of row's site.
	 */
	private function display_site_users( $site_id ) {
		switch_to_blog( $site_id );
		if ( ! $user_count = wp_cache_get( 'user_count_' . $site_id, 'wsuwp:site' ) ) {
			$user_count = count( get_users() );
			wp_cache_add( 'user_count_' . $site_id, $user_count, 'wsuwp:site', 43200 );
		}
		echo '<a href="site-users.php?id=' . $site_id . '">' . absint( $user_count ) . '</a>';
		restore_current_blog();
	}

	/**
	 * Row actions copied directly from class-wp-list-table so that we can
	 * use it with our custom column.
	 *
	 * @param array $actions
	 * @param bool  $always_visible
	 *
	 * @return string HTML output for row actions.
	 */
	function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}
}
new WSU_Network_Sites_List();