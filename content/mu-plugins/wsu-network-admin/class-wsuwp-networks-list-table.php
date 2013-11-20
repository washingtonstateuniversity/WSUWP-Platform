<?php
/**
 * WSUWP Networks List Table class.
 */
class WSUWP_Networks_List_Table extends WP_List_Table {

	function __construct( $args = array() ) {
		parent::__construct( array(
			'plural' => 'networks',
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	function ajax_user_can() {
		return current_user_can( 'manage_sites' );
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

	function prepare_items() {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->site} WHERE 1=1";

		$this->items = $wpdb->get_results( $query, ARRAY_A );

		usort( $this->items, array( $this, '_sort_network_id_asc' ) );

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}

	function no_items() {
		_e( 'No networks found.' );
	}

	function get_columns() {
		$networks_columns = array(
			'cb'             => '<input type="checkbox" />',
			'network_id'     => __( 'Network ID' ),
			'network_name'   => __( 'Network Name' ),
			'network_domain' => __( 'Network Domain' ),
		);

		return $networks_columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'network_id'     => array( 'network_id', false ),
			'network_name'   => array( 'network_name', false ),
			'network_domain' => array( 'network_domain', false ),
		);
		return $sortable_columns;
	}

	function display_rows() {

		$class = '';
		foreach ( $this->items as $network ) {
			$class = ( 'alternate' == $class ) ? '' : 'alternate';

			echo "<tr class='$class'>";

			list( $columns ) = $this->get_column_info();

			foreach ( $columns as $column_name => $column_display_name ) {

				switch ( $column_name ) {
					case 'cb': ?>
						<th scope="row" class="check-column">
							<input type="checkbox" />
						</th>
						<?php
						break;

					case 'network_id': ?>
						<th valign="top" scope="row">
							<?php echo $network['id']; ?>
						</th>
						<?php
						break;

					case 'network_name':
						switch_to_network( $network['id'] );
						$network_name = get_site_option( 'site_name' );
						restore_current_network();
						?>
						<th valign="top" scope="row">
							<?php echo esc_html( $network_name ); ?>
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
