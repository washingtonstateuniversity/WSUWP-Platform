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
		unset( $site_columns['blogname'] );
		unset( $site_columns['lastupdated'] );
		unset( $site_columns['registered'] );
		unset( $site_columns['users'] );

		$site_columns['site_name'] = 'Site Name';
		$site_columns['site_created'] = 'Created';
		$site_columns['users'] = 'Users';

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
			$this->display_site_name_row( $site_id );
		} elseif ( 'site_created' === $column ) {
			$this->display_site_created( $site_id );
		}
	}

	/**
	 * Display the site name column for the row.
	 *
	 * This is copied almost directly from WordPress core to repurpose for
	 * our custom display.
	 *
	 * @param int $site_id ID of the row's site.
	 */
	private function display_site_name_row( $site_id ) {
		$site_name = esc_html( get_blog_option( $site_id, 'blogname' ) );
		?>
		<a href="<?php echo esc_url( network_admin_url( 'site-info.php?id=' . absint( $site_id ) ) ); ?>" class="edit"><?php echo $site_name; ?></a>
		<?php

		// Preordered.
		$actions = array(
			'edit' => '', 'backend' => '',
			'activate' => '', 'deactivate' => '',
			'archive' => '', 'unarchive' => '',
			'spam' => '', 'unspam' => '',
			'delete' => '',
			'visit' => '',
		);

		$actions['edit']	= '<span class="edit"><a href="' . esc_url( network_admin_url( 'site-info.php?id=' . $site_id ) ) . '">' . __( 'Edit' ) . '</a></span>';
		$actions['backend']	= "<span class='backend'><a href='" . esc_url( get_admin_url( $site_id ) ) . "' class='edit'>" . __( 'Dashboard' ) . '</a></span>';
		if ( get_current_site()->blog_id != $site_id ) {
			if ( get_blog_status( $site_id, 'deleted' ) == '1' )
				$actions['activate']	= '<span class="activate"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=activateblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to activate the site %s' ), $site_name ) ) ), 'confirm' ) ) . '">' . __( 'Activate' ) . '</a></span>';
			else
				$actions['deactivate']	= '<span class="activate"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=deactivateblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to deactivate the site %s' ), $site_name ) ) ), 'confirm') ) . '">' . __( 'Deactivate' ) . '</a></span>';

			if ( get_blog_status( $site_id, 'archived' ) == '1' )
				$actions['unarchive']	= '<span class="archive"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=unarchiveblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to unarchive the site %s.' ), $site_name ) ) ), 'confirm') ) . '">' . __( 'Unarchive' ) . '</a></span>';
			else
				$actions['archive']	= '<span class="archive"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=archiveblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to archive the site %s.' ), $site_name ) ) ), 'confirm') ) . '">' . _x( 'Archive', 'verb; site' ) . '</a></span>';

			if ( get_blog_status( $site_id, 'spam' ) == '1' )
				$actions['unspam']	= '<span class="spam"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=unspamblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to unspam the site %s.' ), $site_name ) ) ), 'confirm') ) . '">' . _x( 'Not Spam', 'site' ) . '</a></span>';
			else
				$actions['spam']	= '<span class="spam"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=spamblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to mark the site %s as spam.' ), $site_name ) ) ), 'confirm') ) . '">' . _x( 'Spam', 'site' ) . '</a></span>';

			if ( current_user_can( 'delete_site', $site_id ) )
				$actions['delete']	= '<span class="delete"><a href="' . esc_url( wp_nonce_url( network_admin_url( 'sites.php?action=confirm&amp;action2=deleteblog&amp;id=' . $site_id . '&amp;msg=' . urlencode( sprintf( __( 'You are about to delete the site %s.' ), $site_name ) ) ), 'confirm') ) . '">' . __( 'Delete' ) . '</a></span>';
		}

		$actions['visit']	= "<span class='view'><a href='" . esc_url( get_home_url( $site_id, '/' ) ) . "' rel='permalink'>" . __( 'Visit' ) . '</a></span>';

		/**
		 * Filter the action links displayed for each site in the Sites list table.
		 *
		 * The 'Edit', 'Dashboard', 'Delete', and 'Visit' links are displayed by
		 * default for each site. The site's status determines whether to show the
		 * 'Activate' or 'Deactivate' link, 'Unarchive' or 'Archive' links, and
		 * 'Not Spam' or 'Spam' link for each site.
		 *
		 * @since 3.1.0
		 *
		 * @param array  $actions  An array of action links to be displayed.
		 * @param int    $blog_id  The site ID.
		 * @param string $blogname Site path, formatted depending on whether it is a sub-domain
		 *                         or subdirectory multisite install.
		 */
		$actions = apply_filters( 'manage_sites_action_links', array_filter( $actions ), $site_id, $site_name );
		echo $this->row_actions( $actions );
	}

	/**
	 * Filter the action links displayed under the site name.
	 *
	 * @param array  $actions   List of action links to display.
	 * @param int    $site_id   ID of the row's site.
	 * @param string $site_name Name of the site.
	 */
	public function manage_sites_action_links( $actions, $site_id, $site_name ) {
		if ( isset( $actions['spam'] ) ) {
			unset( $actions['spam'] );
		}
		if ( isset( $actions['unspam'] ) ) {
			unset( $actions['unspam'] );
		}

		return $actions;
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