<?php
/*
Plugin Name: WSU Roles and Capabilities
Plugin URI: http://web.wsu.edu/
Description: Implements the roles and capabilities required by WSU
Author: washingtonstateuniversity, jeremyfelt
Version: 0.1
*/

class WSU_Roles_And_Capabilities {
	/**
	 * Maintain the single instance of WSU_Roles_And_Capabilities
	 *
	 * @var bool|WSU_Roles_And_Capabilities
	 */
	private static $instance = false;

	/**
	 * Add the filters and actions used
	 */
	private function __construct() {
		add_action( 'init',           array( $this, 'modify_editor_capabilities' ), 10    );
		add_filter( 'editable_roles', array( $this, 'editable_roles'             ), 10, 1 );
		add_filter( 'map_meta_cap',   array( $this, 'map_meta_cap'               ), 10, 4 );
	}

	/**
	 * Handle requests for the instance.
	 *
	 * @return bool|WSU_Roles_And_Capabilities
	 */
	public static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new WSU_Roles_And_Capabilities();

		return self::$instance;
	}

	/**
	 * Modify the editor role.
	 *
	 * Allow editors to create users so that users can be added with less administrator involvement.
	 * 		- Add 'create_users' capability.
	 * 		- Add 'promote_users' capability.
	 */
	function modify_editor_capabilities() {
		$editor = get_role( 'editor' );
		$editor->add_cap( 'create_users' );
		$editor->add_cap( 'promote_users' );
	}

	/**
	 * Modify the list of editable roles.
	 *
	 * As we are giving editors the ability to create and promote users, we should not allow
	 * them to promote users to the administrator level.
	 *
	 * @param array $roles Array of existing roles.
	 *
	 * @return array Array of modified roles.
	 */
	function editable_roles( $roles ) {
		if ( isset( $roles['administrator'] ) && ! current_user_can( 'administrator' ) )
			unset( $roles['administrator'] );

		return $roles;
	}

	/**
	 * Modify user related capabilities to prevent undesired behavior from editors.
	 *
	 * Removes the delete_user, edit_user, remove_user, and promote_user capabilities from a user when
	 * they are not administrators.
	 * @param array  $caps    Array of capabilities.
	 * @param string $cap     Current capability.
	 * @param int    $user_id User ID of capability to modify.
	 * @param array  $args    Array of additional arguments.
	 *
	 * @return array Modified list of capabilities.
	 */
	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		switch( $cap ){
			case 'edit_user':
			case 'remove_user':
			case 'promote_user':
				if( isset( $args[0] ) && $args[0] == $user_id )
					break;
				elseif( ! isset( $args[0] ) )
					$caps[] = 'do_not_allow';
				$other = new WP_User( absint( $args[0] ) );
				if( $other->has_cap( 'administrator' ) ){
					if( ! current_user_can( 'administrator' ) ) {
						$caps[] = 'do_not_allow';
					}
				}
				break;
			case 'delete_user':
			case 'delete_users':
				if( ! isset( $args[0] ) )
					break;
				$other = new WP_User( absint( $args[0] ) );
				if( $other->has_cap( 'administrator' ) ){
					if( ! current_user_can( 'administrator' ) ) {
						$caps[] = 'do_not_allow';
					}
				}
				break;
			default:
				break;
		}

		return $caps;
	}
}
WSU_Roles_And_Capabilities::get_instance();
