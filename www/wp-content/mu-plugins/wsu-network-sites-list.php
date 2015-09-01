<?php
/*
* Plugin Name: WSUWP Network Sites List
* Plugin URI: http://web.wsu.edu
* Description: Maintains the customized layout for the MS Sites List Table
* Author: washingtonstateuniversity, jeremyfelt
* Author URI: http://web.wsu.edu
* Version: 0.1
* Network: true
*/

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
			$this->display_site_name( $site_id );
		} elseif ( 'site_url' === $column ) {
			$this->display_site_url( $site_id );
		} elseif ( 'site_created' === $column ) {
			$this->display_site_created( $site_id );
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