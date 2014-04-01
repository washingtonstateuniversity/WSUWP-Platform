<?php
/**
 * WSUWP Networks List Table class.
 *
 * Provides a list of all networks in this WordPress installation and some common
 * management options for each.
 */
class WSUWP_Networks_List_Table extends WP_List_Table {

	/**
	 * Fire up the parent methods from WP_List_Table.
	 */
	function __construct() {
		parent::__construct( array(
			'plural'   => 'networks',
			'singular' => 'network',
			'ajax'     => false,
			'screen'   => 'network',
		) );
	}

	/**
	 * Sort network results ascending by ID.
	 *
	 * @see prepare_items()
	 *
	 * @param array $network_a Array of network data.
	 * @param array $network_b Array of network data.
	 *
	 * @return int Comparison status for usort.
	 */
	private function _sort_network_id_asc( $network_a, $network_b ) {
		if ( $network_a['id'] === $network_b['id'] ) {
			return 0;
		}

		return ( $network_a['id'] < $network_b['id'] ) ? -1 : 1;
	}

	/**
	 * Sort network results descending by ID.
	 *
	 * @see prepare_items()
	 *
	 * @param array $network_a Array of network data.
	 * @param array $network_b Array of network data.
	 *
	 * @return int Comparison status for usort.
	 */
	private function _sort_network_id_desc( $network_a, $network_b ) {
		if ( $network_a['id'] === $network_b['id'] ) {
			return 0;
		}

		return ( $network_a['id'] > $network_b['id'] ) ? -1 : 1;
	}

	/**
	 * Sort network results ascending by domain.
	 *
	 * @see prepare_items()
	 *
	 * @param array $network_a Array of network data.
	 * @param array $network_b Array of network data.
	 *
	 * @return int Comparison status for usort.
	 */
	private function _sort_network_domain_asc( $network_a, $network_b ) {
		return strcasecmp( $network_a['domain'], $network_b['domain'] );
	}

	/**
	 * Sort network results ascending by domain.
	 *
	 * @see prepare_items()
	 *
	 * @param array $network_a Array of network data.
	 * @param array $network_b Array of network data.
	 *
	 * @return int Comparison status for usort.
	 */
	private function _sort_network_domain_desc( $network_a, $network_b ) {
		return strcasecmp( $network_b['domain'], $network_a['domain'] );
	}

	/**
	 * Sort network results ascending by network name.
	 *
	 * @see prepare_items()
	 *
	 * @param array $network_a Array of network data.
	 * @param array $network_b Array of network data.
	 *
	 * @return int Comparison status for usort.
	 */
	private function _sort_network_name_asc( $network_a, $network_b ) {
		return strcasecmp( $network_a['network_name'], $network_b['network_name'] );
	}

	/**
	 * Sort network results ascending by network name.
	 *
	 * @see prepare_items()
	 *
	 * @param array $network_a Array of network data.
	 * @param array $network_b Array of network data.
	 *
	 * @return int Comparison status for usort.
	 */
	private function _sort_network_name_desc( $network_a, $network_b ) {
		return strcasecmp( $network_b['network_name'], $network_a['network_name'] );
	}

	/**
	 * Prepare items for display in the networks list table.
	 */
	function prepare_items() {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->site} WHERE 1=1";

		$this->items = $wpdb->get_results( $query, ARRAY_A );

		// Parse through the network results and add the network name to the final array.
		foreach ( $this->items as $key => $network ) {
			wsuwp_switch_to_network( $network['id'] );
			$this->items[ $key ]['network_name'] = get_site_option( 'site_name' );
			wsuwp_restore_current_network();
		}

		if ( isset( $_GET['orderby'] ) && array_key_exists( $_GET['orderby'], $this->get_sortable_columns() ) ) {
			$orderby = $_GET['orderby'];
		} else {
			$orderby = 'network_id';
		}

		if ( isset( $_GET['order'] ) && in_array( $_GET['order'], array( 'asc', 'desc' ) ) ) {
			$order = $_GET['order'];
		} else {
			$order = 'asc';
		}

		usort( $this->items, array( $this, '_sort_' . $orderby . '_' . $order ) );

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}

	/**
	 * Provide a list of columns that should be displayed in the networks list table.
	 *
	 * @return array List of column ids and their names.
	 */
	function get_columns() {
		$networks_columns = array(
			'network_id'     => __( 'Network ID' ),
			'network_name'   => __( 'Network Name' ),
			'network_domain' => __( 'Network Domain' ),
		);

		return $networks_columns;
	}

	/**
	 * Provide a list of columns that should be sortable.
	 *
	 * @return array List of column keys and their sortable options.
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'network_id'     => array( 'network_id',     true  ),
			'network_name'   => array( 'network_name',   false ),
			'network_domain' => array( 'network_domain', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Add row actions to each network's row as it is output on the screen.
	 *
	 * @param int $network_id Network ID of the current row being output.
	 */
	private function _output_row_actions( $network_id ) {
		$actions = array(
			'edit'      => '',
			'dashboard' => '',
			'visit'     => '',
		);

		// This URL should be generated in relation to the current (primary) network.
		$actions['edit']      = '<span class="edit"><a href="' . esc_url( network_admin_url( 'site-info.php?display=network&network_id=' . $network_id ) ) . '">' . __( 'Edit' ) . '</a></span>';

		// These URLs should be generated for the individual networks.
		wsuwp_switch_to_network( $network_id );
		$actions['dashboard'] = '<span class="backend"><a href="' . esc_url( network_admin_url() ) . '">' . __( 'Dashboard' ) . '</a></span>';
		$actions['visit']     = '<span class="view"><a href="'    . esc_url( network_home_url()  ) . '">' . __( 'Visit' )     . '</a></span>';
		wsuwp_restore_current_network();

		$actions = apply_filters( 'manage_networks_action_links', array_filter( $actions ), $network_id );

		echo $this->row_actions( $actions );
	}

	/**
	 * Display the rows for the networks list table.
	 */
	function display_rows() {

		$class = '';
		foreach ( $this->items as $network ) {
			$class = ( 'alternate' == $class ) ? '' : 'alternate';

			echo '<tr class="' . $class . '">';

			list( $columns ) = $this->get_column_info();

			foreach ( $columns as $column_name => $column_display_name ) {

				switch ( $column_name ) {
					case 'network_id': ?>
						<th valign="top" scope="row">
							<?php echo $network['id']; ?>
						</th>
						<?php
						break;

					case 'network_name':
						?>
						<th valign="top" scope="row">
							<?php
								echo esc_html( $network['network_name'] );
								$this->_output_row_actions( $network['id'] );
							?>
						</th>
						<?php
						break;

					case 'network_domain': ?>
						<th valign="top" scope="row">
							<?php echo $network['domain']; ?>
						</th>
						<?php
						break;

					default: ?>
						<th valign="top" scope="row">
							<?php echo $network['id']; ?>
						</th>
						<?php
						break;
				}
			}
			?>
			</tr>
		<?php
		}
	}
}
