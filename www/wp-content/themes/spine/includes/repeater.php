<?php

function get_column_options() {
	$column_options = array (
		'One' => '1',
		'Two' => '2',
		'Three' => '3',
		'Four' => '4',
		'Five' => '5',
		'Six' => '6',
		'Seven' => '7',
		'Eight' => '8',
	);
	return $column_options;
	}
function get_section_options() {
	$section_options = array (
		'One' => '1',
		'Two' => '2',
		'Three' => '3',
		'Four' => '4',
		'Five' => '5',
		'Six' => '6',
		'Seven' => '7',
		'Eight' => '8',
	);
	return $section_options;
	}
 
add_action('admin_init', 'wsu_add_meta_boxes', 1);
function wsu_add_meta_boxes() {
	add_meta_box( 'page-sections', 'Sections', 'wsu_repeatable_meta_box_display', 'page', 'normal', 'default');
}
 
function wsu_repeatable_meta_box_display() {
	global $post;
 
	$sections = get_post_meta($post->ID, 'sections', true);
	
	$section_options = get_section_options();
	$column_options = get_column_options();
 
	wp_nonce_field( 'wsu_repeatable_meta_box_nonce', 'wsu_repeatable_meta_box_nonce' );
	?>
	<script type="text/javascript">
	jQuery(document).ready(function( $ ){
		$( '#add-row' ).on('click', function() {
			var row = $( '.empty-row.screen-reader-text' ).clone(true);
			row.removeClass( 'empty-row screen-reader-text' );
			row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
			return false;
		});
  	
		$( '.remove-row' ).on('click', function() {
			$(this).parents('tr').remove();
			return false;
		});
		
		$("select#column-count").change( function() { var section_cols = $("#column-count").value(); $(this).parents("tr").siblings('tr.columns-editors'); } )
	});
	</script>
  
	<table id="repeatable-fieldset-one" width="100%">
	<tbody>
	<?php
	
	if ( $sections ) :
	
	foreach ( $sections as $section ) {
	?>
	<tr>
		<td>
			<label for="section-number">Section Number</label>
			<select name="name[]">
			<?php foreach ( $section_options as $label => $value ) : ?>
			<option value="<?php echo $value; ?>"<?php selected( $section['name'], $value ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	
		<td>
			<label for="column-count">Column Count</label>
			<select id="column-count" name="select[]">
			<?php foreach ( $column_options as $label => $value ) : ?>
			<option value="<?php echo $value; ?>"<?php selected( $section['select'], $value ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	
		<td></td>
	
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	<tr class="columns-editors">
		<td colspan="4">

		<?php
			if ($section['url'] != '' ) {
				$content = $section['url']; } else {
				$content = ''; };
				$editor_id = 'column_editor';
			
			wp_editor( $content, $editor_id, $settings = array('textarea_name' => 'url[]') );
			
			?>
		</td>
	</tr>
	<?php
	}
	else :
	// show a blank one
	?>
	<tr>
		<td>
			<label for="section-number">Section Number</label>
			<select name="name[]">
			<?php foreach ( $section_options as $label => $value ) : ?>
			<option value="<?php echo $value; ?>"><?php echo $label; ?></option>
			<?php endforeach; ?>
			</select>
			
		</td>
	
		<td>
			<label for="column-count">Column Count</label>
			<select name="select[]">
			<?php foreach ( $column_options as $label => $value ) : ?>
			<option value="<?php echo $value; ?>"><?php echo $label; ?></option>
			<?php endforeach; ?>
			</select>
		</td>
	
		<td></td>
	
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	<tr>
		<td colspan="4">
		<?php

			$content = '';
			$editor_id = 'column_editor';
			wp_editor( $content, $editor_id );
			
			?>
		</td>
	</tr>
	<?php endif; ?>
	
	<!-- empty hidden one for jQuery -->
	<tr class="empty-row screen-reader-text">
		<td><input type="text" class="widefat" name="name[]" /></td>
	
		<td>
			<select name="select[]">
			<?php foreach ( $column_options as $label => $value ) : ?>
			<option value="<?php echo $value; ?>"><?php echo $label; ?></option>
			<?php endforeach; ?>
			</select>
		</td>
		
		<td><input type="text" class="widefat" name="url[]" value="http://" /></td>
		  
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	<tr class="empty-row screen-reader-text">
		<td colspan="4">
		<textarea class="wp-editor-area" cols="40" name="url[]" value=""></textarea>
		</td>
	</tr>
	</tbody>
	</table>
	
	<p><a id="add-row" class="button" href="#">Add another</a></p>
	<?php
}
 
add_action('save_post', 'wsu_repeatable_meta_box_save');
function wsu_repeatable_meta_box_save($post_id) {
	if ( ! isset( $_POST['wsu_repeatable_meta_box_nonce'] ) ||
	! wp_verify_nonce( $_POST['wsu_repeatable_meta_box_nonce'], 'wsu_repeatable_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$old = get_post_meta($post_id, 'sections', true);
	$new = array();
	
	$column_options = get_column_options();
	$section_options = get_section_options();
	
	$names = $_POST['name'];
	$selects = $_POST['select'];
	$urls = $_POST['url'];
	
	$count = count( $names );
	
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $names[$i] != '' ) :
			$new[$i]['name'] = stripslashes( strip_tags( $names[$i] ) );
			
			if ( in_array( $selects[$i], $column_options ) )
				$new[$i]['select'] = $selects[$i];
			else
				$new[$i]['select'] = '';
		
			if ( $urls[$i] == 'http://' )
				$new[$i]['url'] = '';
			else
				$new[$i]['url'] = stripslashes( $urls[$i] ); // and however you want to sanitize
		endif;
	}
 
	if ( !empty( $new ) && $new != $old )
		update_post_meta( $post_id, 'sections', $new );
	elseif ( empty($new) && $old )
		delete_post_meta( $post_id, 'sections', $old );
}

?>