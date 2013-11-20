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

	function prepare_items() {
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->site} WHERE 1=1";

		$this->items = $wpdb->get_results( $query, ARRAY_A );

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
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

					case 'network_name': ?>
						<th valign="top" scope="row">
							<?php echo $network['domain']; ?>
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
